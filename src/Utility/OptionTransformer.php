<?php

namespace compwright\ShootproofCli\Utility;

class OptionTransformer extends \ArrayObject
{
	public function __construct(array $input)
	{
		$transformed = [];

		foreach ($input as $key => $value)
		{
			$tkey = $this->transformKey($key);
			$transformed[$tkey] = $value;
		}

		parent::__construct($transformed);
	}

	protected function transformKey($key)
	{
		// --long-option -> longOption
		$key = ltrim($key, '-');
		return preg_replace_callback('/-(.?)/', array($this, 'capitalize'), $key);		
	}

	protected function capitalize($matches) {
		return strtoupper($matches[1]);
	}
}
