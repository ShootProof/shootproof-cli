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

/**
 * Enforces an interface for shootproof-cli commands that need help messages
 */
interface HelpableCommandInterface
{
    /**
     * Sets up CLI help messages to display when using the "help" command
     *
     * @param Help $help The CLI help object used to configure help messages
     * @return Help
     */
    public static function configureHelp(Help $help);
}
