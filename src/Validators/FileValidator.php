<?php

namespace ShootProof\Cli\Validators;

use ShootProof\Cli\Utility\TildeExpander;

class FileValidator implements ValidatorInterface
{
    public function __invoke($value, $setting = null, array $settings = [])
    {
        $value = (string) new TildeExpander($value);

        return (
            is_file($value) &&
            file_exists($value) &&
            is_readable($value)
        );
    }
}
