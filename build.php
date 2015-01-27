#!/usr/bin/env php
<?php

require __DIR__ . '/src/bootstrap.php';

use ShootProof\Cli\Compiler;

error_reporting(-1);
ini_set('display_errors', 1);

$name = 'shootproof-cli.phar';
$path = __DIR__ . '/build/' . $name;

if (!file_exists(dirname($path))) {
    mkdir(dirname($path));
}

try {

    $compiler = new Compiler();
    $compiler->compile($path);
    chmod($path, 0755);

} catch (\Exception $e) {

    echo 'Failed to compile phar: [' . get_class($e) . '] '
        . $e->getMessage() . ' at ' . $e->getFile()
        . ':' . $e->getLine();
    exit(1);

}
