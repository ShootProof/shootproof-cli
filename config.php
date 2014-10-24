<?php

use Monolog\Logger;
use compwright\ShootproofCli\Validators\RequiredValidator;
use compwright\ShootproofCli\Validators\RangeValidator;
use compwright\ShootproofCli\Validators\FileValidator;
use compwright\ShootproofCli\Validators\EmailValidator;

$summary = 'Command line client for ShootProof (http://shootproof.com)';

$usage = '/path/to/shootproof.phar <command> [options]';

$description = <<<TEXT
Supported commands:

  help [<command>]
  push
  pull
  accesslevel

For instructions on a particular command, run 'help <command>'.

This client requires certain options to be set which may be set explicitly on the command line, or in a configuration file. The default location of the configuration file is ~/.shootproof.

The configuration file may contain some or all of the following settings:

  appId=<id>
  accessToken=<token>
  verbosity=<level>
  haltOnError=true
  retryLimit=<limit>
  email=<email>

TEXT;

$options = [
	'verbosity:' => 'Output verbosity level (0=silent, 1=normal, 2=debug)',
	'config:' => 'Path to the .shootproof configuration file',
	'app-id:' => 'ShootProof API application ID',
	'access-token:' => 'ShootProof API access token',
	'email:' => 'Email address to log the script results to',
	'email-from:' => 'From email address to use when emailing the script results. Ignored if --email is not set.',
	'email-subject:' => 'Subject line to use when emailing the script results. Ignored if --email is not set.',
	'retry-limit:' => 'Number of times to retry an operation if it fails. This option is ignored if --halt-on-error is passed.',
	'halt-on-error' => 'When performing batch operations, stops execution at the first error that occurs.',
];

$validators = [
	'verbosity' => new RangeValidator(0, 2),
	'config' => new FileValidator,
	'app-id' => new RequiredValidator,
	'access-token' => new RequiredValidator,
	'email' => new EmailValidator,
	'from-email' => new EmailValidator,
];

$defaults = [
	'verbosity' => 1,
	'config' => '~/.shootproof',
	'retry-limit' => 3,
	'from-subject' => 'shootproof-cli script execution report',
];

return compact(
	'summary',
	'usage',
	'description',
	'options',
	'validators',
	'defaults',
);
