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

use ShootProof\Cli\Utility\TildeExpander;

/**
 * Validates a file exists and is readable
 */
class FileValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($value, $setting = null, array $settings = [])
    {
        $value = (string) new TildeExpander($value);

        return (
            is_file($value) &&
            file_exists($value) &&
            is_readable($value)
        );
    }
}
