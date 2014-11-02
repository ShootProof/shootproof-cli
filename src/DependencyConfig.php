<?php

namespace compwright\ShootproofCli;

use Aura\Di\Config;
use Aura\Di\Container;

class DependencyConfig extends Config
{
	public function define(Container $di)
	{
		$di->set('Aura\Cli\Context', $di->lazyNew('Aura\Cli\Context'));
		$di->set('Aura\Cli\Help', $di->lazyNew('Aura\Cli\Help'));

		// Typehinted dependency resolution for command classes
		$di->params['compwright\ShootproofCli\Command\BaseCommand']['stdio'] = $di->lazyNew('Aura\Cli\Stdio');
		$di->params['compwright\ShootproofCli\Command\BaseCommand']['api'] = $di->get('ShootproofApi');
		$di->params['compwright\ShootproofCli\Command\BaseCommand']['logger'] = $di->get('Logger');
		$di->types['Help'] = $di->lazyGet('Help');

		// Map CLI keywords to command classes
		$di->set('push', $di->lazyNew('compwright\ShootproofCli\Command\PushCommand'));
		$di->set('pull', $di->lazyNew('compwright\ShootproofCli\Command\PullCommand'));
		$di->set('accesslevel', $di->lazyNew('compwright\ShootproofCli\Command\AccesslevelCommand'));
		$di->set('help', $di->lazyNew('compwright\ShootproofCli\Command\HelpCommand'));
	}
}
