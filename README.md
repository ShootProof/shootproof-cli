# ShootProof Command Line Tool

Command line client for interacting with the [ShootProof API](http://developer.shootproof.com).

## Installation

_Your system must have PHP 5.4 or later installed in order to use the ShootProof command line tool._

Download the `shootproof-cli.phar` file from the [latest release](https://github.com/shootproof/shootproof-cli/releases) and place it in `/usr/local/bin` or wherever it's accessible from your `PATH`.

``` bash
$ chmod +x shootproof-cli.phar
$ mv shootproof-cli.phar /usr/local/bin/shootproof-cli
```

Now `shootproof-cli` should be available for you to use from the command line.

Optionally, you may clone this repository and [build the phar file yourself](#building-the-phar-file).


## Usage

```
shootproof-cli <command> [options]
```

### Supported Commands

* help [command] - gets usage instructions for the script or for a script command
* push - uploads photos to a ShootProof event or album
* pull - downloads photos from a ShootProof event or album
* accesslevel - sets the access level for a ShootProof event

### Configuration

This client requires certain options to be set which may be set explicitly on the command line, or in a configuration file. The default location of the configuration file is `~/.shootproof`.

The configuration file may contain some or all of the following settings:

```
appId=<id>
accessToken=<token>
verbosity=<level>
haltOnError=true
retryLimit=<limit>
email=<email>
```

See `.shootproof-sample` for a minimal example of the configuration file.


## Required Permissions

For this script to operate properly, you must have an access token authorized for the following scopes:

* sp.album.create
* sp.album.get_photos
* sp.event.create
* sp.event.get_photos
* sp.event.set_access_level
* sp.photo.upload
* sp.photo.delete

Non-expiring access tokens are available from ShootProof on request.


## Building the Phar File

The ShootProof command line tool is distributed as an executable [phar](http://php.net/phar) file. The `build.php` script handles building this file.

To build `shootproof-cli.phar`, first make sure that phar creation is enabled in your php.ini file:

```
phar.readonly = 0
```

To build the phar file, change to the location of your `shootproof-cli` project clone and execute the `build.php` script line this:

```
$ php build.php
```

This will create a `build/` directory and place the generated `shootproof-cli.phar` file there.


## License

Copyright © 2014-2015 ShootProof, LLC

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
