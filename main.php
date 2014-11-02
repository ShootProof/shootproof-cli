<?php

namespace compwright\ShootproofCli;

set_time_limit(0);

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\Utility\OptionTransformer;
use compwright\ShootproofCli\Validators\ValidatorException;
use Aura\Di\Container;
use Aura\Di\Factory as ContainerFactory;
use Aura\Cli\Status;
use Monolog\Logger;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\BufferHandler;
use Sp_Api;

// Set up the dependency injection container; we do it manually here instead of
// using the ContainerBuilder to prevent calling Container::lock()
$di = new Container(new ContainerFactory);
$di->newInstance('Aura\Cli\_Config\Common')->define($di);
$di->newInstance('compwright\ShootproofCli\DependencyConfig')->define($di);

// Catch errors
set_exception_handler(function(\Exception $e) use ($di)
{
	try
	{
		// ID10T
		if ($e instanceOf ValidatorException)
		{
			echo $e;
			$di->get('help')->__invoke();
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
$optionsFactory = $di->get('OptionsFactory');
$options = $optionsFactory->newInstance(array_keys($config['options']), $config['validators'], $config['defaults']);

// Configure Monolog for email reporting
$bufferedEmailLogger = new Logger('report');
$bufferedEmailLogger->pushHandler(
	new BufferHandler(
		new NativeMailerHandler($options->email, $options->emailSubject, $options->emailFrom, Logger::INFO)
	)
);
$di->set('Logger', $bufferedEmailLogger);
// Set the timezone
date_default_timezone_set($options->timezone);

// Configure the ShootProof API client
$api = new Sp_Api($options->accessToken);
$di->set('ShootproofApi', $api);

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
