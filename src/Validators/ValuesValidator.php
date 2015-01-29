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
 * Validates the value is in a list of valid values
 */
class ValuesValidator implements ValidatorInterface
{
    /**
     * Values considered valid
     * @var array
     */
    protected $values = [];

    /**
     * @param array $values Valid values to test against
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($value, $setting = null, array $settings = [])
    {
        return in_array($value, $this->values);
    }
}
