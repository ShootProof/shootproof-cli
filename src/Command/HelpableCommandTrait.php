<?php

namespace compwright\ShootproofCli\Command;

use Aura\Cli\Help;

trait HelpableCommandTrait
{
	static public function configureHelp(Help $help)
	{
		if (isset(self::$usage))
		{
			$help->setUsage(self::$usage);
		}
		
		if (isset(self::$description))
		{
			$help->setDescr(self::$description);
		}

		if (isset(self::$options))
		{
			$options = array_merge($help->getOptions(), self::$options);
			$help->setOptions($options);
		}

		return $help;
	}
}
