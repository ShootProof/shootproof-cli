<?php

namespace ShootProof\Cli\Validators;

class TimezoneValidator implements ValidatorInterface
{
    public function __invoke($value, $setting = null, array $settings = [])
    {
        try {
            $tz = new \DateTimeZone($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
