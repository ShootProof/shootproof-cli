<?php

namespace ShootProof\Cli\Validators;

class ShootproofEventValidator extends ShootproofEntityValidator
{
	public function __invoke($value, $setting = NULL, array $settings = [])
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
