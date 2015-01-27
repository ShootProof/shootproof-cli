<?php

namespace ShootProof\Cli\Command;

use Aura\Cli\Help;

interface HelpableCommandInterface
{
    public static function configureHelp(Help $help);
}
