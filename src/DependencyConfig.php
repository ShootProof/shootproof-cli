<?php

namespace compwright\ShootproofCli;

use Aura\Di\Config;
use Aura\Di\Container;

class DependencyConfig extends Config
{
	public function define(Container $di)
	{
		// Typehinted dependency resolution for command classes
		$di->types['Context'] = $di->lazyNew('Aura\Cli\Context');
		$di->types['Stdio'] = $di->lazyNew('Aura\Cli\Stdio');
		$di->types['Logger'] = $di->lazyGet('Logger');
		$di->types['Help'] = $di->lazyGet('Help');
		$di->types['Sp_Api'] = $di->lazyGet('ShootproofApi');

		// Map CLI keywords to command classes
		$di->set('push', $di->lazyNew('compwright\ShootproofCli\Command\PushCommand'));
		$di->set('pull', $di->lazyNew('compwright\ShootproofCli\Command\PullCommand'));
		$di->set('accesslevel', $di->lazyNew('compwright\ShootproofCli\Command\AccesslevelCommand'));
		$di->set('help', $di->lazyNew('compwright\ShootproofCli\Command\HelpCommand'));
	}
}
