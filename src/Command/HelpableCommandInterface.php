<?php

namespace ShootProof\Cli\Command;

use Aura\Cli\Help;

interface HelpableCommandInterface
{
	static public function configureHelp(Help $help);
}
