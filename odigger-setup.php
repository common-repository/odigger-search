<?php
// Set up globals for oDigger Search
//

$base = dirname(__FILE__);
include_once ($base . '/odigger-util.php');

define('WP_USE_THEMES', false); 
$root_path = Odigger_Util::get_wp_root_path();
if(@is_file($root_path .'/wp-load.php')) {
	include_once($root_path .'/wp-load.php');
}
else {
	die("Error: Could not access WP Loader.  Please contact support at support@odigger.com");
}

// API Defaults
$odigger_search_api_host = "http://odigger.com";
$odigger_search_api_url = $odigger_search_api_host . "/api/v1";
$odigger_search_api_key = get_option("odigger_search_api_key");
$odigger_search_api_get_offers_url = $odigger_search_api_host . "/api/v1/getOffers.php?api_key=" . $odigger_search_api_key;
$odigger_search_api_get_networks_url = $odigger_search_api_host . "/api/v1/getNetworks.php?api_key=" . $odigger_search_api_key;
$odigger_search_api_get_key_url = $odigger_search_api_host . "/api/v1/getAPIKey.php?api_key=" . $odigger_search_api_key;

// Site defaults
$odigger_search_homepage = "http://odigger.com";
$odigger_search_network_signup = "http://odigger.com/network-signup";
$odigger_search_site_name = "oDigger.com";
?>