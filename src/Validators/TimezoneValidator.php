<?php

namespace compwright\ShootproofCli\Validators;

class TimezoneValidator implements ValidatorInterface
{
	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		try
		{
			$tz = new \DateTimeZone($value);
			return TRUE;
		}
		catch (\Exception $e)
		{
			return FALSE;
		}
	}
}
