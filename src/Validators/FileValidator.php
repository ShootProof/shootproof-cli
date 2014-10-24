<?php

namespace compwright\ShootproofCli\Validators;

class FileValidator implements ValidatorInterface
{
	public function __invoke($value, $setting, array $settings)
	{
		return (
			is_file($value) &&
			file_exists($value) &&
			is_readable($value)
		);
	}
}
