<?php
/**
 * This file is part of the ShootProof command line tool.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) ShootProof, LLC (https://www.shootproof.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ShootProof\Cli\Command;

use Aura\Cli\Context;
use josegonzalez\Dotenv\Loader as DotenvLoader;
use ShootProof\Cli\Options;
use ShootProof\Cli\OptionsFactory;
use ShootProof\Cli\Utility\ConfigWriter;
use ShootProof\Cli\Utility\FileSetCalculator;
use ShootProof\Cli\Utility\ResultPager;
use ShootProof\Cli\Utility\ShootproofFile;
use ShootProof\Cli\Utility\TildeExpander;
use ShootProof\Cli\Validators\ShootproofAlbumValidator;
use ShootProof\Cli\Validators\ShootproofEventValidator;
use ShootProof\Cli\Validators\ValuesValidator;
use Sp_Api as ShootproofApi;

/**
 * Provides the shootproof-cli push command
 */
class PushCommand extends BaseCommand implements HelpableCommandInterface
{
    use HelpableCommandTrait;

    /**
     * @var string
     */
    public static $usage = 'push [options] [<dir>]';

    /**
     * @var string
     */
    public static $description = <<<TEXT
Uploads photos in a directory or set of directories to a ShootProof
    event or album. Choose between the two using the --target-event
    parameter.

    If no event or album ID is passed, a new ShootProof event or album
    will be created automatically using the name of the directory. If
    --event-name or --album-name is passed, it will be created with the
    specified name. Additional album settings may be passed with
    --parent-album and --album-password.

    Push will compare the photos on ShootProof with the ones in a
    directory. New photos will be added to ShootProof; any photos not in
    the directory will be deleted from ShootProof. If the --replace
    option is specified, then matching photos in ShootProof will be
    overwritten with the ones from the directory.

    If the --preview option is passed, then the operation will not
    actually execute, but a preview of the operation will be output.

    If no directory is specified, the current directory will be used.
    Glob expressions are supported for processing multiple directories
    (each matching directory will be pushed to a separate ShootProof
    event or album). Alternately, a list of directories may be piped
    into this command.

    Options for this command may also be set in a .shootproof file in the
    directory:

        target=<target>
        event=<eventId>
        eventName=<name>
        album=<albumId>
        parentAlbum=<parentAlbumId>
        albumName=<name>
        albumPassword=<password>

    After this command completes successfully, a .shootproof file will be
    written to the directory for use in subsequent runs.
TEXT;

    /**
     * @var array
     */
    public static $options = [
        'target:' => 'ShootProof upload target (specify event or album)',
        'event:' => 'ShootProof event ID',
        'event-name:' => 'ShootProof event name',
        'album:' => 'ShootProof album ID',
        'parent-album:' => 'ShootProof parent album ID',
        'album-name:' => 'ShootProof album name',
        'album-password:' => 'ShootProof album password',
        'replace' => 'Replaces files on ShootProof with local files if the names match',
        'preview' => 'Preview this operation, but do not apply any changes',
    ];

    /**
     * Returns an array of validators for validating options passed to this command
     *
     * @return array
     */
    protected function getValidators()
    {
        return [
            'target' => new ValuesValidator(['event', 'album']),
            'event' => new ShootproofEventValidator($this->api),
            'album' => new ShootproofAlbumValidator($this->api),
            'parent-album' => new ShootproofAlbumValidator($this->api),
        ];
    }

    /**
     * Returns the default target option (album or event) for this command
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'target' => function (Options $options) {
                return $options->album
                     ? 'album'
                     : 'event';
            }
        ];
    }

    /**
     * Pushes (uploads) files to ShootProof from the specified local directory
     *
     * @param string $dir The directory to process
     * @param Options $baseOptions
     * @param OptionsFactory $optionsFactory
     */
    protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory)
    {
        // Reload the options and read the directory config file
        $options = $optionsFactory->newInstance([], $this->getValidators(), $this->getDefaults());
        $configPath = new TildeExpander($dir) . '/.shootproof';
        $configLoader = new DotenvLoader($configPath);
        try {
            $configData = $configLoader->parse()->toArray();
            $options->loadOptionData($configData, false); // don't overwrite CLI data
            $this->logger->addDebug('Config file found', [$configPath, $configData]);
        } catch (\InvalidArgumentException $e) {
        // ignore
            $this->logger->addDebug('Config file not found', [$configPath]);
        }

        // Make sure all required options are present
        $options->validateAllRequired();

        // Get local file list
        $localFiles = array_map('basename', $this->getFileList($dir));

        $eventId = $options->event ? $options->event : null;
        $albumId = $options->album ? $options->album : null;

        // Get remote file list
        switch ($options->target)
        {
            case 'album':

                // Create the album
                if (! $albumId) {
                    list($eventId, $albumId) = $this->createAlbum($options, basename($dir));
                }

                $this->logger->addDebug('Fetching album photos', [$albumId]);
                if ($baseOptions->preview) {
                    $remoteFiles = new \ArrayObject();
                } else {
                    $remoteFiles = new ResultPager(function ($page) use ($albumId) {
                        $response = $this->api->getAlbumPhotos($albumId, $page + 1);
                        return [
                            (int) $response['total_pages'],
                            $response['photos']
                        ];
                    });
                }
                break;

            case 'event':

                // Create the event
                if (! $eventId) {
                    $eventId = $this->createEvent($options, basename($dir));
                }

                $this->logger->addDebug('Fetching event photos', [$eventId]);
                if ($baseOptions->preview) {
                    $remoteFiles = new \ArrayObject();
                } else {
                    $remoteFiles = new ResultPager(function ($page) use ($eventId) {
                        $response = $this->api->getEventPhotos($eventId, $page + 1);
                        return [
                            (int) $response['total_pages'],
                            $response['photos']
                        ];
                    });
                }
                break;
        }

        // Turn the response into an array of stringifiable objects so we can compare file names
        $remoteFiles = array_map([new ShootproofFile(), 'arrayFactory'], $remoteFiles->getArrayCopy());

        // Compute files to add, remove, and replace
        $calculator = new FileSetCalculator($localFiles, $remoteFiles, $options->replace);

        foreach ($calculator->add() as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $this->logger->addNotice('Uploading new file to ShootProof', [$filePath]);
            if (! $baseOptions->preview) {
                $this->uploadFile($filePath, $eventId, $albumId, $options->retryLimit);
            }
        }

        foreach ($calculator->remove() as $file) {
            $this->logger->addNotice('Deleting remote file', [$file->getId(), (string) $file]);
            if (! $baseOptions->preview) {
                $this->deleteFile($file->getId(), $options->retryLimit);
            }
        }

        foreach ($calculator->replace() as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $this->logger->addNotice('Replacing remote file', [$file->getId(), $filePath]);
            if (! $baseOptions->preview) {
                $this->updateFile($filePath, $file->getId(), $options->retryLimit);
            }
        }

        // Write ShootProof metadata to the directory
        try {
            $writer = new ConfigWriter([
                'target' => $options->target,
                'event' => $eventId,
                'album' => $albumId,
            ]);
            if (! $baseOptions->preview) {
                $writer->write($configPath);
            }
            $this->logger->addDebug('ShootProof settings file saved', [$configPath]);
        } catch (\InvalidArgumentException $e) {
            $this->logger->addWarning('ShootProof settings file is unwritable', [$configPath]);
        }
        catch (\RuntimeException $e) {
            $this->logger->addWarning('Failed writing ShootProof settings file', [$configPath]);
        }
    }

    /**
     * Uploads a file from disk to a ShootProof event
     *
     * @param string $filepath The path to the local file to upload
     * @param int $eventId The ShootProof ID of the event to which this file should belong
     * @param int|null $albumId Optionally, the ShootProof ID of the album to which this file should belong
     * @param int|null $retryLimit Optionally, the number of times we should attempt to upload (defaults to 1)
     * @throws \RuntimeException if upload fails and haltOnError is true
     */
    protected function uploadFile($filepath, $eventId, $albumId = null, $retryLimit = null)
    {
        $retryLimit = $retryLimit ? $retryLimit : 1;
        for ($i = 1; $i <= $retryLimit; $i++) {
            try {
                $result = $this->api->uploadPhotoFromPath($eventId, $filepath, $albumId);
                $this->logger->addDebug('Upload completed', [$result['stat']]);
                return;
            } catch (\RuntimeException $e) {
                if ($options->haltOnError) {
                    throw $e;
                } else {
                    // continue
                }
            }
        }

        $this->logger->addError('Upload failed on final attempt', [$result['stat']]);
    }

    /**
     * Updates a ShootProof photo with one from local disk
     *
     * @param string $filepath The path to the local file to upload
     * @param int $photoId The ShootProof ID of the photo this file should update
     * @param int|null $retryLimit Optionally, the number of times we should attempt to update (defaults to 1)
     * @throws \RuntimeException if update fails and haltOnError is true
     */
    protected function updateFile($filepath, $photoId, $retryLimit = null)
    {
        $retryLimit = $retryLimit ? $retryLimit : 1;
        for ($i = 1; $i <= $retryLimit; $i++) {
            try {
                $result = $this->api->updatePhotoFromPath($photoId, $filepath);
                $this->logger->addDebug('Update completed', [$result['stat']]);
                return;
            } catch (\RuntimeException $e) {
                if ($options->haltOnError) {
                    throw $e;
                } else {
                    // continue
                }
            }
        }

        $this->logger->addError('Upload failed on final attempt', [$result['stat']]);
    }

    /**
     * Deletes a ShootProof photo
     *
     * @param int $photoId The ShootProof ID of the photo to delete
     * @param int|null $retryLimit Optionally, the number of times we should attempt to delete (defaults to 1)
     * @throws \RuntimeException if delete fails and haltOnError is true
     */
    protected function deleteFile($photoId, $retryLimit = null)
    {
        $retryLimit = $retryLimit ? $retryLimit : 1;
        for ($i = 1; $i <= $retryLimit; $i++) {
            try {
                $result = $this->api->deletePhoto($photoId);
                $this->logger->addDebug('Delete completed', [$result['stat']]);
                return;
            } catch (\RuntimeException $e) {
                if ($options->haltOnError) {
                    throw $e;
                } else {
                    // continue
                }
            }
        }

        $this->logger->addError('Upload failed on final attempt', [$result['stat']]);
    }

    /**
     * Creates a new ShootProof gallery (event)
     *
     * @param Options $options
     * @param string $defaultName Name to use when creating the event if eventName option not set
     * @return int|string ShootProof ID of the event created
     */
    protected function createEvent(Options $options, $defaultName)
    {
        $eventName = $options->eventName ? $options->eventName : $defaultName;
        $this->logger->addNotice('Creating ShootProof event', [$eventName]);
        if (! $options->preview) {
            $response = $this->api->createEvent($eventName);
            return $response['event']['id'];
        } else {
            return 'EVENT_ID_PREVIEW';
        }
    }

    /**
     * Creates a new ShootProof gallery album
     *
     * @param Options $options
     * @param string $defaultName Name to use when creating the album if albumName option not set
     * @return array Ordered array containing the ShootProof event ID and album ID
     */
    protected function createAlbum(Options $options, $defaultName)
    {
        $albumName = $options->albumName ? $options->albumName : $defaultName;
        $eventId = $options->event ? $options->event : $this->createEvent($options, $albumName);
        $this->logger->addNotice('Creating ShootProof album', [
            'name' => $albumName,
            'event' => $eventId,
            'parent' => $options->parentAlbum,
            'password' => $options->albumPassword ? str_repeat('*', strlen($options->albumPassword)) : null,
        ]);
        if (! $options->preview) {
            $response = $this->api->createEventAlbum(
                $eventId,
                $albumName,
                $options->albumPassword,
                $options->parentAlbum
            );
            return [
                $eventId,
                $response['album']['id'],
            ];
        } else {
            return [
                $eventId,
                'ALBUM_ID_PREVIEW',
            ];
        }
    }
}
