<?php

namespace compwright\ShootproofCli;

use compwright\ShootproofCli\Validators\ValidatorException;
use compwright\ShootproofCli\Validators\ValidatorInterface;
use compwright\ShootproofCli\Validators\RequiredValidator;

class Options
{
	protected $throwExceptions = TRUE;
	protected $validators = [];
	protected $defaults = [];
	protected $data = [];

	public function __construct(array $validators = [], array $defaults = [])
	{
		$this->addValidators($validators);
		$this->setDefaults($defaults);
	}

	public function __set($key, $value)
	{
		try
		{
			if ($this->__get($key) !== $value && $this->validate($key, $value, TRUE))
			{
				$this->data[$key] = $value;
			}
		}
		catch (ValidatorException $e)
		{
			throw $e;				
		}
	}

	public function __unset($key)
	{
		throw new \BadMethodCallException('__unset() is not implemented');
	}

	public function __isset($key)
	{
		return isset($this->data[$key]);
	}

	public function __get($key)
	{
		return isset($this->data[$key])
		     ? $this->data[$key]
		     : $this->getDefault($key);
	}

	public function getDefault($key)
	{
		if (isset($this->defaults[$key]))
		{
			$default = $this->defaults[$key];

			return is_callable($default)
			     ? $default($this)
			     : $default;
		}

		return NULL;
	}

	public function throwExceptions($throwExceptions = TRUE)
	{
		$this->throwExceptions = (boolean) $throwExceptions;
	}

	public function loadOptionData(array $data, $overwrite = TRUE, $throwExceptions = TRUE)
	{
		foreach ($data as $key => $value)
		{
			try
			{
				if ($overwrite || ! $this->__isset($key))
				{
					$this->__set($key, $value);
				}
			}
			catch (ValidatorException $e)
			{
				if ($throwExceptions)
				{
					throw $e;
				}
			}
		}
	}

	public function setDefaults(array $defaults)
	{
		foreach ($defaults as $option => $default)
		{
			$this->setDefault($option, $default);
		}

		return $this;
	}

	public function setDefault($option, $default)
	{
		$value = is_callable($default)
		       ? $default($this)
		       : $default;

		if ($this->validate($option, $value, TRUE))
		{
			$this->defaults[$option] = $default;
		}

		return $this;
	}

	public function addValidators(array $validators)
	{
		foreach ($validators as $option => $validator)
		{
			$this->addValidator($option, $validator);
		}

		return $this;
	}

	public function addValidator($option, $validator)
	{
		if (is_callable($validator) || $validator instanceOf ValidatorInterface)
		{
			$this->validators[$option][] = $validator;
		}
		elseif (is_array($validator))
		{
			foreach ($validator as $v)
			{
				$this->addValidator($option, $v);
			}
		}
		else
		{
			throw new \InvalidArgumentException('Validators must be callable or implement ValidatorInterface');
		}

		return $this;
	}

	public function validate($option, $value, $skipRequired = FALSE)
	{
		if (empty($this->validators[$option]))
		{
			return TRUE;
		}

		foreach ($this->validators[$option] as $validator)
		{
			if ($skipRequired && $validator instanceof RequiredValidator)
			{
				continue;
			}

			if ( ! $validator($value, $option, $this->data))
			{
				if ($this->throwExceptions)
				{
					throw new ValidatorException("Invalid {$option}, see help for usage instructions");
				}
				else
				{
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	public function validateAllRequired()
	{
		foreach ($this->validators as $option => $validators)
		{
			foreach ($validators as $validator)
			{
				if ($validator instanceof RequiredValidator && ! $validator($this->__get($option), $option, $this->asArray()))
				{
					if ($this->throwExceptions)
					{
						throw new ValidatorException("{$option} is required, see help for usage instructions");
					}
					else
					{
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}

	public function asArray()
	{
		return array_merge($this->defaults, $this->data);
	}
}
