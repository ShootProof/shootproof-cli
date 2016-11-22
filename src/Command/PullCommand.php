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
use ShootProof\Cli\Utility\FileDownloader;
use ShootProof\Cli\Utility\FileSetCalculator;
use ShootProof\Cli\Utility\ResultPager;
use ShootProof\Cli\Utility\ShootProofFile;
use ShootProof\Cli\Utility\TildeExpander;
use ShootProof\Cli\Validators\ShootProofAlbumValidator;
use ShootProof\Cli\Validators\ShootProofEventValidator;
use ShootProof\Cli\Validators\ValidatorException;
use Sp_Api as ShootProofApi;

/**
 * Provides the shootproof-cli pull command
 */
class PullCommand extends BaseCommand implements HelpableCommandInterface
{
    use HelpableCommandTrait;

    /**
     * @var string
     */
    public static $usage = 'pull [options] [<dir>]';

    /**
     * @var string
     */
    public static $description = <<<TEXT
This command will compare the ShootProof photos in the specified
    event and compare those to the ones in the directory. New photos
    will be downloaded from ShootProof; any photos not on ShootProof
    will be deleted from the directory. If the --replace option is
    specified, then matching photos in the directory will be overwritten
    with the ones from ShootProof.

    If the --preview option is passed, then the operation will not
    actually execute, but a preview of the operation will be output.

    If no directory is specified, the current directory will be used.

    If a .shootproof file exists in the directory, the event and album
    options will be read from that file unless they are explicitly
    provided on the command line.
TEXT;

    /**
     * @var array
     */
    public static $options = [
        'event:' => 'ShootProof event ID',
        'album:' => 'ShootProof album ID',
        'replace' => 'Replaces local files with ShootProof files if the names match',
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
            'event' => new ShootProofEventValidator($this->api),
            'album' => new ShootProofAlbumValidator($this->api),
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
     * Pulls (downloads) files from ShootProof to the specified local directory
     *
     * @param string $dir The directory to process
     * @param Options $baseOptions
     * @param OptionsFactory $optionsFactory
     * @throws ValidatorException if event or album options are not provided
     */
    protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory)
    {
        // If the directory doesn't exist, create it and any parent dirs
        if (! file_exists($dir)) {
            if ($baseOptions->preview || @mkdir($dir, 0777, true)) {
                $this->logger->addNotice('Created directory', [$dir]);
            } else {
                $this->logger->addError('Failed to create directory', [$dir]);
                return;
            }
        }

        // Reload the options and read the directory config file
        $options = $optionsFactory->newInstance([], $this->getValidators(), $this->getDefaults());
        $configPath = new TildeExpander($dir) . '/.shootproof';
        $configLoader = new DotenvLoader((string) $configPath);
        try {
            $configData = $configLoader->parse()->toArray();
            $options->loadOptionData($configData, false); // don't overwrite CLI data
            $this->logger->addDebug('ShootProof settings file found', [$configPath, $configData]);
        } catch (\InvalidArgumentException $e) {
        // ignore
            $this->logger->addDebug('ShootProof settings file not found', [$configPath]);
        }

        // Make sure all required options are present
        $options->validateAllRequired();
        if (! $options->event && ! $options->album) {
            throw new ValidatorException('Either --event or --album is required');
        }

        // Get local file list
        $localFiles = array_map('basename', $this->getFileList($dir));

        // Get remote file list
        switch ($options->target) {
            case 'album':
                $this->logger->addDebug('Fetching album photos', [$options->album]);
                $remoteFiles = new ResultPager(function ($page) use ($options) {
                    $response = $this->api->getAlbumPhotos($options->album, $page + 1);
                    return [
                        (int) $response['total_pages'],
                        $response['photos']
                    ];
                });
                break;

            case 'event':
                $this->logger->addDebug('Fetching event photos', [$options->event]);
                $remoteFiles = new ResultPager(function ($page) use ($options) {
                    $response = $this->api->getEventPhotos($options->event, $page + 1);
                    return [
                        (int) $response['total_pages'],
                        $response['photos']
                    ];
                });
                break;
        }

        // Turn the response into an array of stringifiable objects so we can compare file names
        $remoteFiles = array_map([new ShootProofFile(), 'arrayFactory'], $remoteFiles->getArrayCopy());

        // Compute files to add, remove, and replace
        $calculator = new FileSetCalculator($remoteFiles, $localFiles, $options->replace);

        foreach ($calculator->add() as $file) {
        // download
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $this->logger->addNotice('Downloading new file from ShootProof', [$filePath]);
            if (! $baseOptions->preview) {
                $this->downloadFile($file->getUrl('original'), $filePath, $baseOptions);
            }
        }

        foreach ($calculator->remove() as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $this->logger->addNotice('Deleting local file', [$filePath]);
            if (! $baseOptions->preview) {
                @unlink($filePath);
            }
        }

        foreach ($calculator->replace() as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $this->logger->addNotice('Replacing local file', [$filePath]);
            if (! $baseOptions->preview) {
                @unlink($filePath);
                $this->downloadFile($file->getUrl('original'), $filePath, $baseOptions);
            }
        }

        // Write ShootProof metadata to the directory
        try {
            $writer = new ConfigWriter([
                'target' => $options->target,
                $options->target => $options->{$options->target}, // event or album
            ]);
            if (! $baseOptions->preview) {
                $writer->write($configPath);
            }
            $this->logger->addDebug('ShootProof settings file saved', [$configPath]);
        } catch (\InvalidArgumentException $e) {
            $this->logger->addWarning('ShootProof settings file is unwritable', [$configPath]);
        } catch (\RuntimeException $e) {
            $this->logger->addWarning('Failed writing ShootProof settings file', [$configPath]);
        }
    }

    /**
     * Downloads an individual file and stores it to local disk
     *
     * @param string $url URL of the file to download
     * @param string $destination Path to the location on disk where file should be saved
     * @param Options $options
     * @throws \RuntimeException if download failed and haltOnError is true
     */
    protected function downloadFile($url, $destination, Options $options)
    {
        $downloader = new FileDownloader($url);

        $retryLimit = $options->retryLimit ? $options->retryLimit : 1;
        for ($i = 1; $i <= $retryLimit; $i++) {
            try {
                if (isset($e)) {
                    $this->logger->addInfo('Download failed, retrying', [$downloader->getResult('http_code')]);
                    unset($e);
                }

                $downloader->download($destination, true);
                $this->logger->addDebug('Download completed', [$downloader->getResult('download_content_length')]);
                return;
            } catch (\RuntimeException $e) {
                @unlink($destination);

                if ($options->haltOnError) {
                    throw $e;
                } else {
                    // continue
                }
            }
        }

        $this->logger->addError('Download failed on final attempt', [$downloader->getResult('http_code')]);
    }
}
