<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * test.php
 *
 * @package default
 */

DEFINE('UNREGISTERED_USER', 1);
DEFINE('REGISTERED_USER', 2);
DEFINE('ADMIN_USER', 10);

class UrlResponseTest
{
	private function __construct() {}

	/**
	 * test
	 */
	public static function test($domain = null)
	{
		require_once 'PHPUtils/Utils.php';
		require_once 'PHPUtils/WebData.php';

		if (empty($domain))
		{
			$domain = self::getDomain();
		}

		self::setLongTermOperation();

		// get customized config info
		global $errors;
		require_once "UrlResponseTest.config.php";

		// Insure we are starting fresh
		self::testPage($config['logout'], '', 200, true, true);

		$pages = self::getPages();
		$users = self::getUsers();
		
		foreach ($users as $user)
		{
			echo $user['description']."<br />\r\n";

			// if it is a registered user
			if ($user['type'] > UNREGISTERED_USER)
			{
				self::testPage($config['login'], $user['postData'],
					200, true, true);
			}
			else
			{
				self::testPage($config['register'], $user['postData'],
					200, true, true);

				// test if registration worked
				$account = null;
				if ((function_exists('getAccountByEmail')) && 
					(is_callable('getAccountByEmail')))
				{
					$account = call_user_func('getAccountByEmail',
						$user['email']);
				}

				if ($account == null)
				{
					echo "<span style=\"color: red;\">".
						"registration user does not exist: $user[email]".
						"</span><br />\r\n";
				}
				else
				{
//					self::testPage(
//						"$domain/account/activate/$account->id/$account->newEmailKey",
//						'', 200, true, false);

					// to test as unregistered user
//					self::testPage($domain.'/account/logout', '', 200, true, true);
				}
			}

			foreach($pages as $page)
			{
				$url = $domain.$page[0];

				// log_message is standard in codeIgniter, others have similar
				if ((function_exists('log_message')) && 
					(is_callable('log_message')))
				{
					call_user_func('log_message','debug', "testing: $url");
				}

				if ($page[1] < $user['type'])
				{
					// first, check to insure we can't go places we shouldn't
					self::testPage($url, '', 302, false, false, 0,
						"redirect check");
				}

				// check that final destination ok
				$info = self::testPage($url, '', 200, true, false, 0, "GET");
				if ($page[2] == TRUE)
				{
/*
					// Test register POST
					if ($page[0] == '/account/edit_account')
					{
						if ($user['type'] == 1)
						{
							$page[3]['is_admin'] = 1; 
						}
						elseif ($user['type'] == 2)
						{
							$page[3]['is_client'] = 1; 
						}

						$page[3]['username'] = $user['username']; 
						$page[3]['email'] = $user['email']; 
					}
					elseif ($page[0] == '/admin/add_account')
					{
						$extra = self::db->insert_id() + 5000;
						$page[3]['username'] = $user['username'] . $extra; 
						$page[3]['email'] = $user['username'] . $extra .
							"@ofjapan.jp"; 
					}

					self::testPage($url, $page[3], 200, true, false,
						$page[4], $page[5]);
*/
				}
			}

			// clean up for next time
//			self::accounts->delete($account->id);
			ob_end_flush();
		}
	}

	private function getDomain()
	{
		// for cron jobs or command line exec, SERVER_NAME may not be set
		if (array_key_exists("SERVER_NAME", $_SERVER))
		{
			$domain = $_SERVER["SERVER_NAME"];
		}
		else
		{
			// cron jobs should predefin this.
			$domain = SERVER_NAME;
		}

		return $domain;
	}

	// if all urls accessible from main page, could use a crawler too
	private function getPages()
	{
		$pages = readCsvFile('pages.csv');

		return $pages;
	}

	private function getUsers()
	{
		$users = array();
		$lines = readCsvFile('accounts.csv');

		foreach ($lines as $line)
		{
			$user['type'] = $line[0];
			$user['description'] = $line[1];
			$user['username'] = $line[2];
			$user['email'] = $line[3];
			$user['postData'] = json_decode($line[4], true);

			$users[] = $user;
		}

		return $users;
	}

	private function postResultCodeCheck($url, $response, $notes)
	{
		if (empty($response['content']))
		{
			echo "<span>page: $url (POST Error: page content empty) $notes".
				"</span><br />\r\n";
		}
		else
		{
			// check for hidden post_result field
			libxml_use_internal_errors(true);
			$document = new DOMDocument;
			$document->loadhtml($response['content']);
			$xPath = new DOMXpath($document);
			
			$post_result = $xPath->evaluate(
				'string(//input[@name="post_result"]/@value)');

			if($post_result == -1)
			{
				if ($expected_error_type == 1)
				{
					echo "<span>HTTP response code: $response[http_code] ".
						"page: $url (POST Error) $notes</span><br />\r\n";
				}
				else
				{
					self::showError($url, $response, $notes);
				}
				$success = false;
			}
		}
	}

	private function setLongTermOperation()
	{
		ini_set('memory_limit','512M');
		ini_set('max_execution_time', 1800); //or 30 minutes

		if (function_exists('apache_setenv'))
		{
			apache_setenv('no-gzip', '1');
		}
		ini_set('implicit_flush', 1);
		ini_set('zlib.output_compression', 0);
		ob_implicit_flush(true);

		header( 'Content-Type: text/html; charset=UTF-8' );
		mb_internal_encoding( 'UTF-8' );
		mb_regex_encoding( 'UTF-8' );
	}

	private function showError($url, $response, $notes)
	{
		echo "<span style=\"color: red;\">HTTP response code: ".
			"$response[http_code] page: $url (POST Error) ".
			"$notes</span><br />\r\n";
	}

	private function testPage($url, $post_data, $checkCode,
		$follow_redirects, $new_session, $expected_error_type = 0,
		$notes = "")
	{
		$success = true;

		global $errors;

		if (NULL == $post_data)
		{
			$use_post = false;
		}
		else
		{
			$use_post = true;
		}

		$info = WebData::getWebPage($url, $use_post, $post_data,
			$follow_redirects, $new_session, true);
//var_dump($info);

		if ($info['http_code'] != $checkCode)
		{
			echo "<span style=\"color: red;\">HTTP response code: $info[http_code] page: ".
				"$url $notes</span><br />\r\n";
			$success = false;
		}

		// check for hidden post_result field
		self::postResultCodeCheck($url, $info, $notes);

		foreach($errors as $error)
		{
			$position = stripos($info['content'], $error);

			if ($position !== FALSE)
			{
				echo "<span style=\"color: red;\">HTTP response code: ".
					"$info[http_code] page: $url ($error) $notes".
					"</span><br />\r\n";
				$success = false;
			}
		}

		if ($success == true)
		{
			echo "<span>HTTP response code: $info[http_code] ".
				"page: $url $notes</span><br />\r\n";
		}

		ob_flush();
		flush();

		return $info;
	}
}

/* End of file test.php */
