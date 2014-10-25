<?php

namespace compwright\ShootproofCli\Validators;

interface ValidatorInterface
{
	public function __invoke($value, $setting = NULL, array $settings = []);
}
