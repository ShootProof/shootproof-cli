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

class TildeExpander
{
    protected $path;
    protected $homedir;

    public function __construct($path)
    {
        if (is_array($path)) {
            throw new \InvalidArgumentException('$path must be a string');
        }

        $this->path = $path;
        $info = posix_getpwuid(posix_getuid());
        $this->homedir = $info['dir'];
    }

    public function __toString()
    {
        return str_replace('~', $this->homedir, $this->path);
    }
}
