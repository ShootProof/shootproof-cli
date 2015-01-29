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
 * Validates value falls within (or is equal to) the start and end ranges
 */
class RangeValidator implements ValidatorInterface
{
    /**
     * Low end of the range
     * @var int
     */
    protected $start;

    /**
     * High end of the range
     * @var int
     */
    protected $end;

    /**
     * @param int $start The low end of the range to test
     * @param int $end The high end of the range to test
     */
    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($value, $setting = null, array $settings = [])
    {
        return (
            $value >= $this->start &&
            $value <= $this->end
        );
    }
}
