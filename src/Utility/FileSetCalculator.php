<?php

namespace compwright\ShootproofCli\Utility;

class FileSetCalculator
{
	protected $a, $b, $replace;

	public function __construct(array $a, array $b, $replace = TRUE)
	{
		$this->a = $a;
		$this->b = $b;
		$this->replace = $replace;
	}

	public function add()
	{
		return array_diff($this->a, $this->b);
	}

	public function remove()
	{
		return array_diff($this->b, $this->a);
	}

	public function replace()
	{
		return $this->replace ? array_intersect($this->a, $this->b) : [];
	}
}
