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

namespace ShootProof\Cli\Validators;

/**
 * Enforces an interface for all validators
 */
interface ValidatorInterface
{
    /**
     * Called when this object is called as a function
     *
     * @param mixed $value The value to validate
     * @param string $setting
     * @param array $settings
     * @return boolean True if the $value is valid, false otherwise
     */
    public function __invoke($value, $setting = null, array $settings = []);
}
