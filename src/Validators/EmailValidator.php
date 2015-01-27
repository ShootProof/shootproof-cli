<?php

namespace ShootProof\Cli\Validators;

class EmailValidator implements ValidatorInterface
{
    public function __invoke($value, $setting = null, array $settings = [])
    {
        return (
            $value === filter_var($value, FILTER_VALIDATE_EMAIL)
        );
    }
}
