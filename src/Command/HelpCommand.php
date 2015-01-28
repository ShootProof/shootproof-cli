<?php
/**
 * This file is part of the ShootProof command line tool.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) ShootProof, LLC (https://www.shootproof.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ShootProof\Cli\Command;

use Aura\Cli\Help;
use Aura\Cli\Stdio;

/**
 * Provides the shootproof-cli help command
 */
class HelpCommand implements HelpableCommandInterface
{
    /**
     * @var string
     */
    public static $usage = 'help [command]';

    /**
     * @var string
     */
    public static $description = <<<TEXT
Displays this help screen, or help for a specific command.

    Supported commands:

        help
        push
        pull
        accesslevel
TEXT;

    /**
     * Command line standard input/output
     * @var Stdio
     */
    protected $stdio;

    /**
     * Sets up the help message output for the help command
     *
     * @param Help $help The CLI help object used to configure help messages
     * @return Help
     */
    public static function configureHelp(Help $help)
    {
        $help->setUsage(self::$usage);
        $help->setDescr(self::$description);
        $help->setOptions([]);

        return $help;
    }

    /**
     * Constructs the help command
     *
     * @param Stdio $stdio Command line standard input/output
     */
    public function __construct(Stdio $stdio)
    {
        $this->stdio = $stdio;
    }

    /**
     * Called when this object is called as a function
     *
     * @param Help $help
     * @param HelpableCommandInterface $subCommand
     */
    public function __invoke(Help $help, HelpableCommandInterface $subCommand = null)
    {
        if ($subCommand !== null) {
            $subCommand->configureHelp($help);
        }

        $this->stdio->outln(
            $help->getHelp('shootproof-cli')
        );
    }
}
