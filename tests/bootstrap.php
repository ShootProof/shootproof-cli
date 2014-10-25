<?php

error_reporting(-1);
date_default_timezone_set('UTC');

$vendorPos = strpos(__DIR__, 'vendor/compwright/shootproof-cli');
if ($vendorPos !== FALSE)
{
    // Package has been cloned within another composer package, resolve path to autoloader
    $vendorDir = substr(__DIR__, 0, $vendorPos) . 'vendor/';
    require $vendorDir . 'autoload.php';
}
else
{
    // Package itself (cloned standalone)
    require __DIR__ . '/../vendor/autoload.php';
}
