<?php

namespace compwright\ShootproofCli\Utility;

class DirectoryListFactory
{
	public $source = self::SOURCE_DEFAULT;
	protected $dirList;

	const SOURCE_COMMAND_LINE = 'command_line';
	const SOURCE_STDIN = 'stdin';
	const SOURCE_DEFAULT = 'cwd';

	public function getList()
	{
		if ($this->dirList)
		{
			return $this->dirList;
		}
		else
		{
			return [ getcwd() ];
		}
	}

	public function loadFromCommandline(array $optionData, $offset)
	{
		if ( ! empty($this->dirList))
		{
			return;
		}

		// If a wildcard expression is given, PHP will automatically expand it.
		// We need to get all the elements at or after key value 2.
		$i = array_search($offset, array_keys($optionData));
		if ($i > -1)
		{
			$fileList = array_slice($optionData, $i);
			$this->dirList = [];
			foreach ($fileList as $path)
			{
				foreach ($this->excludeFiles($this->normalizePathExpression($path)) as $normalizedDir)
				{
					array_push($this->dirList, $normalizedDir);
				}
			}
			$this->source = self::SOURCE_COMMAND_LINE;
		}
	}

	public function loadFromStdin(StdinReader $reader)
	{
		if ( ! empty($this->dirList))
		{
			return;
		}
		
		$dirList = [];

		try
		{
			$reader->read(function($line) use (&$dirList)
			{
				foreach ($this->normalizePathExpression(trim($line)) as $dir)
				{
					array_push($dirList, $dir);
				}
			});
		}
		catch (\RuntimeException $e)
		{
			if ( ! empty($dirList))
			{
				// There was data, but the stream timed out
				throw $e;
			}
		}

		if ($dirList)
		{
			$this->dirList = $this->excludeFiles($dirList);
			$this->source = self::SOURCE_STDIN;
		}
	}

	protected function normalizePathExpression($expression)
	{
		// expand tilde, expand wildcards, expand to absolute path
		return array_map(
			function($path)
			{
				if ($normalizedPath = realpath($path))
				{
					return $normalizedPath;
				}
				else
				{
					return $path;
				}
			},
			glob(new TildeExpander($expression), GLOB_NOCHECK)
		);
	}
	
	protected function excludeFiles(array $list)
	{
		return array_filter($list, function($path)
		{
			return ! is_file($path);
		});
	}
}
