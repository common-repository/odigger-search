<?php
/*
	Plugin Name: oDigger Affiliate Offer Search
	Plugin URI: http://odigger.com/api/v1

	Description: Add a new income stream to your blog with the oDigger Search plugin.  This plugin allows your users to search through 35,000+ affiliate offers and 100+ affiliate networks right from your site and it allows you to collect referral commissions on any users that sign up to a network through your blog.  

				 Affiliates are constantly researching new offers and opportunities and now then can do it right from your site.  This provides a better user experience for your users since they can search through oDigger's ever growing database of affiliate offers while staying on your blog.  

				 The oDigger Search plugin also enables your website to make money in a new and lucrative way: you can put your affiliate network referral URL into the search results! This means you earn commissions off any affiliate who signs up to an affiliate network through your search results link. Most affiliate networks offer a lifetime 5% commission of all income earned by any affiliates you refer. If you sign up just 5 heavy hitting affiliates, who earn just a grand a day (there are a lot of them out there), then you're making $250 a day in passive income for life!

				 Upon installing the oDigger search plugin, it will add a page to your blog where users can perform searches for affiliate programs. You also have the option to add a little sidebar widget with a search field that will take people to your affiliate program search page when people enter search queries. This will help drive traffic to your search feature and get affiliates signing up to networks under your links.

				 The plugin is free.  So to get oDigger Search functionality and start making money today just download and install it.

				 To see a live version of the plugin at work visit http://insideaffiliate.net/offers/. You can also learn more by visiting http://odigger.com/api/v1/
				 
	Version: 1.37
	Author: oDigger.com 
	License: GPL2

	Copyright 2010  oDigger.com  gz@odigger.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	$base = dirname(__FILE__);
	require_once ($base. "/odigger-setup.php");
	require_once ($base . "/odigger-search-client.php");
	require_once ($base . "/odigger-util.php");
   
	
	// Set up constants
	//
	if (!defined('WP_CONTENT_DIR')) {
		define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
	}
	if (!defined('WP_CONTENT_URL')) {
		define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
	}
	if (!defined('WP_PLUGIN_DIR')) {
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
	}
	if (!defined('WP_PLUGIN_URL')) {
		define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
	}
	
	if (!defined('ODIGGER_OPTION_PREFIX')) {
		define('ODIGGER_OPTION_PREFIX', 'odigger_');
	}
	
	if (!defined('ODIGGER_OPTION_NETWORK_LINK_SUFFIX')) {
		define('ODIGGER_OPTION_NETWORK_LINK_SUFFIX', '_link');
	}	
	
	// Load settings
	if (is_admin()) {
		include_once($base . '/odigger-search-config.php');
	}
	
	//Sidebar widgets
	include_once($base . '/odigger-search-widgets.php');
	
	// Initialize plugin 
	//
	add_action('init', 'odigger_search_init');
	
	// Initializes oDigger Search Plugin
	//  
	function odigger_search_init()
	{
		add_filter('the_content', 'embed_odigger_search_table');
		
		wp_enqueue_script('jquery');
		wp_register_script('odigger_js', WP_PLUGIN_URL . '/odigger-search/odigger.js');
		wp_enqueue_script('odigger_js');
		
		add_action('wp_head', 'odigger_css');
		add_action('wp_head', 'odigger_js');
		
		add_shortcode('odigger_offer_search', 'odigger_offer_search_func');
		
		add_option('odigger_search_show_attribution', true);
		
		// Going from v1.32 to 1.33 requires setting the odigger_search_page_id option from the previous 
		// corresponding widget option for backwards compatability
		// 
		$widget_options = get_option('odigger_search_sidebar_widget_options');
		if (!($page_id = get_option('odigger_search_page_id')) &&
			 isset($widget_options['page_id']) && 
			 $widget_options['page_id'] > 0) { 
			 	
			$page_id = (int) $widget_options['page_id'];
			$widget_options['page_id'] = -1;

			update_option('odigger_search_sidebar_widget_options', $widget_options);
			update_option('odigger_search_page_id', $page_id);
		}
	}
	
	
	// This function takes the content of a page/post, 
	// looks for the token "[ODIGGER_SEARCH_RESULTS]"
	// and replaces it with a call to get oDigger Search Results
	// which returns a table of affiliate offer search results
	//
	// Legacy.  Use shortcode instead.
	function embed_odigger_search_table($content)
	{
		$return_content = $content;
		
		// Check for token
		//
		if (strpos($content, "[ODIGGER_SEARCH_RESULTS]") !== FALSE) 
		{
			// If oDigger Search Client has been properly installed then replace token
			// or remove token if it has not been installed properly
			//
			if (class_exists("Odigger_Search_Client"))
			{
				$return_content = str_replace("[ODIGGER_SEARCH_RESULTS]", Odigger_Search_Client::get_client(), $content);
			}
			else
			{
				$return_content = str_replace("[ODIGGER_SEARCH_RESULTS]", "", $content);
			}
		}
		
		return $return_content;
	}
	
	// shortcode function for [odigger_offer_search width="300"]
	//
	function odigger_offer_search_func ($atts) {
		$return_content = "";
		
		extract(shortcode_atts(array(
					'width' => ''
				), $atts));

		if (class_exists("Odigger_Search_Client"))
		{
			$return_content = Odigger_Search_Client::get_client($width);
		}

		return $return_content;
	}
	

	// Initializes oDigger CSS files
	//
	function odigger_css() 
	{
		echo "<!-- oDigger Search CSS -->\n";
		// This is the main CSS file with default CSS
		//
		echo "<link rel='stylesheet' href='" . WP_PLUGIN_URL . "/odigger-search/style/odigger.css' type='text/css' media='all' />\n";
		
		// This is the custom CSS file that users should modify to get a custom look. 
		// This file will not be overwritten in future upgrades
		//
		// This is legacy.  If you want to modify the default CSS you will need to do it in a theme.
		echo "<link rel='stylesheet' href='" . WP_PLUGIN_URL . "/odigger-search/style/odigger-custom.css' type='text/css' media='all' />\n";
	}

	// Initialize Custom oDigger Search JS
	//
	function odigger_js()
	{
		// Pass needed WP Constants to JS.  Not sure if there is a better way to do this.
		//
		echo "<script type='text/javascript'>";
		echo "var odigger_search_wp_plugin_url = '" . WP_PLUGIN_URL . "'";
 		echo "</script>";
	}
?>
