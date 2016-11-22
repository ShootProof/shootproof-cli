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

namespace ShootProof\Cli;

use Aura\Di\Config;
use Aura\Di\Container;

/**
 * Dependency injection container configuration
 */
class DependencyConfig extends Config
{
    /**
     * Defines configuration for the dependency injection container
     *
     * @param Container $di Aura dependency injection container
     */
    public function define(Container $di)
    {
        $di->set('Aura\Cli\Context', $di->lazyNew('Aura\Cli\Context'));
        $di->set('Aura\Cli\Help', $di->lazyNew('Aura\Cli\Help'));

        // Typehinted dependency resolution for command classes
        $di->params['ShootProof\Cli\Command\BaseCommand']['stdio'] = $di->lazyNew('Aura\Cli\Stdio');
        $di->params['ShootProof\Cli\Command\BaseCommand']['api'] = $di->get('ShootProofApi');
        $di->params['ShootProof\Cli\Command\BaseCommand']['logger'] = $di->get('Logger');
        $di->types['Help'] = $di->lazyGet('Help');

        // Map CLI keywords to command classes
        $di->set('push', $di->lazyNew('ShootProof\Cli\Command\PushCommand'));
        $di->set('pull', $di->lazyNew('ShootProof\Cli\Command\PullCommand'));
        $di->set('accesslevel', $di->lazyNew('ShootProof\Cli\Command\AccesslevelCommand'));
        $di->set('brands', $di->lazyNew('ShootProof\Cli\Command\BrandsCommand'));
        $di->set('help', $di->lazyNew('ShootProof\Cli\Command\HelpCommand'));
    }
}
