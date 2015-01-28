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

class OptionTransformer extends \ArrayObject
{
    public function __construct(array $input = [])
    {
        $transformed = [];

        foreach ($input as $key => $value) {
            $tkey = $this->transformKey($key);
            $transformed[$tkey] = $value;
        }

        parent::__construct($transformed);
    }

    public function transformKey($key)
    {
        // --long-option -> longOption
        $key = ltrim($key, '-');
        return preg_replace_callback('/-(.?)/', [$this, 'capitalize'], $key);
    }

    public function untransformKey($key)
    {
        // longOption -> long-option
        return ltrim(preg_replace_callback('/([A-Z])/', [$this, 'uncapitalize'], $key), '-');
    }

    protected function capitalize($matches)
    {
        return strtoupper($matches[1]);
    }

    protected function uncapitalize($matches)
    {
        return '-' . strtolower($matches[1]);
    }
}
