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

namespace ShootProof\Cli\Utility;

/**
 * Utility to read standard input from the command line
 */
class StdinReader
{
    /**
     * Seconds to wait before timeout
     * @var int
     */
    protected $wait = 3;

    /**
     * Constructs a standard input reader
     *
     * @param int $wait Seconds to wait before timeout (defaults to 3)
     */
    public function __construct($wait = 3)
    {
        $this->wait = 3;
    }

    /**
     * Read everything from standard input
     *
     * @param callable $lineCallback An operation to perform on each line read
     * @param callable $doneCallback An operation to perform when the end has been reached
     * @throws \RuntimeException if a timeout occurs
     * @link http://www.gregfreeman.org/2013/processing-data-with-php-using-stdin-and-piping/
     *       Based on "Processing data with PHP using STDIN and Piping" by Greg Freeman
     */
    public function read(callable $lineCallback, callable $doneCallback = null)
    {
        stream_set_blocking(STDIN, 0);
        $timeoutStarted = false;
        $timeout = null;

        while (1) {
        // I'm getting something...
            while (false !== ($line = fgets(STDIN))) {
                $lineCallback($line);

                if ($timeoutStarted) {
                    $timeoutStarted = false;
                    $timeout = null;
                }
            }

            // End of input
            if (feof(STDIN)) {
                if ($doneCallback) {
                    $doneCallback();
                }

                break;
            }

            // Wait a spell
            if (null === $timeout) {
                $timeout = time();
                $timeoutStarted = true;
                continue;
            }

            // Timeout
            if (time() > $timeout + $this->wait) {
                throw new \RuntimeException('Timeout reached while reading STDIN');
                return;
            }
        };
    }
}
