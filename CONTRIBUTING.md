# Contributing

## Run CodeSniffer

``` bash
$ ./vendor/bin/phpcs src bin bin/shootproof-cli --standard=psr2 -sp
```

Fix any errors reported before submitting a pull request.

## Prepare for Release

When preparing a release, clean things up with Composer before building the phar file:

``` bash
$ composer install --no-dev --prefer-source --optimize-autoloader
```

Now, you may build the phar file, and it will be cleaner and more compact.

## Build the Phar File

The ShootProof command line tool is distributed as an executable [phar](http://php.net/phar) file. The `build.php` script handles building this file.

To build `shootproof-cli.phar`, first make sure that phar creation is enabled in your php.ini file:

```
phar.readonly = 0
```

Make sure you have enough file handles available:

``` bash
$ ulimit -Sn 4096
```

To build the phar file, change to the location of your `shootproof-cli` project clone and execute the `build.php` script:

``` bash
$ php build.php
```

This will create a `build/` directory and place the generated `shootproof-cli.phar` file there.
