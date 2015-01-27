<?php

namespace ShootProof\Cli\Validators;

class RequiredValidator implements ValidatorInterface
{
    protected $strict;

    public function __construct($strict = false)
    {
        $this->strict = $strict;
    }

    public function __invoke($value, $setting = null, array $settings = [])
    {
        if (! isset($settings[$setting])) {
            return false;
        }

        if (! $this->strict) {
            return (boolean) $value;
        } else {
            return true;
        }
    }
}
