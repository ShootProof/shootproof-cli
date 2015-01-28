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

namespace ShootProof\Cli\Validators;

class CallbackValidator implements ValidatorInterface
{
    protected $callback;
    protected $passAllArgs = true;

    public function __construct(callable $callback, $passAllArgs = true)
    {
        $this->callback = $callback;
        $this->passAllArgs = $passAllArgs;
    }

    public function __invoke($value, $setting = null, array $settings = [])
    {
        if ($this->passAllArgs) {
            return call_user_func($this->callback, $value, $setting, $settings);
        } else {
            return call_user_func($this->callback, $value);
        }
    }
}
