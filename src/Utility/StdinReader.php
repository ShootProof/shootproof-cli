<?php

namespace ShootProof\Cli\Utility;

class StdinReader
{
    protected $wait = 3;

    public function __construct($wait = 3)
    {
        $this->wait = 3;
    }

    // Based on http://www.gregfreeman.org/2013/processing-data-with-php-using-stdin-and-piping/
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
