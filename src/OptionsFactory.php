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

namespace ShootProof\Cli;

use Aura\Cli\Context;
use josegonzalez\Dotenv\Loader as DotenvLoader;
use ShootProof\Cli\Options;
use ShootProof\Cli\Utility\OptionTransformer;
use ShootProof\Cli\Utility\TildeExpander;

/**
 * Factory for building an Options object
 */
class OptionsFactory
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \InvalidArgumentException
     */
    protected $error;

    /**
     * Constructs an options factory
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Sets up the base configuration for options
     *
     * @param array $getopt Options data
     * @param array $validators Validators to validate the options
     * @param array $defaults Default values for the options
     */
    public function setBaseConfig(array $getopt = [], array $validators = [], array $defaults = [])
    {
        $this->config = compact('getopt', 'validators', 'defaults');
    }

    /**
     * Creates and returns a new Options instance
     *
     * @param array $getopt Options data
     * @param array $validators Validators to validate the options
     * @param array $defaults Default values for the options
     * @return Options
     */
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

    /**
     * Returns the last exception encountered when processing options
     *
     * @return \InvalidArgumentException|null
     */
    public function getLastError()
    {
        return $this->error;
    }
}
