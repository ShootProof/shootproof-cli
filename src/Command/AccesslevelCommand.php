<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use compwright\ShootproofCli\OptionsFactory;
use compwright\ShootproofCli\Validators\ShootproofEventValidator;
use compwright\ShootproofCli\Validators\ValuesValidator;
use compwright\ShootproofCli\Validators\RequiredValidator;
use Aura\Cli\Context;
use Sp_Api as ShootproofApi;

class AccesslevelCommand extends BaseCommand implements HelpableCommandInterface, ConfiguresOptionsInterface
{
	use HelpableCommandTrait;

	public static $usage = 'accesslevel --access-level=<value> [options] [<dir>]';

	public static $description = <<<TEXT
Changes the access level and password for a ShootProof event.
    --access-level must be set to one of the following access levels:

        public_no_password
        public_password
        private_no_password
        private_password

    If no --event option is specified and a .shootproof file exists in 
    the directory, --event will be read from that file.
TEXT;

	public static $options = [
		'access-level:' => 'ShootProof access level',
		'event:' => 'ShootProof event ID',
	];

	public static function configureOptions(Options $options, ShootproofApi $api)
	{
		$options->addValidators([
			'access-level' => [
				new RequiredValidator,
				new ValuesValidator([
					'public_no_password',
					'public_password',
					'private_no_password',
					'private_password',
				])
			],
			'event' => new ShootproofEventValidator($api),
		]);
	}

	public function __invoke(Context $context, OptionsFactory $optionsFactory)
	{
		/*
		    Start log buffer
		    Load command options
		      Load from .shootproof in directory
		      Load from command line
		    Set event access level
		 */
	}
}
