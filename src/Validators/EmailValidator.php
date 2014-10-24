<?php

namespace compwright\ShootproofCli\Validators;

class EmailValidator implements ValidatorInterface
{
	public function __invoke($value, $setting, array $settings)
	{
		return TRUE;
	}
}
