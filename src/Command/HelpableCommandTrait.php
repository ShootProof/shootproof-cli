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
 * Applies common help command functionality to all classes using this trait
 */
trait HelpableCommandTrait
{
    /**
     * Sets up CLI help messages to display when using the "help" command
     *
     * @param Help $help The CLI help object used to configure help messages
     * @return Help
     */
    static public function configureHelp(Help $help)
    {
        if (isset(self::$usage)) {
            $help->setUsage(self::$usage);
        }

        if (isset(self::$description)) {
            $help->setDescr(self::$description);
        }

        if (isset(self::$options)) {
            $options = array_merge($help->getOptions(), self::$options);
            $help->setOptions($options);
        }

        return $help;
    }
}
