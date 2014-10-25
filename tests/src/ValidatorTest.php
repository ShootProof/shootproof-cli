<?php

namespace compwright\ShootproofCli\Test;

use \PHPUnit_Framework_TestCase;
use compwright\ShootproofCli\Validators\EmailValidator;
use compwright\ShootproofCli\Validators\FileValidator;
use compwright\ShootproofCli\Validators\RangeValidator;
use compwright\ShootproofCli\Validators\RequiredValidator;
use compwright\ShootproofCli\Validators\ValuesValidator;
use compwright\ShootproofCli\Validators\ValidatorInterface;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return [
            [ new EmailValidator, 'test@domain.com', TRUE ],
            [ new EmailValidator, 'test@domain.co', TRUE ],
            [ new EmailValidator, 'test@domain.', FALSE ],
            [ new EmailValidator, 'test@domain', FALSE ],
            [ new EmailValidator, 'test@', FALSE ],
            [ new EmailValidator, 'test', FALSE ],
            [ new EmailValidator, 'test.com', FALSE ],
            [ new EmailValidator, '', FALSE ],
            [ new FileValidator, __FILE__, TRUE ],
            [ new FileValidator, __DIR__ . '/null', FALSE ],
            [ new FileValidator, __DIR__, FALSE ],
            [ new FileValidator, NULL, FALSE ],
            [ new RangeValidator(5, 10), 5, TRUE ],
            [ new RangeValidator(5, 10), 1, FALSE ],
            [ new RangeValidator(5, 10), 20, FALSE ],
            [ new RangeValidator(0, 0), NULL, TRUE ],
            [ new ValuesValidator(['A', 'B', 'C']), 'A', TRUE ],
            [ new ValuesValidator(['A', 'B', 'C']), 'D', FALSE ],
            [ new ValuesValidator(['A', 'B', 'C']), '', FALSE ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidator($validator, $value, $expected)
    {
        $this->assertTrue($validator instanceof ValidatorInterface);
        $this->assertEquals($expected, $validator($value));
    }

    public function requiredDataProvider()
    {
        return [
            [ new RequiredValidator(TRUE), 'asdf', 'A', [ 'A' => 'asdf'], TRUE ],
            [ new RequiredValidator(FALSE), 'asdf', 'A', [ 'A' => 'asdf'], TRUE ],
            [ new RequiredValidator(TRUE), '', 'A', [ 'A' => ''], TRUE ],
            [ new RequiredValidator(FALSE), '', 'A', [ 'A' => ''], FALSE ],
            [ new RequiredValidator(TRUE), 'asdf', '', [], FALSE ],
            [ new RequiredValidator(FALSE), 'asdf', '', [], FALSE ],
        ];
    }

    /**
     * @dataProvider requiredDataProvider
     */
    public function testRequiredValidator($validator, $value, $setting, $settings, $expected)
    {
        $this->assertTrue($validator instanceof ValidatorInterface);
        $this->assertEquals($expected, $validator($value, $setting, $settings));
    }
}
