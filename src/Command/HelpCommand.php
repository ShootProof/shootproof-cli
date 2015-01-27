<?php

namespace ShootProof\Cli\Command;

use Aura\Cli\Help;
use Aura\Cli\Stdio;

class HelpCommand implements HelpableCommandInterface
{
    public static $usage = 'help [command]';

    public static $description = <<<TEXT
Displays this help screen, or help for a specific command.

    Supported commands:

        help
        push
        pull
        accesslevel
TEXT;

    protected $stdio;

    public static function configureHelp(Help $help)
    {
        $help->setUsage(self::$usage);
        $help->setDescr(self::$description);
        $help->setOptions([]);
        return $help;
    }

    public function __construct(Stdio $stdio)
    {
        $this->stdio = $stdio;
    }

    public function __invoke(Help $help, $subCommand = null)
    {
        if ($subCommand instanceof HelpableCommandInterface) {
            $subCommand->configureHelp($help);
        }

        $this->stdio->outln(
            $help->getHelp('shootproof-cli')
        );
    }
}
