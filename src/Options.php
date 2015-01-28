<?php

namespace ShootProof\Cli;

use ShootProof\Cli\Validators\ValidatorException;
use ShootProof\Cli\Validators\ValidatorInterface;
use ShootProof\Cli\Validators\RequiredValidator;
use ShootProof\Cli\Utility\OptionTransformer;

class Options
{
    protected $throwExceptions = true;
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
        if ($this->__get($key) !== $value && $this->validate($key, $value, true)) {
            $this->data[$key] = $value;
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
        if (isset($this->defaults[$key])) {
            $default = $this->defaults[$key];

            return is_callable($default)
                 ? $default($this)
                 : $default;
        }

        return null;
    }

    public function throwExceptions($throwExceptions = true)
    {
        $this->throwExceptions = (boolean) $throwExceptions;
    }

    public function loadOptionData(array $data, $overwrite = true, $throwExceptions = true)
    {
        foreach ($data as $key => $value) {
            try {
                if ($overwrite || ! $this->__isset($key)) {
                    $this->__set($key, $value);
                }
            } catch (ValidatorException $e) {
                if ($throwExceptions) {
                    throw $e;
                }
            }
        }
    }

    public function setDefaults(array $defaults)
    {
        foreach ($defaults as $option => $default) {
            $this->setDefault($option, $default);
        }

        return $this;
    }

    public function setDefault($option, $default)
    {
        $value = is_callable($default)
               ? $default($this)
               : $default;

        if ($this->validate($option, $value, true)) {
            $this->defaults[$option] = $default;
        }

        return $this;
    }

    public function addValidators(array $validators)
    {
        foreach ($validators as $option => $validator) {
            $this->addValidator($option, $validator);
        }

        return $this;
    }

    public function addValidator($option, $validator)
    {
        if (is_callable($validator) || $validator instanceof ValidatorInterface) {
            $this->validators[$option][] = $validator;
        } elseif (is_array($validator)) {
            foreach ($validator as $v) {
                $this->addValidator($option, $v);
            }
        } else {
            throw new \InvalidArgumentException('Validators must be callable or implement ValidatorInterface');
        }

        return $this;
    }

    public function validate($setting, $value, $skipRequired = false)
    {
        if (empty($this->validators[$setting])) {
            return true;
        }

        foreach ($this->validators[$setting] as $validator) {
            if ($skipRequired && $validator instanceof RequiredValidator) {
                continue;
            }

            if (! $validator($value, $setting, $this->asArray())) {
                if ($this->throwExceptions) {
                    $transformer = new OptionTransformer();
                    $option = $transformer->untransformKey($setting);
                    throw new ValidatorException("Invalid --{$option}, see help for usage instructions");
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    public function validateAll()
    {
        foreach ($this->validators as $setting => $validators) {
            foreach ($validators as $validator) {
                $value = $this->__get($setting);
                $settings = $this->asArray();

                if (! $validator($value, $setting, $settings)) {
                    if ($this->throwExceptions) {
                        $transformer = new OptionTransformer();
                        $option = $transformer->untransformKey($setting);
                        throw new ValidatorException("Invalid --{$option}, see help for usage instructions");
                    } else {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function validateAllRequired()
    {
        foreach ($this->validators as $setting => $validators) {
            foreach ($validators as $validator) {
                $value = $this->__get($setting);
                $settings = $this->asArray();

                if ($validator instanceof RequiredValidator && ! $validator($value, $setting, $settings)) {
                    if ($this->throwExceptions) {
                        $transformer = new OptionTransformer();
                        $option = $transformer->untransformKey($setting);
                        throw new ValidatorException("--{$option} is required, see help for usage instructions");
                    } else {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function asArray()
    {
        return array_merge($this->defaults, $this->data);
    }
}
