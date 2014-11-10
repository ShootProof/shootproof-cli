# shootproof-cli

Command line client for [ShootProof](http://shootproof.com)

## Installation

[Download shootproof-cli.phar](https://bitbucket.org/compwright/shootproof-cli/src/928535f7bcf8b8270f3a2d9c3f6920edec46150a/bin/shootproof-cli.phar?at=master)

Move this file to a convenient location:

```
$ wget https://bitbucket.org/compwright/shootproof-cli/src/928535f7bcf8b8270f3a2d9c3f6920edec46150a/bin/shootproof-cli.phar?at=master
$ mv ./shootproof-cli.phar /usr/local/bin/shootproof-cli
```

## Usage

```/path/to/shootproof.phar <command> [options]```

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
