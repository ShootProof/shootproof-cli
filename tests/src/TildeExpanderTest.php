<?php

namespace ShootProof\Cli\Test;

use \PHPUnit_Framework_TestCase;
use ShootProof\Cli\Utility\TildeExpander;

class TildeExpanderTest extends PHPUnit_Framework_TestCase
{
    public function testExpander()
    {
        $dir = '~/testDir';
        $expandedDir = posix_getpwuid(posix_getuid())['dir'] . '/testDir';
        $expander = new TildeExpander($dir);
        $this->assertEquals((string) $expander, $expandedDir);
    }
}
