<?php

namespace ShootProof\Cli\Utility;

class ResultPager extends \ArrayObject
{
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
