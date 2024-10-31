<?php 

// oDigger Search Utility class
// 
class Odigger_Util
{
	// function to get array params with no danger of raising warning
	// messages for attempting to grab unset POST array values
	//
	static public function getArrayParam($array, $param)
	{
		$returnVar = "";
		if (array_key_exists($param,$array) && isset($array[$param]))
		{
			$returnVar = $array[$param];
		}
		return $returnVar;
	}
	
	// get pretty date time for results table
	//
	static public function getPrettyDateTime($date)
	{
		$prettyDate = Odigger_Util::formatSqlDateTime($date, 'Y-m-d');
		$today = date( 'Y-m-d' );
		$yesterday = date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
					 	
		$returnDate = $prettyDate;
		if ($prettyDate == $today)
		{
			$returnDate = "Today";
		}
		else if ($prettyDate == $yesterday)
		{
			$returnDate = "Yesterday";
	 	}
	 	else
 		{
	 		$returnDate = Odigger_Util::formatSqlDateTime($date, 'M j');
	
	 	}
	 	return $returnDate;
	}

	// Convert Sql Date Times to date objects
	//
	static public function formatSqlDateTime($datetime, $format, $substituteDateTime = "")
	{
		if ($datetime == '1970-01-01 00:00:00' || $datetime == '2037-12-31 00:00:00')
		{
			return $substituteDateTime;
		}
	
		$date_time_array = split(" ", $datetime);
		$date_array = split("-", $date_time_array[0]);
		$year = $date_array[0];
		$month = $date_array[1];
		$day = $date_array[2];
		return date($format, mktime(0,0,0,$month, $day, $year));
	}

	static public function my_session_start()
	{
		// Start session to store search options.
		// Must be first to avoid warning message.
		//
		if (!isset($_SESSION))
		{
			session_start();
		}
	}

	// Get the current page's url
	//
	static public function curPageURL() 
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";

		if ($_SERVER["SERVER_PORT"] != "80") 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} 
		else 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		if ($pageUrl)
		{
			$pageUrlParts = split ($pageUrl, "?");
			$pageURL = $pageUrlParts[0];
		}
		return $pageURL;
	}

	// Method used to get the config root of the WP instance
	//
	static function get_wp_root_path()
	{
	    $base = dirname(__FILE__);
	    $path = false;
	
	    if (@file_exists(dirname(dirname($base))."/wp-config.php"))
	    {
	        $path = dirname(dirname($base));
	    }
	    else
	    if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php"))
	    {
	        $path = dirname(dirname(dirname($base)));
	    }
	    else
	    $path = false;
	
	    if($path != false)
	    {
	        $path = str_replace("\\", "/", $path);
	    }
	    return $path;
	}
}

Odigger_Util::my_session_start();

?>