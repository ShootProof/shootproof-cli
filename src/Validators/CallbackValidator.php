<?php

namespace compwright\ShootproofCli\Validators;

class CallbackValidator implements ValidatorInterface
{
	protected $callback;
	protected $passAllArgs = TRUE;

	public function __construct(callable $callback, $passAllArgs = TRUE)
	{
		$this->callback = $callback;
		$this->passAllArgs = $passAllArgs;
	}

	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		if ($this->passAllArgs)
		{
			return call_user_func($this->callback, $value, $setting, $settings);
		}
		else
		{
			return call_user_func($this->callback, $value);
		}
	}
}
