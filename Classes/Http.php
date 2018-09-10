<?php

Class Http
{

	public static $webroot = '';
	public static $dirroot = '';

	/**
	 * das boot function
	 * @return void
	 */
	public static function boot()
	{
		// zoeken naar localhost
		// zoeken naar /public/
		// webroot van maken

		if($_SERVER['HTTP_HOST'] == 'localhost' && strpos($_SERVER['REQUEST_URI'], '/public/')) {

			$urlParts = explode('/public/', $_SERVER['REQUEST_URI']);

			self::$webroot = self::httpOrHttps().$_SERVER['HTTP_HOST'].$urlParts[0].'/public/';
		}
		else {
			self::$webroot = self::httpOrHttps().$_SERVER['HTTP_HOST'];
		}

		// get the dir root
		self::$dirroot = str_replace('/Classes', '/',  __DIR__);
	}

	/*
	Returns webroot
	 */
	public static function webroot()
	{
		return self::$webroot;
	}


	/*
	Checks for http(s) and returns
	 */
	private static function httpOrHttps()
	{
		if(isset($_SERVER['HTTPS'])) {
			return 'https://';
		}
		return 'http://';
	}

}
