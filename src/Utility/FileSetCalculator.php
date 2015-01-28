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

/**
 * Utility to compute files to add, remove, or replace
 */
class FileSetCalculator
{
    /**
     * An array of file paths for comparison
     * @var array
     */
    protected $a;

    /**
     * An array of file paths for comparison
     * @var array
     */
    protected $b;

    /**
     * @var boolean
     */
    protected $replace;

    /**
     * Constructs a file calculator object
     *
     * @param array $a A list of files to compare against $b
     * @param array $b A list of files to compare against $a
     * @param boolean $replace Whether to allow replacements
     */
    public function __construct(array $a, array $b, $replace = true)
    {
        $this->a = $a;
        $this->b = $b;
        $this->replace = $replace;
    }

    /**
     * Returns a list of all files in $a that are not in $b; these are files we
     * might want to add
     *
     * @return array
     */
    public function add()
    {
        return array_diff($this->a, $this->b);
    }

    /**
     * Returns a list of all files in $b that are not in $a; these are files we
     * might want to remove
     *
     * @return array
     */
    public function remove()
    {
        return array_diff($this->b, $this->a);
    }

    /**
     * Returns a list of all files in $a that are also in $b; these are files we
     * might want to replace
     *
     * Returns an empty array if this object was created with $replace as false.
     *
     * @return array
     */
    public function replace()
    {
        return $this->replace ? array_intersect($this->a, $this->b) : [];
    }
}
