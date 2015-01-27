<?php

namespace ShootProof\Cli\Utility;

class FileDownloader
{
	public $result;
	protected $url;

	public function __construct($url)
	{
		$this->url = $url;
	}

	public function download($path, $overwrite = TRUE)
	{
		if (file_exists($path))
		{
			if ($overwrite)
			{
				@unlink($path);
			}
			else
			{
				throw new \InvalidArgumentException($path . ' already exists');
			}
		}

		// Download straight to a file via CURL
		$fp = fopen($path, 'w');
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);

		// Collect info
		$this->result = curl_getinfo($ch);
		$error = curl_error($ch);
		$errno = curl_errno($ch);

		curl_close($ch);
		fclose($fp);

		if ($error)
		{
			throw new \RuntimeException($error, $errno);
		}
		elseif ($this->result['http_code'] >= 400)
		{
			throw new \RuntimeException('Download failed with HTTP ' . $this->result['http_code']);
		}
	}
}
