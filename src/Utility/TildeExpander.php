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
 * Utility to expand tilde file paths into full paths
 *
 * @uses POSIX functions, which are NOT available on Windows
 */
class TildeExpander
{
    /**
     * The file path to expand
     * @var string
     */
    protected $path;

    /**
     * The current system user's home directory
     * @var string
     */
    protected $homedir;

    /**
     * Constructs a tilde expander object
     *
     * @param string $path The file path to expand
     * @throws \InvalidArgumentException if $path is an array
     */
    public function __construct($path)
    {
        if (is_array($path)) {
            throw new \InvalidArgumentException('$path must be a string');
        }

        $this->path = $path;
        $info = posix_getpwuid(posix_getuid());
        $this->homedir = $info['dir'];
    }

    /**
     * Converts this object to a string representation of the expanded path
     *
     * @return string
     */
    public function __toString()
    {
        return str_replace('~', $this->homedir, $this->path);
    }
}
