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

/**
 * Validates that the value is a valid ShootProof album
 *
 * @todo Complete validation functionality for ShootProofAlbumValidator
 */
class ShootProofAlbumValidator extends ShootProofEntityValidator
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($value, $setting = null, array $settings = [])
    {
        try {
            // call $api with $entity $value
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
