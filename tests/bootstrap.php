<?php

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('UTC');

// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    die("Dependencies must be installed using composer:\n\nphp composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

// Include the Composer autoloader
$loader = include realpath(__DIR__ . '/../vendor/autoload.php');
$loader->addPsr4('ShootProof\\Cli\\', realpath(__DIR__ . '/../src'));
$loader->addPsr4('ShootProof\\Cli\\Test\\', realpath(__DIR__ . '/src'));
