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

class RequiredValidator implements ValidatorInterface
{
    protected $strict;

    public function __construct($strict = false)
    {
        $this->strict = $strict;
    }

    public function __invoke($value, $setting = null, array $settings = [])
    {
        if (! isset($settings[$setting])) {
            return false;
        }

        if (! $this->strict) {
            return (boolean) $value;
        } else {
            return true;
        }
    }
}
