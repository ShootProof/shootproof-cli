<?php

namespace compwright\ShootproofCli\Utility;

class TildeExpander
{
	protected $path;
	protected $homedir;

	public function __construct($path)
	{
		if (is_array($path))
		{
			throw new \InvalidArgumentException('$path must be a string');
		}

		$this->path = $path;
		$info = posix_getpwuid(posix_getuid());
		$this->homedir = $info['dir'];
	}

	public function __toString()
	{
        return str_replace('~', $this->homedir, $this->path);
	}
}
