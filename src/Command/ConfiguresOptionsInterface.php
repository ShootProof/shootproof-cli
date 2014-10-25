<?php

namespace compwright\ShootproofCli\Command;

use compwright\ShootproofCli\Options;
use Sp_Api as ShootproofApi;

interface ConfiguresOptionsInterface
{
	public static function configureOptions(Options $options, ShootproofApi $api);
}
