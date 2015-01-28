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
 * Utility for writing .shootproof config files
 */
class ConfigWriter extends \ArrayObject
{
    /**
     * Converts array property keys stored on this object into a string representation
     *
     * @return string
     */
    public function __toString()
    {
        $contents = '';
        foreach ($this as $k => $v) {
            if (strpos($v, ' ') !== false) {
                // Quote value, escape quotes that happen to be in the value
                $v = '"' . str_replace('"', '\\"', $v) . '"';
            }

            $contents .= $k . '=' . $v . "\n";
        }

        return $contents;
    }

    /**
     * Writes the config properties stored on this object to the given file
     *
     * @param string $filepath Path to the local file to which this config should be written
     * @return boolean true when the file has been written to successfully
     * @throws \InvalidArgumentException if the file is not writable
     * @throws \RuntimeException if an error occurred while writing to the file
     */
    public function write($filepath)
    {
        if (! is_writable(dirname($filepath))) {
            throw new \InvalidArgumentException('File not writeable: ' . $filepath);
        }

        if (file_put_contents($filepath, (string) $this) !== false) {
            return true;
        } else {
            throw new \RuntimeException('An error occured while writing ' . $filepath);
        }
    }
}
