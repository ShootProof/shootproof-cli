<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\OptionsFactory;
use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Monolog\Logger;
use Sp_Api as ShootproofApi;

abstract class BaseCommand
{
	public static $usage = 'command [options] [<dir>]';
	public static $description = '';
	public static $options = [];

	protected $stdio;
	protected $logger;
	protected $api;

	public function __construct(Stdio $stdio, Logger $logger, ShootproofApi $api)
	{
		$this->stdio = $stdio;
		$this->logger = $logger;
		$this->api = $api;
	}

	abstract static function configureOptions(Options $options, ShootproofApi $api);

	abstract public function __invoke(Context $context, OptionsFactory $optionsFactory);
}
