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

    /**
     * @dataProvider dataProvider
     */
    public function testUntransform($input, $expected)
    {
        $transformer = new OptionTransformer;
        $this->assertEquals($expected, $transformer->untransformKey($input));
    }

    public function dataProvider()
    {
        return [
            [ 'Option', 'option' ],
            [ 'optionA', 'option-a' ],
            [ 'option-b', 'option-b' ],
            [ 'option_c', 'option_c' ],
            [ 'OptionD', 'option-d' ],
        ];
    }
}
