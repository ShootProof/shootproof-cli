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

class ValuesValidator implements ValidatorInterface
{
    protected $values = [];

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function __invoke($value, $setting = null, array $settings = [])
    {
        return in_array($value, $this->values);
    }
}
