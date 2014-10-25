<?php

namespace compwright\ShootproofCli\Test;

use \PHPUnit_Framework_TestCase;
use compwright\ShootproofCli\Utility\OptionTransformer;

class OptionTransformerTest extends PHPUnit_Framework_TestCase
{
    public function testTransform()
    {
        $input = [
        	'optionA' => 'valueA',
        	'option-b' => 'valueB',
        	'--option-c' => 'valueC',
        	'--option_d' => 'valueD',
        ];

        $expected = [
        	'optionA' => 'valueA',
        	'optionB' => 'valueB',
        	'optionC' => 'valueC',
        	'option_d' => 'valueD',
        ];

        $transformer = new OptionTransformer($input);

        $this->assertInstanceOf('ArrayObject', $transformer);
        $this->assertEquals($expected, $transformer->getArrayCopy());
    }
}
