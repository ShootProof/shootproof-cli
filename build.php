#!/usr/bin/env php
<?php

// start buffering. Mandatory to modify stub.
$phar->startBuffering();

// Get the default stub. You can create your own if you have specific needs
$defaultStub = $phar->createDefaultStub('shootproof-cli.php');

// Adding files
$phar->buildFromDirectory(__DIR__, '/\.php$/');

// Create a custom stub to add the shebang
$stub = "#!/usr/bin/env php\n" . $defaultStub;

// Add the stub
$phar->setStub($stub);

$phar->stopBuffering();
