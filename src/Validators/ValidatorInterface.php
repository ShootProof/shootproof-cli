<?php

namespace ShootProof\Cli\Validators;

interface ValidatorInterface
{
	public function __invoke($value, $setting = NULL, array $settings = []);
}
