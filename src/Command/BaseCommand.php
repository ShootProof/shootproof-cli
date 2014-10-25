<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\OptionsFactory;
use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Monolog\Logger;
use Sp_Api as ShootproofApi;

abstract class BaseCommand
{
	protected $stdio;
	protected $logger;
	protected $api;

	public function __construct(Stdio $stdio, Logger $logger, ShootproofApi $api)
	{
		$this->stdio = $stdio;
		$this->logger = $logger;
		$this->api = $api;
	}

	abstract public function __invoke(Context $context, OptionsFactory $optionsFactory);
}
