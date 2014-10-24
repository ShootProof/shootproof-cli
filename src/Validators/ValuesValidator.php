<?php

namespace compwright\ShootproofCli\Validators;

class ValuesValidator implements ValidatorInterface
{
	protected $values;

	public function __construct($values)
	{
		$this->start = $values;
	}

	public function __invoke($value, $setting, array $settings)
	{
		return in_array($value, $this->values);
	}
}
