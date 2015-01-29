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

use Sp_Api;

/**
 * Base functionality for validating ShootProof API entities
 */
abstract class ShootProofEntityValidator implements ValidatorInterface
{
    protected $api;

    /**
     * @param Sp_Api $api A ShootProof API object for making ShootProof API requests
     */
    public function __construct(Sp_Api $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function __invoke($value, $setting = null, array $settings = []);
}
