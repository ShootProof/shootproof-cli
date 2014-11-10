<?php

namespace compwright\ShootproofCli\Validators;

class RequiredValidator implements ValidatorInterface
{
	protected $strict;

	public function __construct($strict = FALSE)
	{
		$this->strict = $strict;
	}

	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		if ( ! isset($settings[$setting]))
		{
			return FALSE;
		}

		if ( ! $this->strict)
		{
			return (boolean) $value;
		}
		else
		{
			return TRUE;
		}
	}
}
