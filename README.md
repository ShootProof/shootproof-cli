# shootproof-cli

Command line client for [ShootProof](http://shootproof.com)

## Installation

[Download shootproof-cli.phar](https://bitbucket.org/compwright/shootproof-cli/src/928535f7bcf8b8270f3a2d9c3f6920edec46150a/bin/shootproof-cli.phar?at=master)

Move this file to a convenient location:

```
$ wget https://github.com/ShootProof/shootproof-cli/blob/master/bin/shootproof-cli.phar?raw=true
$ mv ./shootproof-cli.phar /usr/local/bin/shootproof-cli
```

## Usage

```shootproof-cli <command> [options]```

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
