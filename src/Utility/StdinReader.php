<?php

namespace compwright\ShootproofCli\Utility;

class StdinReader
{
	protected $wait = 3;

	public function __construct($wait = 3)
	{
		$this->wait = 3;
	}

	// Based on http://www.gregfreeman.org/2013/processing-data-with-php-using-stdin-and-piping/
	public function read(callable $lineCallback, callable $doneCallback = NULL)
	{
		stream_set_blocking(STDIN, 0);
		$timeoutStarted = FALSE;
		$timeout = NULL;
		while (1)
		{
			// I'm getting something...
		    while (FALSE !== ($line = fgets(STDIN)))
		    {
		        $lineCallback($line);

		        if ($timeoutStarted)
		        {
		            $timeoutStarted = FALSE;
		            $timeout = NULL;
		        }
		    }

		    // End of input
		    if (feof(STDIN))
		    {
		    	if ($doneCallback)
		    	{
			    	$doneCallback();
		    	}
	
		        break;
		    }

		    // Wait a spell
		    if (NULL === $timeout)
		    {
		        $timeout = time();
		        $timeoutStarted = TRUE;
		        continue;
		    }

		    // Timeout
		    if (time() > $timeout + $this->wait)
		    {
				throw new \RuntimeException('Timeout reached while reading STDIN');
		        return;
		    }
		};
	}
}
