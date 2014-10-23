<?php

namespace compwright\ShootproofCli\Command;

use Aura\Cli\Help;
use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Monolog\Logger;

class HelpCommand
{
	public static $usage = 'help [command]';

	public static $description = <<<TEXT
Displays this help screen, or help for a specific command.

Supported commands:

  help
  push
  pull
  accesslevel
TEXT;

	public static $options = [];

	protected $help;

	public function __construct(Stdio $stdio, Logger $logger, Help $help)
	{
		parent::__construct($stdio, $logger);
		$this->help = $help;
	}

	public function __invoke(BaseCommand $subCommand = NULL)
	{
		if ($subCommand)
		{
			$this->help->setUsage($subCommand::$usage);
			$this->help->setDescr($subCommand::$description);
			$this->help->setOptions($subCommand::$options);
		}

		$this->stdio->outln(
			$this->help->getHelp('shootproof-cli.phar')
		);
	}
}
