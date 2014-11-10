#!/usr/bin/env php
<?php

$name = 'shootproof-cli.phar';
$path = __DIR__ . '/bin/' . $name;

$phar = new Phar($path, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $name);
$phar->startBuffering();

// Add all PHP files
$phar->buildFromDirectory(__DIR__, '/\.php$/');

// Create a custom stub to add the shebang
$stub = "#!/usr/bin/env php\n"
      . $phar->createDefaultStub('main.php');
$phar->setStub($stub);

// Close the file
$phar->stopBuffering();

// Make the phar executable
chmod($path, 0755);
