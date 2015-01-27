<?php

namespace ShootProof\Cli\Command;

use ShootProof\Cli\Options;
use ShootProof\Cli\OptionsFactory;
use ShootProof\Cli\Validators\ShootproofEventValidator;
use ShootProof\Cli\Validators\ValuesValidator;
use ShootProof\Cli\Validators\RequiredValidator;
use ShootProof\Cli\Validators\CallbackValidator;
use ShootProof\Cli\Utility\TildeExpander;
use Aura\Cli\Context;
use Sp_Api as ShootproofApi;
use josegonzalez\Dotenv\Loader as DotenvLoader;

class AccesslevelCommand extends BaseCommand implements HelpableCommandInterface
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
		'event:' => 'ShootProof event ID',
		'access-level:' => 'ShootProof access level',
		'password:' => 'ShootProof password (required for certain access levels)',
	];

	protected function getValidators()
	{
		return [
			'accessLevel' => [
				new ValuesValidator([
					'public_no_password',
					'public_password',
					'private_no_password',
					'private_password',
				]),
				new RequiredValidator,
			],
			'event' => [
				new RequiredValidator,
				new ShootproofEventValidator($this->api),
			],
			'password' => new CallbackValidator(function($value, $setting, array $settings)
			{
				// Require a password for certain access levels
				switch ($settings['accessLevel'])
				{
					case 'public_password':
					case 'private_password':
						// Was the --password option passed with no value?
						if ($value === TRUE)
						{
							return FALSE;
						}

						$validator = new RequiredValidator;
						$settings[$setting] = $value;
						return $validator($value, $setting, $settings);
				}

				return TRUE;
			}),
		];
	}

	protected function processDirectory($dir, Options $baseOptions, OptionsFactory $optionsFactory)
	{
		// Reload the options and read the directory config file
		$options = $optionsFactory->newInstance([], $this->getValidators());
		$configPath = new TildeExpander($dir) . '/.shootproof';
		$configLoader = new DotenvLoader($configPath);
		try
		{
			$configData = $configLoader->parse()->toArray();
			$options->loadOptionData($configData, FALSE); // don't overwrite CLI data
			$this->logger->addDebug('ShootProof settings file found', [$configPath, $configData]);
		}
		catch (\InvalidArgumentException $e)
		{
			// ignore
			$this->logger->addDebug('ShootProof settings file not found', [$configPath]);
		}

		// Make sure all required options are present
		//var_dump($options->asArray());
		$options->validateAllRequired();
		$options->validate('password', $options->password);

		// Set the event access level
		$this->logger->addNotice('Setting access level', [
			'event' => $options->event,
			'level' => $options->accessLevel,
			'password' => $options->password ? str_repeat('*', strlen($options->password)) : NULL,
		]);
		if ( ! $baseOptions->preview)
		{
			$result = $this->api->setEventAccessLevel($options->event, $options->accessLevel, $options->password);
			$this->logger->addDebug('Operation completed', $result);
		}
	}
}
