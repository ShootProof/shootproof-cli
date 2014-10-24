<?php

namespace compwright\ShootproofCli\Validators;

class ShootproofEventValidator extends ShootproofEntityValidator
{
	public function __invoke($value, $setting, array $settings)
	{
		try
		{
			// call $api with $entity $value
			return TRUE;
		}
		catch (\Exception $e)
		{
			return FALSE;
		}
	}
}
