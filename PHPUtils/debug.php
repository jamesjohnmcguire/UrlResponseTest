<?php
/**
 * This has been re-factored from the original old and messy 
 * metropolis-listings-v2.1 code.  There is a still a lot of bad code about.
 */

function DebugDump($name, $variable, $htmlcode = false)
{
	global $debug;

	if ($debug == true)
	{
		echo "$name: ";
		
		if ($htmlcode == true)
		{
			echo "<pre><code><xmp>";
		}
		htmlentities(var_dump($variable));
		
		if ($htmlcode == true)
		{
			echo "</xmp></code></pre>";
		}
		else
		{
			echo "<br />\r\n";
		}
	}
}

function DebugEcho($variable, $value = null)
{
	global $debug;

	if ($debug == true)
	{
		if (!is_null($value))
		{
			echo "$variable: $value<br />\r\n";
		}
		else
		{
			echo "$variable<br />\r\n";
		}
	}
}

function DebugVar($variable, $value = null)
{
	global $debug;

	if ($debug == true)
	{
		if (!is_null($value))
		{
			echo "$variable: $value<br />\r\n";
		}
		else
		{
			echo "<span style=\"color: red; \">$variable</span><br />\r\n";
		}
	}
}

function SetErrorReportingOn()
{
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}

?>