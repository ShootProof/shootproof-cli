<?php

namespace ShootProof\Cli\Utility;

class ConfigWriter extends \ArrayObject
{
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
