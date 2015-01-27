<?php

namespace ShootProof\Cli\Validators;

interface ValidatorInterface
{
    public function __invoke($value, $setting = null, array $settings = []);
}
