<?php

namespace ShootProof\Cli\Validators;

class ShootproofAlbumValidator extends ShootproofEntityValidator
{
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
