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

trait HelpableCommandTrait
{
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
