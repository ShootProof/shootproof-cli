<?php

namespace compwright\ShootproofCli;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\Utility\OptionTransformer;
use compwright\ShootproofCli\Utility\TildeExpander;
use Aura\Cli\Context;
use josegonzalez\Dotenv\Loader as DotenvLoader;

class OptionsFactory
{
	protected $context;
	protected $loader;
	protected $error;

	public function __construct(Context $context)
	{
		$this->context = $context;
	}

	public function setBaseConfig(array $getopt = [], array $validators = [], array $defaults = [])
	{
		$this->config = compact('getopt', 'validators', 'defaults');
	}

	public function newInstance(array $getopt = [], array $validators = [], array $defaults = [])
	{
		$this->error = NULL;
		
		// Extend the base config
		$getopt = array_merge($this->config['getopt'], $getopt);
		$validators = array_merge($this->config['validators'], $validators);
		$defaults = array_merge($this->config['defaults'], $defaults);

		// Read command line
		$cli = $this->context->getopt($getopt);
		$data = new OptionTransformer($cli->get());

		// Create the options container instance
		$options = new Options($validators, $defaults);
		$options->loadOptionData($data); // initial load so we can access the config option

		// Read config file
		$configLoader = new DotenvLoader(new TildeExpander($options->config));
		try
		{
			$configData = $configLoader->parse()->toArray();
			$options->loadOptionData($configData, FALSE); // don't overwrite CLI data
		}
		catch (\InvalidArgumentException $e)
		{
			$this->error = $e;
		}

		return $options;
	}

	public function getLastError()
	{
		return $this->error;
	}
}
