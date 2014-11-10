<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\OptionsFactory;
use compwright\ShootproofCli\Utility\DirectoryListFactory;
use compwright\ShootproofCli\Utility\StdinReader;
use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Monolog\Logger;
use Sp_Api as ShootproofApi;

abstract class BaseCommand implements ConfiguresOptionsInterface
{
	protected $stdio;
	protected $logger;
	protected $api;

	static protected $options = [];

	public static function configureOptions(Options $options, ShootproofApi $api) {}

	public function __construct(Stdio $stdio, Logger $logger, ShootproofApi $api)
	{
		$this->stdio = $stdio;
		$this->logger = $logger;
		$this->api = $api;
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
				$this->logger->addNotice('In directory', [$dir]);
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
					$this->logger->addError($e->getMessage(), [$e]);
				}
			}
		}
	}

	abstract protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory);

	protected function getFileList($dir)
	{
		// filter to only files, expand to absolute path
		return array_map('realpath', array_filter(glob($dir . '/*'), 'is_file'));
	}
}
