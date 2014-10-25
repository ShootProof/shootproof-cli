<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\OptionsFactory;
use compwright\ShootproofCli\Validators\ShootproofEventValidator;
use compwright\ShootproofCli\Validators\ShootproofAlbumValidator;
use compwright\ShootproofCli\Utility\FileSetCalculator;
use Aura\Cli\Context;
use Sp_Api as ShootproofApi;

class PullCommand extends BaseCommand implements HelpableCommandInterface, ConfiguresOptionsInterface
{
	use HelpableCommandTrait;

	public static $usage = 'pull [options] [<dir>]';

	public static $description = <<<TEXT
This command will compare the ShootProof photos in the specified
    event and compare those to the ones in the directory. New photos 
    will be downloaded from ShootProof; any photos not on ShootProof 
    will be deleted from the directory. If the --replace option is 
    specified, then matching photos in the directory will be overwritten 
    with the ones from ShootProof.

    If the --preview option is passed, then the operation will not 
    actually execute, but a preview of the operation will be output.

    If no directory is specified, the current directory will be used.

    If a .shootproof file exists in the directory, the event and album 
    options will be read from that file unless they are explicitly 
    provided on the command line.
TEXT;

	public static $options = [
		'event:' => 'ShootProof event ID',
		'album:' => 'ShootProof album ID',
		'replace' => 'Replaces local files with ShootProof files if the names match',
		'preview' => 'Preview this operation, but do not apply any changes',
	];

	public static function configureOptions(Options $options, ShootproofApi $api)
	{
		$options->addValidators([
			'event' => new ShootproofEventValidator($api),
			'album' => new ShootproofAlbumValidator($api),
		]);
	}

	public function __invoke(Context $context, OptionsFactory $optionsFactory)
	{
		/*
		    Start log buffer
		    Get directory list
		    Load command options
		      Load from .shootproof in directory
		      Load from command line
		    Get file list
		    Get remote file list
		    Compute files to add
		    Compute files to remove
		    Compute files to replace
		    Each file:
		      Download file
		 */
	}
}
