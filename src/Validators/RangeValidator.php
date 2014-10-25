<?php

namespace compwright\ShootproofCli\Validators;

class RangeValidator implements ValidatorInterface
{
	protected $start;
	protected $end;

	public function __construct($start, $end)
	{
		$this->start = $start;
		$this->end = $end;
	}

	public function __invoke($value, $setting = NULL, array $settings = [])
	{
		return (
			$value >= $this->start &&
			$value <= $this->end
		);
	}
}
