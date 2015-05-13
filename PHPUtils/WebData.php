<?php

class WebData
{
	private function __construct() {}

	/////////////////////////////////////////////////////////////////////////
	/**
	 * getWebPage function
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @param	string
	 * @param	bool
	 * @param	bool
	 * @param	bool
	 * @param	string
	 * @param	int
	 * @param	int
	 */
	/////////////////////////////////////////////////////////////////////////
	public static function getWebPage($url, $usePost = false, $sendData = '',
		$followRedirects = true, $newSession = false,
		$includeErrorInfo = false, $agent = 'WebData Library', $timeOut = 0,
		$maxRedirects = -1)
	{
		$curlObject = self::initialize($url);

		curl_setopt($curlObject, CURLOPT_FOLLOWLOCATION, $followRedirects);

		// identifies the caller (us)
		curl_setopt($curlObject, CURLOPT_USERAGENT, $agent);
		curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, $timeOut);
		// timeout on response
		curl_setopt($curlObject, CURLOPT_TIMEOUT, $timeOut);
		curl_setopt($curlObject, CURLOPT_MAXREDIRS, $maxRedirects);

		if ($newSession == true)
		{
			curl_setopt($curlObject, CURLOPT_COOKIEJAR, "cookie.txt"); 
			curl_setopt($curlObject, CURLOPT_COOKIESESSION, true);
		}
		else
		{
			curl_setopt($curlObject, CURLOPT_COOKIEFILE, "cookie.txt"); 
		}

		curl_setopt($curlObject, CURLOPT_POST, $usePost);

		if (TRUE == $usePost)
		{
			curl_setopt($curlObject, CURLOPT_POSTFIELDS, $sendData);
		}

		$content = curl_exec($curlObject);
		$header  = curl_getinfo($curlObject);

		if ($includeErrorInfo == true)
		{
			$header['errorNumber'] = curl_errno($curlObject);
			$header['errorMessage'] = curl_error($curlObject);

			$header['content'] = $content;
		}

		curl_close($curlObject);

		if ($includeErrorInfo == true)
		{
			return $header;
		}
		else
		{
			return $content;
		}
	}

	private static function initialize($url)
	{
		$curlObject	= curl_init($url);

		curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlObject, CURLOPT_HEADER, true);
		// handle all encodings
		curl_setopt($curlObject, CURLOPT_ENCODING, "");
		// set referer on redirect
		curl_setopt($curlObject, CURLOPT_AUTOREFERER, true);
		curl_setopt($curlObject, CURLOPT_VERBOSE, 1);
		curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

		return $curlObject;
	}

}

?>
