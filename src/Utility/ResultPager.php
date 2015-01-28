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
 * Utility to build a full list of results from pages of results
 */
class ResultPager extends \ArrayObject
{
    /**
     * Constructs a result pager object
     *
     * @param callable $fetch A callback returning an ordered array of [total pages, current page results]
     */
    public function __construct(callable $fetch)
    {
        $resultset = [];

        for ($pages = 1, $page = 0; $page < $pages; $page++) {
            list($pages, $results) = $fetch($page);
            $resultset = array_merge($resultset, $results);
        }

        parent::__construct($resultset);
    }
}
