<?php

namespace compwright\ShootproofCli;

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\Utility\TildeExpander;
use compwright\ShootproofCli\Utility\OptionTransformer;
use Aura\Di\ContainerBuilder;
use Aura\Cli\Status;
use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\BufferHandler;
use Sp_Api;

// Set up the dependency injection container
$diFactory = new ContainerBuilder;
$di = $diFactory->newInstance([], [
	'Aura\Cli\_Config\Common',
	'compwright\ShootproofCli\DependencyConfig',
]);

// Catch errors
set_exception_handler(function(\Exception $e) use ($di)
{
	try
	{
		// ID10T
		if ($e instanceOf ValidationException)
		{
			$di->get('help')();
			exit(Status::USAGE);
		}

		// Unhandled exception
		fputs(STDERR, $e);
		exit(Status::SOFTWARE);		
	}
	catch (\Exception $e)
	{
		// Exception occured inside the exception handler
		fputs(STDERR, $e);
		exit(Status::SOFTWARE);
	}
});

// Parse the command line
$context = $di->get('Aura\Cli\Context');
$getopt = $context->getopt(array_keys($config['options']));

// Set up the options container
$optionsFactory = $di->get('compwright\ShootproofCli\OptionsFactory');
$options = $optionsFactory->newInstance(array_keys($config['options']), $config['validators'], $config['defaults']);

// Configure Monolog for email reporting
$bufferedEmailLogger = new Logger('report');
$bufferedEmailLogger->pushHandler(
	new BufferHandler(
		new NativeMailerHandler($options->email, $options->emailSubject, $options->emailFrom, Logger::INFO)
	)
);
$di->set('Logger', $bufferedEmailLogger);

// Configure the ShootProof API client
$api = new Sp_Api($options->accessToken);
$di->set('ShootproofApi', $api);

// Configure the Help writer
$help = $di->get('Aura\Cli\Help');
$help->setSummary($config['summary']);
$help->setUsage($config['usage']);
$help->setDescr($config['description']);
$help->setOptions($config['options']);
$di->set('Help', $help);

// Dispatch command
$command = $getopt->get(1);
if ($command === 'help')
{
	// handle help subcommands
	$subCommand = $getopt->get(2);
	if ($subCommand)
	{
		if ($di->has($subCommand))
		{
			// Show subcommand help
			$commandClass($subCommandClass);
			exit(Status::SUCCESS);
		}
		else
		{
			// Invalid subcommand; show help command help
			$commandClass($commandClass);
			exit(Status::USAGE);
		}
	}
	else
	{
		// No subcommand
		$commandClass($commandClass);
		exit(Status::SUCCESS);
	}
}
elseif ($di->has($command))
{
	// Each command has its own set of options, but will need the general options too, so
	// we inject the base config which will be extended with the command's own config
	$optionsFactory->setBaseConfig(array_keys($config['options']), $config['validators'], $config['defaults']);

	$commandClass = $di->get($command);
	$commandClass($context, $optionsFactory);
	exit(Status::SUCCESS);
}
else
{
	// No command, show main help
	$di->get('help')();
	exit(Status::NOINPUT);
}
