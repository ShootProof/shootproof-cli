<?php

namespace compwright\ShootproofCli\Test;

use \PHPUnit_Framework_TestCase;
use compwright\ShootproofCli\Utility\ResultPager;

class ResultPagerTest extends PHPUnit_Framework_TestCase
{
	protected $datasource = [];
	protected $expect = [];

	public function setUp()
	{
		$pages = 4;
		$pageSize = 5;
		$letters = range('A', 'Z');

		$this->expect = array_slice($letters, 0, $pages * $pageSize);

		for ($page = 0; $page < $pages; $page++)
		{
			for ($i = 0; $i < $pageSize; $i++)
			{
				$this->datasource[$page][] = $letters[($page * $pageSize) + $i];
			}
		}
	}

    public function testPager()
    {
    	$datasource = $this->datasource;
        $pager = new ResultPager(function($page) use ($datasource) {
        	return [
        		count($datasource), // total number of pages
        		$datasource[$page], // contents of the current page
        	];
        });

        $this->assertEquals($pager->getArrayCopy(), $this->expect);
    }
}
