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

namespace ShootProof\Cli\Utility;

class FileSetCalculator
{
    protected $a;
    protected $b;
    protected $replace;

    public function __construct(array $a, array $b, $replace = true)
    {
        $this->a = $a;
        $this->b = $b;
        $this->replace = $replace;
    }

    public function add()
    {
        return array_diff($this->a, $this->b);
    }

    public function remove()
    {
        return array_diff($this->b, $this->a);
    }

    public function replace()
    {
        return $this->replace ? array_intersect($this->a, $this->b) : [];
    }
}
