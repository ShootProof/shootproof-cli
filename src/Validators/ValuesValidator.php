<?php

namespace ShootProof\Cli\Validators;

class ValuesValidator implements ValidatorInterface
{
	protected $values = [];

	public function __construct(array $values = [])
	{
		$this->values = $values;
	}

	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		return in_array($value, $this->values);
	}
}
