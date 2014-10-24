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
		// --long-option -> long_option
		$key = ltrim($key, '-');
		$key = strtr($key, '-', '_');

		// long_option -> longOption
		return preg_replace('/_(.?)/e', "strtoupper('$1')", $key);		
	}
}
