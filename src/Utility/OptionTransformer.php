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
 * Utility to transform option key names
 */
class OptionTransformer extends \ArrayObject
{
    /**
     * Constructs an option transformer object
     *
     * @param array $input An array of options to store on this object
     */
    public function __construct(array $input = [])
    {
        $transformed = [];

        foreach ($input as $key => $value) {
            $tkey = $this->transformKey($key);
            $transformed[$tkey] = $value;
        }

        parent::__construct($transformed);
    }

    /**
     * Transforms $key from --long-option format into longOption
     *
     * @param string $key The option key to transform
     * @return string
     */
    public function transformKey($key)
    {
        $key = ltrim($key, '-');
        return preg_replace_callback('/-(.?)/', [$this, 'capitalize'], $key);
    }

    /**
     * "Untransforms" $key from longOption format into long-option
     *
     * @param string $key The option key to "untransform"
     * @return string
     */
    public function untransformKey($key)
    {
        return ltrim(preg_replace_callback('/([A-Z])/', [$this, 'uncapitalize'], $key), '-');
    }

    /**
     * Capitalizes a string and returns it
     *
     * @param array $matches Array of matches from preg_replace
     * @return string
     */
    protected function capitalize($matches)
    {
        return strtoupper($matches[1]);
    }

    /**
     * Lowercases a string and returns it
     *
     * @param array $matches Array of matches from preg_replace
     * @return string
     */
    protected function uncapitalize($matches)
    {
        return '-' . strtolower($matches[1]);
    }
}
