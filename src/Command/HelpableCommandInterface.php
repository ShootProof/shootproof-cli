<?php

namespace compwright\ShootproofCli\Command;

use Aura\Cli\Help;

interface HelpableCommandInterface
{
	static public function configureHelp(Help $help);
}
