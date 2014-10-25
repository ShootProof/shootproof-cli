<?php

namespace compwright\ShootproofCli\Test;

use \PHPUnit_Framework_TestCase;
use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\Validators\EmailValidator;
use compwright\ShootproofCli\Validators\RequiredValidator;

class OptionsTest extends PHPUnit_Framework_TestCase
{
    protected $options;

    public function setUp()
    {
        $validators = [
            'A' => [
                new RequiredValidator,
                new EmailValidator
            ],
            'B' => new EmailValidator,
            'C' => new RequiredValidator,
            'D' => new RequiredValidator,
        ];

        $defaults = [
            'D' => 'testD',
        ];

        $this->options = new Options($validators, $defaults);
        $this->options->throwExceptions(FALSE);
    }

    public function testLoadValidateGet()
    {
        $data = [
            'A' => 'test@domain.com',
            'B' => 'test2@domain.com',
        ];

        $expected = [
            'A' => 'test@domain.com',
            'B' => 'test2@domain.com',
            'D' => 'testD',
        ];

        $this->options->loadOptionData($data, FALSE, TRUE);
        $this->assertTrue(isset($this->options->A));
        $this->assertEquals($expected['A'], $this->options->A);
        $this->assertEquals($expected, $this->options->asArray());

        // Overwrite B with an invalid value
        $this->options->loadOptionData([ 'B' => 'invalid' ], TRUE, FALSE);
        $this->assertTrue(isset($this->options->B));
        $this->assertEquals($expected['B'], $this->options->B);
        $this->assertEquals($expected, $this->options->asArray());

        // Overwrite B with a valid value
        $this->options->loadOptionData([ 'B' => 'valid@domain.com' ], TRUE, FALSE);
        $this->assertTrue(isset($this->options->B));
        $this->assertNotEquals($expected['B'], $this->options->B);
        $this->assertNotEquals($expected, $this->options->asArray());

        // Block overwriting B with a valid value
        $this->options->loadOptionData([ 'B' => $data['B'] ], FALSE, FALSE);
        $this->assertTrue(isset($this->options->B));
        $this->assertNotEquals($expected['B'], $this->options->B);
        $this->assertNotEquals($expected, $this->options->asArray());

        // Get value that isn't set
        $this->assertFalse(isset($this->options->C));
        $this->assertNull($this->options->C);

        // Get default value
        $this->assertFalse(isset($this->options->D));
        $this->assertEquals($expected['D'], $this->options->D);

        // Validate all required
        $this->assertFalse($this->options->validateAllRequired());
        $this->options->loadOptionData([ 'C' => 'testC' ], FALSE, FALSE);
        $this->assertTrue($this->options->validateAllRequired());
    }
}
