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
use Aura\Cli\Stdio;
use Monolog\Logger;
use ShootProof\Cli\Options;
use ShootProof\Cli\OptionsFactory;
use ShootProof\Cli\Utility\DirectoryListFactory;
use ShootProof\Cli\Utility\StdinReader;
use Sp_Api as ShootproofApi;

/**
 * Provides functionality shared by most shootproof-cli commands
 */
abstract class BaseCommand
{
    /**
     * Command line options that may be passed to the command
     * @var array
     */
    static protected $options = [];

    /**
     * Command line standard input/output
     * @var Stdio
     */
    protected $stdio;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ShootproofApi
     */
    protected $api;

    /**
     * Constructs a command object
     *
     * @param Stdio $stdio Command line standard input/output
     * @param Logger $logger
     * @param ShootproofApi $api
     */
    public function __construct(Stdio $stdio, Logger $logger, ShootproofApi $api)
    {
        $this->stdio = $stdio;
        $this->logger = $logger;
        $this->api = $api;
    }

    /**
     * Called when this object is called as a function
     *
     * @param Context $context
     * @param OptionsFactory $optionsFactory
     * @throws \Exception if haltOnError setting is true
     */
    public function __invoke(Context $context, OptionsFactory $optionsFactory)
    {
        $getopt = $context->getopt(array_keys(self::$options));

        // Get directory list
        $dirListFactory = new DirectoryListFactory();
        $dirListFactory->loadFromCommandline($getopt->get(), 2);
        $dirListFactory->loadFromStdin(new StdinReader(3));
        $dirList = $dirListFactory->getList();
        $this->logger->addDebug('Found directories', [count($dirList)]);

        // Load base options
        $baseOptions = $optionsFactory->newInstance();
        $baseOptions->validateAllRequired();

        if ($baseOptions->preview) {
            $this->logger->addNotice('PREVIEW MODE');
        }

        foreach ($dirList as $dir) {
            try {
                $this->logger->addNotice('In directory', [$dir]);
                $this->processDirectory($dir, $baseOptions, $optionsFactory);
            } catch (\Exception $e) {
                if ($baseOptions->haltOnError) {
                    throw $e;
                } elseif ($baseOptions->verbosity == 2) {
                    $this->logger->addError($e, []);
                } else {
                    $this->logger->addError($e->getMessage(), []);
                }
            }
        }
    }

    /**
     * Processes a directory according to the command
     *
     * @param string $dir The directory to process
     * @param Options $baseOptions
     * @param OptionsFactory $optionsFactory
     */
    abstract protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory);

    /**
     * Returns a list of the files that appear in the specified directory
     *
     * @param string $dir The directory from which to list files
     * @return array
     */
    protected function getFileList($dir)
    {
        // filter to only files, expand to absolute path
        return array_map('realpath', array_filter(glob($dir . '/*'), 'is_file'));
    }
}
