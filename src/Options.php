<?php
/**
 * This file is part of the ShootProof command line tool.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) ShootProof, LLC (https://www.shootproof.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace ShootProof\Cli;

use ShootProof\Cli\Utility\OptionTransformer;
use ShootProof\Cli\Validators\RequiredValidator;
use ShootProof\Cli\Validators\ValidatorException;
use ShootProof\Cli\Validators\ValidatorInterface;

/**
 * Stores and validates command line interface options
 *
 * @property string $timezone
 * @property int $verbosity
 * @property string $config
 * @property string $appId
 * @property string $accessToken
 * @property string $email
 * @property string $emailFrom
 * @property string $emailSubject
 * @property int $retryLimit
 * @property boolean $haltOnError
 */
class Options
{
    /**
     * Whether to throw exceptions
     * @var boolean
     */
    protected $throwExceptions = true;

    /**
     * List of validators to use when validating options
     * @var array
     */
    protected $validators = [];

    /**
     * Default configuration values
     * @var array
     */
    protected $defaults = [];

    /**
     * The options
     * @var array
     */
    protected $data = [];

    /**
     * @var array $validators Validators to use when validating options
     * @var array $defaults Default configuration values
     */
    public function __construct(array $validators = [], array $defaults = [])
    {
        $this->addValidators($validators);
        $this->setDefaults($defaults);
    }

    /**
     * Validates and sets data properties
     */
    public function __set($key, $value)
    {
        if ($this->__get($key) !== $value && $this->validate($key, $value, true)) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Method not implemented
     *
     * @throws \BadMethodCallException since this method is not implemented
     */
    public function __unset($key)
    {
        throw new \BadMethodCallException('__unset() is not implemented');
    }

    /**
     * Checks whether a data property is set
     *
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Returns a data property or its default value, if no present
     *
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->data[$key])
             ? $this->data[$key]
             : $this->getDefault($key);
    }

    /**
     * Returns the default value for $key, or null if not found
     *
     * @return mixed|null
     */
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

    /**
     * Sets the throwExceptions property to true or false
     *
     * @param boolean $throwExceptions
     */
    public function throwExceptions($throwExceptions = true)
    {
        $this->throwExceptions = (boolean) $throwExceptions;
    }

    /**
     * Loads option data from an array, validating and setting it on this object
     *
     * @param array $data The option data to load
     * @param boolean $overwrite Whether to overwrite existing option data
     * @param boolean $throwExceptions Whether to throw exceptions
     * @throws ValidatorException if a validation error is encountered and $throwExceptions is true
     */
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

    /**
     * Sets the default values for data properties that aren't set
     *
     * @param array $defaults List of default values
     * @return self
     */
    public function setDefaults(array $defaults)
    {
        foreach ($defaults as $option => $default) {
            $this->setDefault($option, $default);
        }

        return $this;
    }

    /**
     * Validates and sets a default value for a specific option
     *
     * @param string $option The name of the option
     * @param mixed $default The default value to set for the option
     * @return self
     */
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

    /**
     * Adds the validators for data properties
     *
     * @param array $validators List of validators to add
     * @return self
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $option => $validator) {
            $this->addValidator($option, $validator);
        }

        return $this;
    }

    /**
     * Adds a validator for a specific option
     *
     * @param string $option The name of the option
     * @param ValidatorInterface|callable $validator The validator to use on the option
     * @return self
     * @throws \InvalidArgumentException if the validator is not callable or instance of ValidatorInterface
     */
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

    /**
     * Validates value for the specified setting (option)
     *
     * @param string $setting The name of the option to validate against
     * @param mixed $value The value to validate for the option
     * @param boolean $skipRequired Whether to skip checking required validators
     * @return boolean true if value is valid, false otherwise
     * @throws ValidatorException if setting is an invalid option and throwExceptions is true
     */
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

    /**
     * Validates all option data against their validators
     *
     * @return boolean true if all option data is valid, false otherwise
     * @throws ValidatorException if encountering an invalid option and throwExceptions is true
     */
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

    /**
     * Validates all required options
     *
     * @return boolean true if all option data is valid, false otherwise
     * @throws ValidatorException if encountering an invalid option and throwExceptions is true
     */
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

    /**
     * Returns an array of all option data
     *
     * @return array
     */
    public function asArray()
    {
        return array_merge($this->defaults, $this->data);
    }
}
