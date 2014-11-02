<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\OptionsFactory;
use compwright\ShootproofCli\Validators\ShootproofEventValidator;
use compwright\ShootproofCli\Validators\ShootproofAlbumValidator;
use compwright\ShootproofCli\Validators\ValidatorException;
use compwright\ShootproofCli\Utility\FileSetCalculator;
use compwright\ShootproofCli\Utility\TildeExpander;
use compwright\ShootproofCli\Utility\DirectoryListFactory;
use compwright\ShootproofCli\Utility\StdinReader;
use compwright\ShootproofCli\Utility\ShootproofFile;
use compwright\ShootproofCli\Utility\ResultPager;
use compwright\ShootproofCli\Utility\FileDownloader;
use Aura\Cli\Context;
use Sp_Api as ShootproofApi;
use josegonzalez\Dotenv\Loader as DotenvLoader;


class PullCommand extends BaseCommand implements HelpableCommandInterface, ConfiguresOptionsInterface
{
	use HelpableCommandTrait;

	public static $usage = 'pull [options] [<dir>]';

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

	public static $options = [
		'event:' => 'ShootProof event ID',
		'album:' => 'ShootProof album ID',
		'replace' => 'Replaces local files with ShootProof files if the names match',
		'preview' => 'Preview this operation, but do not apply any changes',
	];

	public static function configureOptions(Options $options, ShootproofApi $api)
	{
		$options->addValidators([
			'event' => new ShootproofEventValidator($api),
			'album' => new ShootproofAlbumValidator($api),
		]);

		$options->setDefault('target', function(Options $options)
		{
			return $options->album
			     ? 'album'
			     : 'event';
		});
	}

	public function __invoke(Context $context, OptionsFactory $optionsFactory)
	{
		$getopt = $context->getopt(array_keys(self::$options));

		// Get directory list
		$dirListFactory = new DirectoryListFactory;
		$dirListFactory->loadFromCommandline($getopt->get(), 2);
		$dirListFactory->loadFromStdin(new StdinReader(3));
		$dirList = $dirListFactory->getList();
		$this->logger->addDebug('Found directories', [count($dirList)]);

		// Load base options
		$baseOptions = $optionsFactory->newInstance();
		self::configureOptions($baseOptions, $this->api);
		$baseOptions->validateAllRequired();

		if ($baseOptions->preview)
		{
			$this->logger->addNotice('PREVIEW MODE');
		}

		foreach ($dirList as $dir)
		{
			try
			{
				$this->processDirectory($dir, $baseOptions, $optionsFactory);
			}
			catch (\Exception $e)
			{
				if ($baseOptions->haltOnError)
				{
					throw $e;
				}
				else
				{
					// proceed to the next directory
				}
			}
		}
	}

	protected function processDirectory($dir, $baseOptions, $optionsFactory)
	{
		$this->logger->addNotice('In directory', [$dir]);

		// Reload the options and read the directory config file
		$options = $optionsFactory->newInstance();
		self::configureOptions($options, $this->api);
		$configPath = new TildeExpander($dir) . '/.shootproof';
		$configLoader = new DotenvLoader($configPath);
		try
		{
			$configData = $configLoader->parse()->toArray();
			$options->loadOptionData($configData, FALSE); // don't overwrite CLI data
			$this->logger->addDebug('Config file found', [$configPath, $configData]);
		}
		catch (\InvalidArgumentException $e)
		{
			// ignore
			$this->logger->addDebug('Config file not found', [$configPath]);
		}

		// Make sure all required options are present
		$options->validateAllRequired();
		if ( ! $options->event && ! $options->album)
		{
			throw new ValidatorException('Either --event or --album is required');
		}

		// Get local file list
		$localFiles = array_map('basename', $this->getFileList($dir));

		// Get remote file list
		switch ($options->target)
		{
			case 'album':
				$this->logger->addDebug('Fetching album photos', [$options->album]);
				$remoteFiles = new ResultPager(function($page) use ($options)
				{
					$response = $this->api->getAlbumPhotos($options->album, $page + 1);
					return [
						(int) $response['total_pages'],
						$response['photos']
					];
				});
			break;

			case 'event':
				$this->logger->addDebug('Fetching event photos', [$options->event]);
				$remoteFiles = new ResultPager(function($page) use ($options)
				{
					$response = $this->api->getEventPhotos($options->event, $page + 1);
					return [
						(int) $response['total_pages'],
						$response['photos']
					];
				});
			break;
		}

		// Turn the response into an array of stringifiable objects so we can compare file names
		$remoteFiles = array_map([new ShootproofFile, 'arrayFactory'], $remoteFiles->getArrayCopy());

		// Compute files to add, remove, and replace
		$calculator = new FileSetCalculator($remoteFiles, $localFiles, $options->replace);
		
		foreach ($calculator->add() as $file)
		{
			// download
			$filePath = $dir . DIRECTORY_SEPARATOR . $file;
			$this->logger->addNotice('Downloading new file from ShootProof', [$filePath]);
			if ( ! $baseOptions->preview)
			{
				$this->downloadFile($file->url['original'], $filePath, $baseOptions);
			}
		}

		foreach ($calculator->remove() as $file)
		{
			$filePath = $dir . DIRECTORY_SEPARATOR . $file;
			$this->logger->addNotice('Deleting local file', [$filePath]);
			if ( ! $baseOptions->preview)
			{
				@unlink($filePath);
			}
		}

		foreach ($calculator->replace() as $file)
		{
			$filePath = $dir . DIRECTORY_SEPARATOR . $file;
			$this->logger->addNotice('Replacing local file', [$filePath]);
			if ( ! $baseOptions->preview)
			{
				@unlink($filePath);
				$this->downloadFile($file->url['original'], $filePath, $baseOptions);
			}
		}

		// Write ShootProof metadata to the directory
		$success = $this->writeConfig($configPath, [
			'target' => $options->target,
			$options->target => $options->{$options->target}, // event or album
		]);

		if ($success !== FALSE)
		{
			$this->logger->addDebug('ShootProof settings file saved', [$configPath]);
		}
		else
		{
			$this->logger->addWarning('ShootProof settings is unwritable', [$configPath]);
		}
	}

	protected function downloadFile($url, $destination, $options)
	{
		$downloader = new FileDownloader($url);

		$retryLimit = $options->retryLimit ? $options->retryLimit : 1;
		for ($i = 1; $i <= $retryLimit; $i++)
		{
			try
			{
				if (isset($e))
				{
					$this->logger->addInfo('Download failed, retrying', [$downloader->result['http_code']]);
					unset($e);
				}

				$downloader->download($destination, TRUE);
				$this->logger->addDebug('Download completed', [$downloader->result['http_code']]);
				return;
			}
			catch (\RuntimeException $e)
			{
				@unlink($destination);

				if ($options->haltOnError)
				{
					throw $e;
				}
				else
				{
					// continue
				}
			}
		}

		$this->logger->addError('Download failed on final attempt', [$downloader->result['http_code']]);
	}

	protected function writeConfig($path, array $config)
	{
		$contents = '';
		foreach ($config as $k => $v)
		{
			if (strpos($v, ' ') !== FALSE)
			{
				// Quote value, escape quotes that happen to be in the value
				$v = '"' . str_replace('"', '\\"', $v) . '"';
			}

			$contents .= $k . '=' . $v . PHP_EOL;
		}

		return @file_put_contents($path, $contents);
	}
}
