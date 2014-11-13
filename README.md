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

MIT license

## Disclaimer

While every effort has been made to deliver a high quality product, this product is not guaranteed to be free from defects. The software is provided "as is," and you use the software at your own risk. No warranty is made as to performance, merchantability, fitness for a particular purpose, or any other warranties whether expressed or implied. No oral or written communication from or information provided with this software shall create a warranty. Under no circumstances shall the publisher or author be liable for direct, indirect, special, incidental, or consequential damages resulting from the use, misuse, or inability to use this software, even if the publisher or auther has been advised of the possibility of such damages. These exclusions and limitations may not apply in all jurisdictions. You may have additional rights and some of these limitations may not apply to you.
