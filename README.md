# shootproof-cli

Command line client for [ShootProof](http://shootproof.com)

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
