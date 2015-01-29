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
 * Validates the presence of a required value
 */
class RequiredValidator implements ValidatorInterface
{
    /**
     * Whether to test for strictness
     * @var boolean
     */
    protected $strict;

    /**
     * Constructs a required validator
     *
     * If $strict is true, then when the value is present but equal to an
     * empty value (for the PHP definition of emptiness, see {@link http://php.net/empty empty()}),
     * the validator will pass as true.
     *
     * However, if strict is false, we will also test that the value provided
     * does not evaluate to false. If it does, then validation will fail.
     *
     * By default, this does not do a strict check, so values that evaluate to
     * boolean false will fail validation as a required value.
     *
     * @param boolean $strict Whether to test for strictness
     */
    public function __construct($strict = false)
    {
        $this->strict = $strict;
    }

    /**
     * {@inheritdoc}
     */
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
