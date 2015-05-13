<?php
/////////////////////////////////////////////////////////////////////////////
// $Id
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
/**
 * IsWindows function
 */
/////////////////////////////////////////////////////////////////////////////
if ( ! function_exists('IsWindows'))
{
	function IsWindows()
	{
		$is_windows = FALSE;

		if (strncasecmp(PHP_OS, 'WIN', 3) == 0)
		{
			$is_windows = TRUE;
		}

		return $is_windows;
	}
}

if ( ! function_exists('readCsvFile'))
{
	function readCsvFile($filePath, $skipFirst = true)
	{
		$lines = NULL;

		putenv("LANG=en_US.UTF-8");
		setlocale(LC_ALL, 'en_US.UTF-8');
	
		if (($handle = fopen($filePath, "r")) !== FALSE)
		{
			$i = 0;
			while (($line = fgetcsv($handle)) !== FALSE)
			{
				if ((false == $skipFirst) || ($i > 0))
				{
					for ($j=0; $j<count($line); $j++)
					{
						$lines[$i][$j] = $line[$j];
					}
				}
				$i++;
			}

			fclose($handle);
		}

		return $lines;
	}
}

/**
 * Show info message
 *
 * @param	string
 * @return	void
 */
if ( ! function_exists('ShowMessage'))
{
	function  ShowMessage($message, $redirect = '')
	{
		$CI =& get_instance();

		$CI->session->set_flashdata('message', $message);

		redirect($redirect);
	}
}
