# shootproof-cli

Command line client for [ShootProof](http://shootproof.com)

## Usage

```/path/to/shootproof.phar <command> [options]```

This client requires certain options to be set which may be set explicitly on the command line, or in a configuration file. The default location of the configuration file is `~/.shootproof`.

The configuration file should contain some or all of the following settings:

```
accessToken=<token>
verbosity=<level>
haltOnError=true
```

## General Options

Replace `<placeholders>` with their actual values. [Brackets] signify options which are optional.

### --verbosity=`<level>`

Output verbosity level. Default is 1 (normal).

Level | Description
------|------------
    0 | Silent
    1 | Normal
    2 | Debug

This option overrides the value set in the configuration file.

### --config=`<pathToConfigFile>`

Path to the script configuration file.

### --access-token=`<token>`

ShootProof API access token.

This option overrides the value set in the configuration file.

### --haltOnError

When performing batch operations, stops execution at the first error that occurs.

## Commands

### help [`<command>`]

Displays the help screen for a given command, or a list of commands if no command is specified.

### push [options] [`<dir>`]

```push [--target=event|album] [--event=<eventId>] [--event-name="<name>"] [--album=<albumId>] [--parent-album=<albumId>] [--album-name="<name>"] [--album-password="<password>"] [--replace] [--preview] [--link] [<dir>]```

If no `event` option is passed, a new ShootProof event will be created automatically using the name of the directory, or with the name specified in `event-name`.

This command will compare the ShootProof photos in the specified event and compare those to the ones in the directory. New photos will be added to ShootProof; any photos not in the directory will be deleted from ShootProof. If the `replace` option is specified, then matching photos in ShootProof will be overwritten with the ones from the directory.

If the `preview` option is passed, then the operation will not actually execute, but a preview of the operation will be output.

If no directory is specified, the current directory will be used.

Options for this command may also be set in a `.shootproof` file in the directory, or piped in.

```
target=<target>
event=<eventId>
eventName=<name>
album=<albumId>
parentAlbum=<parentAlbumId>
albumName=<name>
albumPassword=<password>
```

After this command completes successfully, a `.shootproof` file will be written to the directory for use in subsequent runs.

### pull [options] [`<dir>`]

```pull [--event=<eventId>] [--album=<albumId>] [--replace] [--preview] [<dir>]```

This command will compare the ShootProof photos in the specified event and compare those to the ones in the directory. New photos will be downloaded from ShootProof; any photos not on ShootProof will be deleted from the directory. If the `replace` option is specified, then matching photos in the directory will be overwritten with the ones from ShootProof.

If the `preview` option is passed, then the operation will not actually execute, but a preview of the operation will be output.

If no directory is specified, the current directory will be used.

If a `.shootproof` file exists in the directory, the `event` and `album` options will be read from that file unless they are explicitly provided on the command line.

### accesslevel [options] [`<dir>`]

```accesslevel --access-level=<level> [--event=<eventId>] [<dir>]```

Changes the access level and password for a ShootProof event. `access-level` must be set to one of the following access levels:

* `public_no_password`
* `public_password`
* `private_no_password`
* `private_password`

If no `event` option is specified and a `.shootproof` file exists in the directory, `event` will be read from that file.
