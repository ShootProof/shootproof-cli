<?php
/**
 * This file is part of the ShootProof command line tool.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) ShootProof, LLC (https://www.shootproof.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

use Monolog\Logger;
use ShootProof\Cli\Validators\CallbackValidator;
use ShootProof\Cli\Validators\EmailValidator;
use ShootProof\Cli\Validators\FileValidator;
use ShootProof\Cli\Validators\RangeValidator;
use ShootProof\Cli\Validators\RequiredValidator;
use ShootProof\Cli\Validators\TimezoneValidator;

$summary = 'Command line tool for interacting with the ShootProof API';

$usage = '<command> [options]';

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
    'email-from:' => 'From email address to use when emailing the script results.'
        . "\n        Ignored if --email is not set.",
    'email-subject:' => 'Subject line to use when emailing the script results.'
        . "\n        Ignored if --email is not set.",
    'retry-limit:' => 'Number of times to retry an operation if it fails. This'
        . "\n        option is ignored if --halt-on-error is passed.",
    'halt-on-error' => 'When performing batch operations, stops execution at'
        . "\n        the first error that occurs.",
];

$validators = [
    'timezone' => new TimezoneValidator(),
    'verbosity' => [
        new CallbackValidator('is_numeric', false),
        new RangeValidator(0, 2),
    ],
    'appId' => new RequiredValidator(),
    'accessToken' => new RequiredValidator(),
    'email' => new EmailValidator(),
    'emailFrom' => new EmailValidator(),
];

$defaults = [
    'timezone' => 'America/New_York',
    'verbosity' => 1,
    'config' => '~/.shootproof',
    'retryLimit' => 3,
    'emailSubject' => 'shootproof-cli script execution report (' . date('Y-m-d H:i:s') . ')',
];

/**
 * @string
 */
$monologChannel = 'shootproof-cli';

/**
 * @string
 */
$monologFormat = "%level_name%: %message% %context%" . PHP_EOL;

/**
 * @array
 */
$monologVerbosity = [
    0 => Logger::ERROR,
    1 => Logger::NOTICE,
    2 => Logger::DEBUG
];

return [
    'summary' => $summary,
    'usage' => $usage,
    'description' => $description,
    'options' => $options,
    'validators' => $validators,
    'defaults' => $defaults,
    'monologChannel' => $monologChannel,
    'monologFormat' => $monologFormat,
    'monologVerbosity' => $monologVerbosity,
];
