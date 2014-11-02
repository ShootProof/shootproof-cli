<?php

use compwright\ShootproofCli\Validators\RequiredValidator;
use compwright\ShootproofCli\Validators\RangeValidator;
use compwright\ShootproofCli\Validators\FileValidator;
use compwright\ShootproofCli\Validators\EmailValidator;
use compwright\ShootproofCli\Validators\CallbackValidator;
use compwright\ShootproofCli\Validators\TimezoneValidator;
use Monolog\Logger;

$summary = 'Command line client for ShootProof';

$usage = '/path/to/shootproof.phar <command> [options]';

$description = <<<TEXT
Supported commands:

        help
        push
        pull
        accesslevel

    For instructions on a particular command, run 'help <command>'.

    This client requires certain options to be set which may be set 
    explicitly on the command line, or in a configuration file. The 
    default location of the configuration file is ~/.shootproof.

    The configuration file may contain some or all of the following 
    settings:

        appId=<id>
        accessToken=<token>
        verbosity=<level>
        haltOnError=true
        retryLimit=<limit>
        email=<email>
TEXT;

$options = [
	'timezone:' => 'System timezone (http://us1.php.net/manual/en/timezones.php)',
	'verbosity:' => 'Output verbosity level (0=silent, 1=normal, 2=debug)',
	'config:' => 'Path to the .shootproof configuration file',
	'app-id:' => 'ShootProof API application ID',
	'access-token:' => 'ShootProof API access token',
	'email:' => 'Email address to log the script results to',
	'email-from:' => "From email address to use when emailing the script results.\n        Ignored if --email is not set.",
	'email-subject:' => "Subject line to use when emailing the script results.\n        Ignored if --email is not set.",
	'retry-limit:' => "Number of times to retry an operation if it fails. This\n        option is ignored if --halt-on-error is passed.",
	'halt-on-error' => "When performing batch operations, stops execution at\n        the first error that occurs.",
];

$validators = [
	'timezone' => new TimezoneValidator,
	'verbosity' => [
		new CallbackValidator('is_numeric', FALSE),
		new RangeValidator(0, 2),
	],
	//'config' => new FileValidator,
	'appId' => new RequiredValidator,
	'accessToken' => new RequiredValidator,
	'email' => new EmailValidator,
	'fromEmail' => new EmailValidator,
];

$defaults = [
	'timezone' => 'America/New_York',
	'verbosity' => 1,
	'config' => '~/.shootproof',
	'retryLimit' => 3,
	'fromSubject' => 'shootproof-cli script execution report',
];

return compact(
	'summary',
	'usage',
	'description',
	'options',
	'validators',
	'defaults'
);
