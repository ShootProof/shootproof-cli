<?php

namespace compwright\ShootproofCli;

set_time_limit(0);
date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\Utility\OptionTransformer;
use compwright\ShootproofCli\Validators\ValidatorException;
use Aura\Di\Container;
use Aura\Di\Factory as ContainerFactory;
use Aura\Cli\Status;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Formatter\LineFormatter;
use Sp_Api;

// Set up the dependency injection container; we do it manually here instead of
// using the ContainerBuilder to prevent calling Container::lock()
$di = new Container(new ContainerFactory);
$di->newInstance('Aura\Cli\_Config\Common')->define($di);

// Parse the command line
$context = $di->newInstance('Aura\Cli\Context');
$getopt = $context->getopt(array_keys($config['options']));

// Set up the options container
$optionsFactory = $di->newInstance('compwright\ShootproofCli\OptionsFactory');
$options = $optionsFactory->newInstance(array_keys($config['options']), $config['validators'], $config['defaults']);

// Set the timezone
date_default_timezone_set($options->timezone);

// Configure Monolog
$logger = new Logger($config['monologChannel']);
$formatter = new LineFormatter($config['monologFormat']);
$handler = new StreamHandler("php://stdout", $config['monologVerbosity'][$options->verbosity]);
$handler->setFormatter($formatter);
$logger->pushHandler($handler); // log to screen

// Send an email report when the script ends
if ($options->email)
{
	$handler = new NativeMailerHandler($options->email, $options->emailSubject, $options->emailFrom, Logger::NOTICE);
	$handler->setFormatter($formatter);
	$logger->pushHandler(new BufferHandler($handler));
}

// Use Monolog as the error handler
ErrorHandler::register($logger);

// Misc. debug messages
$logger->addDebug('Timezone set', [$options->timezone]);
if ($optionsFactory->getLastError())
{
	$logger->addDebug('Config file not found', [$options->config]);
}
else
{
	$logger->addDebug('Config file found', [$options->config]);
}
if ($options->email)
{
	$logger->addDebug('Will send email report', [
		'to' => $options->email,
		'from' => $options->emailFrom,
		'subject' => $options->emailSubject,
	]);
}

// Configure the ShootProof API client
$api = new Sp_Api($options->accessToken);

// Finish configuring the DI container, and lock it
$di->set('Logger', $logger);
$di->set('ShootproofApi', $api);
$di->newInstance('compwright\ShootproofCli\DependencyConfig')->define($di);
$di->lock();

// Configure the Help writer
$help = $di->get('Aura\Cli\Help');
$help->setSummary($config['summary']);
$help->setUsage($config['usage']);
$help->setDescr($config['description']);
$help->setOptions($config['options']);

// Dispatch command
$commandName = $getopt->get(1);
if ($commandName === 'help')
{
	$helpCommand = $di->get($commandName);

	// handle help subcommands
	$subCommandName = $getopt->get(2);
	if ($subCommandName)
	{
		if ($di->has($subCommandName))
		{
			// Show subcommand help
			$subCommand = $di->get($subCommandName);
			$helpCommand($help, $subCommand);
			exit(Status::SUCCESS);
		}
		else
		{
			// Invalid subcommand; show help command help
			$helpCommand($help, $helpCommand);
			exit(Status::USAGE);
		}
	}
	else
	{
		// No subcommand
		$helpCommand($help, $helpCommand);
		exit(Status::SUCCESS);
	}
}
elseif ($di->has($commandName))
{
	$logger->addNotice('Executing command', [$commandName]);

	// Each command has its own set of options, but will need the general options too, so
	// we inject the base config which will be extended with the command's own config
	$optionsFactory->setBaseConfig(
		array_keys($config['options']),
		$config['validators'],
		$config['defaults']
	);

	$command = $di->get($commandName);
	$command($context, $optionsFactory);
	exit(Status::SUCCESS);
}
else
{
	// No command, show main help
	$di->get('help')->__invoke($help);
	exit(Status::NOINPUT);
}
