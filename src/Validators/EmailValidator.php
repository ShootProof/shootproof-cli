<?php

namespace compwright\ShootproofCli\Validators;

class EmailValidator implements ValidatorInterface
{
	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		return (
			$value === filter_var($value, FILTER_VALIDATE_EMAIL)
		);
	}
}
