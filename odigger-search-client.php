<?php 

// Using class for encapsulation since not all PHP versions have Namespaces yet.
// This class creates the oDigger search form that is placed on a blog page or post.
// This form in turn is used to generate affiliate offer searches.
// 
class Odigger_Search_Client
{
	// This function does the work of getting affiliate networks from the oDigger API
	// and then using those to create an affiliate offers search form.
	//
	public static function get_client($default_width = "")
	{
		// oDigger API url to get affiliate networks
		//
		global $odigger_search_api_get_networks_url;
		global $odigger_search_homepage;
		
		// The width of the search button and network select dropdown 
		// This is used to expand the width of the search bar.
		// 
		if (!defined('SEARCH_BUTTON_AND_SELECT_WIDTH'))
			define('SEARCH_BUTTON_AND_SELECT_WIDTH', 310);
		
		// Start by making sure the session has started so that we can grab the 
		// search settings (i.e. query, sort column, results per page...)
		//
		Odigger_Util::my_session_start();
	
		
		// Get all networks and counts to set up select drop down
		//
		$all_networks = file_get_contents($odigger_search_api_get_networks_url);
		$xml = simplexml_load_string($all_networks, 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR);
		
		if (!$xml)
		{
			echo ("Sorry, we cannot search affiliate offers at this time. (1)");
			return;
		}
		
		// Select the network node from the xml
		//
		if (!$result = $xml->xpath("//network"))
		{
			echo ("Sorry, we cannot search affiliate offers at this time. (2)");
			return;
		}
		
		// Load any post variables to prefill the search form with.
		// Possible post variables:
		//   query: the search term passed to the getOffers API
		//	 nid:	the network id passed to the getOffers API
		//
		$query = (isset($_POST["query"]) && $_POST["query"] != "") ? $_POST["query"] : "";
		$nid = (isset($_GET["nid"]) && $_GET["nid"] != "") ? $_GET["nid"] : "";
		
		// Get any configuration options that may have been set up in the admin screen that control
		// display of the search form.
		// Possible options so far are:
		// 		Title -- The text displayed above the search form
		// 		Width -- The width of the search form and results
		//		Button Color -- The color of the search button
		//
		$search_title = get_option("odigger_search_results_title") ? trim(get_option("odigger_search_results_title")) : "Search For Affiliate Offers Across Affiliate Networks";
		$search_title = empty($search_title) ? "" : $search_title;

		$results_style = "";
		$results_width = get_option("odigger_search_results_width");
		if (isset($default_width) && is_numeric($default_width) )
			$results_width = $default_width;

		if ( isset($results_width) && is_numeric($results_width) ) 
			$results_style = "style='width:" . $results_width . "px'";

		$form_style = "";
		$input_style = "";
		$bar_width = get_option("odigger_search_bar_width");
		if ( isset($bar_width) && is_numeric($bar_width) ) {
			$input_style = "style='width:" . ($bar_width - SEARCH_BUTTON_AND_SELECT_WIDTH). "px'";
			$form_style = "width:" . ($bar_width) . "px";
		} 

		if (get_option("odigger_search_input_flush_left")) {
			$form_style .= ";float:left;";
		}
		
		$button_style = get_option("odigger_search_button_color");
		$button_style = isset($button_style) && "default" != $button_style ? "style='background:url(" . WP_PLUGIN_URL . "/odigger-search/images/search_button_" . $button_style . ".jpg) no-repeat left top;height:23px;width:104px;'" : "";

		if (get_option("odigger_search_show_attribution"))
		{
			$odigger_home_href_start = "<a href='" . esc_attr($odigger_search_homepage) . "' title='" . esc_attr($odigger_search_site_name) . " Affiliate Offers Search Engine'>";
			$odigger_attribution = "<div class='odigger-attribution' style='{$results_style}'>powered by " . $odigger_home_href_start . "oDigger</a></div>";
		}
		
		
		// Create the offers search form
		//
		$client = '
		<div id="odigger-search" class="odigger-search" ' . $results_style . '>
			<div class="search-title">' . $search_title . '</div>
			<div class="clearDiv"></div>
			<div class="form-wrapper">
			 <form id="offers-search" style="' . $form_style . '" name="offers-search" class="offers-search" 
				   onSubmit="javascript: odigger_remove_pre_fill(document.getElementById(\'odigger_query\')); odigger_set_preferences();return false;">
				<input name="odigger_q" class="q" ' . $input_style . ' id="odigger_query" type="text" value="' . $query . '" onclick="javascript: odigger_remove_pre_fill(this);" ></input>
				<select name="odigger_network" class="network" onchange="javascript: odigger_remove_pre_fill(document.getElementById(\'odigger_query\')); odigger_set_preferences(); return false;"/>
				<option value="0">All Networks</option>';
		
				foreach ($result as $node)
				{

					$selected_text = "";
					if ($node->id == $nid)
					{
						$selected_text = "selected";
					}
					$client .= "<option " . $selected_text . " value='" . $node->id . "'>" . $node->name . "(" . $node->offer_count . ")</option>";
				
				}
				
				$client .= '
		
				<input type="hidden" name="odigger_page" id="page" value="1"></input>
				<input type="hidden" name="odigger_order" id="order" value="added"></input>
				<input type="hidden" name="odigger_by" id="by" value="desc"></input>
				<input type="hidden" name="odigger_limit" id="by" value="20"></input>
				<input type="submit" class="search-button" ' . $button_style . ' onclick="javascript: odigger_remove_pre_fill(document.getElementById(\'odigger_query\')); odigger_set_preferences();return false;" value=""></input>
				<div class="clearDiv"></div>
			</form>
			<div class="clearDiv"></div>
			</div>
			<div class="clearDiv"></div>
			<div id="loading-gif" class="visible"></div>
			<div class="loading" name="overlay" id="overlay"><div id="search-results" name="search-results" class="search-results"></div></div>
			<div class="clearDiv"></div>
		</div> <!--  end odigger-search -->

		<script type="text/javascript">
			jQuery(document).ready(function() {
				odigger_set_preferences();
			});
		</script>' . $odigger_attribution ;
		
		return $client;
	}
}
?>