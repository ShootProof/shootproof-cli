<?php

namespace ShootProof\Cli\Test;

use \PHPUnit_Framework_TestCase;
use ShootProof\Cli\Utility\ConfigWriter;

class ConfigWriterTest extends PHPUnit_Framework_TestCase
{
    protected $writer;

    public function setUp()
    {
        $this->writer = new ConfigWriter([
            'foo' => 'bar',
            'SOMETHING' => 'Something\'s happening',
            'quotedText' => '"He said," she said.',
        ]);
    }

    public function testWriter()
    {
        $file = tempnam(sys_get_temp_dir(), 'ConfigWriterTest');
        unlink($file);

    	$expected = <<<TXT
foo=bar
SOMETHING="Something's happening"
quotedText="\"He said,\" she said."

TXT;

		$this->assertFalse(file_exists($file));
    	$this->assertTrue($this->writer->write($file));
    	$this->assertTrue(file_exists($file));
    	$this->assertEquals(file_get_contents($file), $expected);

    	unlink($file);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWriterUnwritable()
    {
        $file = __DIR__ . '/asdfasdfasdf/test.dat';
        $writer = new ConfigWriter(['foo' => 'bar']);
        $writer->write($file);
    }
}
