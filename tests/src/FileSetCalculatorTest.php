<?php

namespace ShootProof\Cli\Test;

use \PHPUnit_Framework_TestCase;
use ShootProof\Cli\Utility\FileSetCalculator;

class FileSetCalculatorTest extends PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return [
            [
                'config' => [
                    'a' => ['A', 'B', 'C', 'D'],
                    'b' => ['E', 'F', 'G', 'H'],
                    'replace' => FALSE,
                ],
                'add' => ['A', 'B', 'C', 'D'],
                'remove' => ['E', 'F', 'G', 'H'],
                'replace' => [],
            ],
            [
                'config' => [
                    'a' => ['A', 'B', 'C', 'D'],
                    'b' => ['E', 'F', 'G', 'H'],
                    'replace' => TRUE,
                ],
                'add' => ['A', 'B', 'C', 'D'],
                'remove' => ['E', 'F', 'G', 'H'],
                'replace' => [],
            ],
            [
                'config' => [
                    'a' => ['A', 'B', 'C', 'D'],
                    'b' => ['C', 'D', 'E', 'F'],
                    'replace' => FALSE,
                ],
                'add' => ['A', 'B'],
                'remove' => ['E', 'F'],
                'replace' => [],
            ],
            [
                'config' => [
                    'a' => ['A', 'B', 'C', 'D'],
                    'b' => ['C', 'D', 'E', 'F'],
                    'replace' => TRUE,
                ],
                'add' => ['A', 'B'],
                'remove' => ['E', 'F'],
                'replace' => ['C', 'D'],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAdd($config, $add, $remove, $replace)
    {
        $calculator = new FileSetCalculator($config['a'], $config['b'], $config['replace']);
        $actual = $calculator->add();
        sort($actual);
        $this->assertEquals($add, $actual);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemove($config, $add, $remove, $replace)
    {
        $calculator = new FileSetCalculator($config['a'], $config['b'], $config['replace']);
        $actual = $calculator->remove();
        sort($actual);
        $this->assertEquals($remove, $actual);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReplace($config, $add, $remove, $replace)
    {
        $calculator = new FileSetCalculator($config['a'], $config['b'], $config['replace']);
        $actual = $calculator->replace();
        sort($actual);
        $this->assertEquals($replace, $actual);
    }
}
