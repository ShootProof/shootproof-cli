<?php

namespace compwright\ShootproofCli\Validators;

use compwright\ShootproofCli\Utility\TildeExpander;

class FileValidator implements ValidatorInterface
{
	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		$value = (string) new TildeExpander($value);
		
		return (
			is_file($value) &&
			file_exists($value) &&
			is_readable($value)
		);
	}
}
