<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\OptionsFactory;
use compwright\ShootproofCli\Validators\ShootproofEventValidator;
use compwright\ShootproofCli\Validators\ShootproofAlbumValidator;
use compwright\ShootproofCli\Validators\ValuesValidator;
use compwright\ShootproofCli\Utility\FileSetCalculator;
use compwright\ShootproofCli\Utility\TildeExpander;
use Aura\Cli\Context;
use Sp_Api as ShootproofApi;

class PushCommand extends BaseCommand
{
	public static $usage = 'push [options] [<dir>]';

	public static $description = <<<TEXT
Uploads photos in a directory or set of directories to a ShootProof event or album. Choose between the two using the --target-event parameter.

If no event or album ID is passed, a new ShootProof event or album will be created automatically using the name of the directory. If --event-name or --album-name is passed, it will be created with the specified name. Additional album settings may be passed with --parent-album and --album-password.

Push will compare the photos on ShootProof with the ones in a directory. New photos will be added to ShootProof; any photos not in the directory will be deleted from ShootProof. If the --replace option is specified, then matching photos in ShootProof will be overwritten with the ones from the directory.

If the --preview option is passed, then the operation will not actually execute, but a preview of the operation will be output.

If no directory is specified, the current directory will be used. Glob expressions are supported for processing multiple directories (each matching directory will be pushed to a separate ShootProof event or album). Alternately, a list of directories may be piped into this command.

Options for this command may also be set in a .shootproof file in the directory:

	target=<target>
	event=<eventId>
	eventName=<name>
	album=<albumId>
	parentAlbum=<parentAlbumId>
	albumName=<name>
	albumPassword=<password>

After this command completes successfully, a .shootproof file will be written to the directory for use in subsequent runs.
TEXT;

	public static $options = [
		'target:' => 'ShootProof upload target (specify event or album)',
		'event:' => 'ShootProof event ID',
		'event-name:' => 'ShootProof event name',
		'album:' => 'ShootProof album ID',
		'parent-album:' => 'ShootProof parent album ID',
		'album-name:' => 'ShootProof album name',
		'album-password:' => 'ShootProof album password',
		'replace' => 'Replaces files on ShootProof with local files if the names match',
		'preview' => 'Preview this operation, but do not apply any changes',
	];

	public static function configureOptions(Options $options, ShootproofApi $api)
	{
		$options->addValidators([
			'target' => new ValuesValidator(['event', 'album']),
			'event' => new ShootproofEventValidator($api),
			'album' => new ShootproofAlbumValidator($api),
			'parent-album' => new ShootproofAlbumValidator($api),
		]);

		$options->setDefault('event', function(Options $options)
		{
			return $options->album
			     ? 'album'
			     : 'event';
		});
	}

	public function __invoke(Context $context, OptionsFactory $optionsFactory)
	{
		/*
		    Start log buffer
		    Get directory list
		    Each directory:
		      Load command options
		        Load from .shootproof in directory
		        Load from command line
		      Create event or album
		      Get file list
		      Get remote file list
		      Compute files to add
		      Compute files to remove
		      Compute files to replace
		      Each file to add:
		        Try:
		          Upload file
		        Upload failed:
		          Retry if tries < retryLimit
		      Each file to remove:
		        Remove file
		      Each file to replace:
		        Remove file
		        Try:
		          Upload file
		        Upload failed:
		          Retry if tries < retryLimit
		 */
	}
}
