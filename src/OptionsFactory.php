<?php

namespace ShootProof\Cli;

use Aura\Cli\Context;
use josegonzalez\Dotenv\Loader as DotenvLoader;
use ShootProof\Cli\Options;
use ShootProof\Cli\Utility\OptionTransformer;
use ShootProof\Cli\Utility\TildeExpander;

class OptionsFactory
{
    protected $config = [];
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
        $this->error = null;

        // Extend the base config
        if (isset($this->config['getopt'])) {
            $getopt = array_merge($this->config['getopt'], $getopt);
        }

        if (isset($this->config['validators'])) {
            $validators = array_merge($this->config['validators'], $validators);
        }

        if (isset($this->config['defaults'])) {
            $defaults = array_merge($this->config['defaults'], $defaults);
        }

        // Read command line
        $cli = $this->context->getopt($getopt);
        $data = new OptionTransformer($cli->get());

        // Create the options container instance
        $options = new Options($validators, $defaults);
        $options->loadOptionData($data->getArrayCopy()); // initial load so we can access the config option

        // Read config file
        $configLoader = new DotenvLoader(new TildeExpander($options->config));
        try {
            $configData = $configLoader->parse()->toArray();
            $options->loadOptionData($configData, false); // don't overwrite CLI data
        } catch (\InvalidArgumentException $e) {
            $this->error = $e;
        }

        return $options;
    }

    public function getLastError()
    {
        return $this->error;
    }
}
