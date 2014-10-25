<?php

namespace compwright\ShootproofCli\Validators;

use Sp_Api;

abstract class ShootproofEntityValidator implements ValidatorInterface
{
	protected $api;

	public function __construct(Sp_Api $api)
	{
		$this->api = $api;
	}

	abstract public function __invoke($value, $setting = NULL, array $settings = []);
}
