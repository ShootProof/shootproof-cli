<?php

namespace ShootProof\Cli\Command;

use ShootProof\Cli\Options;
use ShootProof\Cli\OptionsFactory;
use ShootProof\Cli\Utility\DirectoryListFactory;
use ShootProof\Cli\Utility\StdinReader;
use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Monolog\Logger;
use Sp_Api as ShootproofApi;

abstract class BaseCommand
{
    protected $stdio;
    protected $logger;
    protected $api;

    static protected $options = [];

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

    abstract protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory);

    protected function getFileList($dir)
    {
        // filter to only files, expand to absolute path
        return array_map('realpath', array_filter(glob($dir . '/*'), 'is_file'));
    }
}
