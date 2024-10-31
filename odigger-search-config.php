<?php
	add_action('admin_menu', 'odigger_search_menus');
	add_action('admin_head', 'odigger_search_admin_head');
	add_action('publish_post', 'odigger_search_results_page_check');
	add_action('publish_page', 'odigger_search_results_page_check');
	
	// Initialize all oDigger Search Admin Menus
	//
	function odigger_search_menus() {
		/* Set up WP-Admin menus */
		$plugin_name = dirname(plugin_basename(__FILE__));
		add_menu_page('oDigger Search', 'oDigger Search', 'update_plugins', 'odigger_search', 'odigger_search_config', plugins_url($plugin_name.'/images/odigger_admin.jpg'));
		add_submenu_page('odigger_search', 'General Settings', 'General Settings', 'update_plugins', 'odigger_search', 'odigger_search_config');
		add_submenu_page('odigger_search', 'Network Links', 'Network Links', 'update_plugins', 'odigger_config_network_links', 'odigger_config_network_links');
	}
	
	
	// ****** CSS FOR HEAD SECTION ******
	function odigger_search_admin_head() 
	{ 
		?>
		<!-- Start of oDigger Search Admin CSS additions-->
		<style type="text/css">
		</style>
		<!-- End of oDigger Search Admin CSS additions-->
		<?php
	}

	// "General Settings" config panel
	// As of now, you can modify:
	// 		API KEY -- required for any of the plugin to work 
	//		Search Results Title -- Displayed on top of search results
	//		Search Results Width -- Width of search results table so it fits on their site
	//		Search Results Attribution -- Whether or not an oDigger.com attribution logo show up at the bottom of search results table
	//		Search Button Color -- Color of the search button in the search table and sidebar widget
	//
	function odigger_search_config() 
	{
		global $odigger_search_api_get_key_url;
		
		if (!current_user_can('manage_options'))  
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		// Update Options
		//
		$new_key = "";
		if (!empty($_POST))
		{
			if (isset($_POST['odigger_search_request_key']))
			{
				$new_key = file_get_contents($odigger_search_api_get_key_url);
				update_option("odigger_search_api_key", $new_key);
			}
		}
	
		// Get Options
		//
		$odigger_search_api_key = get_option('odigger_search_api_key');

		// Create Request Key Form
		//
		?>
		<div class="wrap"> 
			<div id="icon-options-general" class="icon32"><br /></div> 
			
			<h2>oDigger Search: General Settings</h2> 
			
			<?php if (isset($_POST['odigger_search_request_key'])): ?>
				<p style="font-weight: bold">New API Key Has Been Requested</p>
			<?php endif; ?>
			
			<form method="POST" action="">
			
			<?php 
				if ( function_exists('wp_nonce_field') )
					wp_nonce_field('odigger-search-get-api-key_');
			?>
				<table class="form-table">
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_api_key">API KEY:</label></th> 
						<td style='width:250px'>
							<input type="hidden" name="odigger_search_request_key" id="odigger_search_request_key" value="Y"/>
							<input style='width:250px;' type="text" id="odigger_search_api_key" name="odigger_search_api_key" value="<?php echo $odigger_search_api_key?>" disabled="disabled"/>
						</td> 
						<td>
							<input type="submit"  value="Generate Key" />&nbsp;&nbsp; <br />
							<p>Please click the "Generate Key" button above.  You must generate a key to activate this plugin.</p>  
							<p>After you generate the key, put this text on any page of your blog: "[odigger_offer_search]".
							This will display the oDigger search results on that page. You can also set the width of the search results 
							by adding a width parameter to the above text like so: "[odigger_offer_search width="500"]".    </p>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?php 
		
		// Create General Settings Form
		//
		?>
		<div class="wrap"> 
			
			<?php 
				if (isset($_POST['odigger_search_general_settings_update']) && 
					!empty($_POST['odigger_search_general_settings_update'])):

					echo '<p style="font-weight: bold">Your changes have been saved.</p>';
			 
					if (!isset($_POST["odigger_search_request_key"])) {
						if (isset($_POST["odigger_search_results_title"])) {
							update_option("odigger_search_results_title", $_POST["odigger_search_results_title"]);
						}
						
						if (isset($_POST["odigger_search_results_width"])) {
							update_option("odigger_search_results_width", $_POST["odigger_search_results_width"]);
						}
						
						if (isset($_POST["odigger_search_bar_width"])) {
							update_option("odigger_search_bar_width", $_POST["odigger_search_bar_width"]);
						}
				
						if (isset($_POST["odigger_search_button_color"])) {
							update_option("odigger_search_button_color", $_POST["odigger_search_button_color"]);
						}
						
						$input_flush_left = false;
						if (isset($_POST["odigger_search_input_flush_left"])) {
							$input_flush_left = $_POST['odigger_search_input_flush_left'] == "on" ? true : false;
						}
						update_option("odigger_search_input_flush_left", $input_flush_left);
										
						$show_attribution = false;
						if (isset($_POST['odigger_search_show_attribution'])) {
							$show_attribution = $_POST['odigger_search_show_attribution'] == "on" ? true : false;					
						}
						update_option("odigger_search_show_attribution", $show_attribution);
					}
					
			
				endif;

				$show_attribution = get_option("odigger_search_show_attribution");
				$input_flush_left = get_option("odigger_search_input_flush_left");
			
			?>
						
			<form method="POST" action="">
				<?php 
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('odigger-search-search-results-settings_');
				?>			
				<table class="form-table">
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_page_id">oDigger Search Page ID:</label></th> 
						<td style='width:250px'>
<?php 				
							$odigger_search_page_id = get_option("odigger_search_page_id") !== FALSE
														? get_option("odigger_search_page_id") 
														: "";
							if (isset($_POST["odigger_search_page_id"]))
							{
								$odigger_search_page_id = $_POST["odigger_search_page_id"]; 
								update_option("odigger_search_page_id", $odigger_search_page_id);
							}
?>						
							<input style='width:250px;' 
									type="text" 
									id="odigger_search_page_id" 
									name="odigger_search_page_id" 
									value="<?php echo $odigger_search_page_id?>"/>
												
						</td> 
						<td>			
							This is the ID of the page where you added the text "[odigger_offer_search]". If you are not 
							sure how to find the page ID, please see <a href='http://www.techtrot.com/wordpress-page-id/'>"Finding The WordPress Page ID"</a>.    
						</td>
					</tr>				
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_results_title">Title:</label></th> 
						<td style='width:250px'>
							<input type="hidden" name="odigger_search_general_settings_update" id="odigger_search_general_settings_update" value="Y"/>
							<input style='width:250px;' type="text" id="odigger_search_results_title" name="odigger_search_results_title" value="<?php echo get_option("odigger_search_results_title");?>" />
						</td> 
						<td>This is the text that will show up above the oDigger search results.  Use a space character to have no title</td>
					</tr>
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_results_width">Search Results Width:</label></th> 
						<td style='width:250px'>
							<input style='width:80px;' type="text" id="odigger_search_results_width" name="odigger_search_results_width" value="<?php echo get_option("odigger_search_results_width");?>" />
						</td> 
						<td>(Integer) Use this field to control the width of search results on all pages.  This option is just for convenience. You may overwrite this with theme CSS.</td>
					</tr>
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_bar_width">Search Bar Width:</label></th> 
						<td style='width:250px'>
							<input style='width:80px;' type="text" id="odigger_search_bar_width" name="odigger_search_bar_width" value="<?php echo get_option("odigger_search_bar_width");?>" />
						</td> 
						<td>(Integer) Use this field to control the width of the search term input field.  This option is just for convenience. You may overwrite this with theme CSS.</td>
					</tr>
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_input_flush_left">Search Bar Flush Left:</label></th> 
						<td style='text-align:left'>
							<input  type="checkbox" id="odigger_search_input_flush_left" name="odigger_search_input_flush_left" <?php if ($input_flush_left) echo 'checked="checked"'; ?> />
						</td>
						<td>By default, the search bar is centered.  Check this box to flush the search bar to the left.</td>
					</tr>
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_show_attribution">Show oDigger Logo at bottom of search results:</label></th> 
						<td style='text-align:left'>
							<input  type="checkbox" id="odigger_search_show_attribution" name="odigger_search_show_attribution" <?php if ($show_attribution) echo 'checked="checked"'; ?> />
						</td>
						<td >We work hard to make this plugin free for all.  Please help us out by letting people know that oDigger is on your site.  Thanks!</td> 
					</tr>
					<?php 
						$selected_color = get_option("odigger_search_button_color") ? get_option("odigger_search_button_color") : "default";
					?>
					<tr valign="top"> 
						<th scope="row"><label for="odigger_search_button_color">Search Button Color:</label></th> 
						<td style='text-align:left'>
							<input  type="radio" name="odigger_search_button_color" value="default" <?php echo $selected_color == 'default' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="blue" <?php echo $selected_color == 'blue' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_light_blue.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="dark_blue" selected <?php echo $selected_color == 'dark_blue' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_dark_blue.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="red" <?php echo $selected_color == 'red' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_red.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="orange" <?php echo $selected_color == 'orange' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_orange.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="grey" <?php echo $selected_color == 'grey' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_grey.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="black" <?php echo $selected_color == 'black' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_black.jpg' /></input>
							<input  type="radio" name="odigger_search_button_color" value="green" <?php echo $selected_color == 'green' ? "checked" : ""; ?>><img src='<?php echo WP_PLUGIN_URL?>/odigger-search/images/search_button_green.jpg' /></input>
						</td> 
					</tr>
					<tr>
						<td>
							<input type="submit" value="Save Settings" class="button" />
						</td>
						<td></td><td></td>
					</tr>
				</table>
			</form>
		</div>		
		<?php 
	}

	// Network Links configuration
	// This allows the plugin user to use their own affiliate network links
	// in the oDigger Search results.
	//
	function odigger_config_network_links() 
	{
		if (!current_user_can('manage_options'))  
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		global $odigger_search_api_get_networks_url;
		
		$hidden_field_name = 'odigger_submit_hidden';
		
		// See if the user has posted us some information
		// If they did, this hidden field will be set to 'Y'
		$options_submitted = false;
		if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) 
		{
			$options_submitted = true;
		}
		
		// Get the list of networks from the oDigger API
		//
		$nets = odigger_search_getNetworksXML(file_get_contents($odigger_search_api_get_networks_url));
		
		echo "<h2>Update Network Links</h2>";
		echo "<p>Use this configuration panel to use your own network referral links in the oDigger Search Results</p>";
		echo "<form id='net-links' action='' method='POST'>";
		
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('odigger-search-update-net-links_');
	
		echo "<input type='hidden' name='" . $hidden_field_name . "' value='Y'>";
		  
		
		// Create 3 columns of networks and inputs for their corresponding referral links
		//
		$network_links_options_array = get_option("odigger_search_network_links") ? get_option("odigger_search_network_links") : array();
		$count = 1;
		$networks_per_column = ceil (count($nets) / 3);
		$new_table = false;
		echo "<table style='float:left;margin:0 30px 0 0;' cellspacing='0' cellpadding='0'>";
		foreach($nets as $network)
		{
			if (true == $new_table)
			{
				echo "<table style='float:left;margin:0 30px;' cellspacing='0' cellpadding='0'>";
				$new_table = false;
			}
			
			$option_name = ODIGGER_OPTION_PREFIX . $network->id . ODIGGER_OPTION_NETWORK_LINK_SUFFIX;
	
			if ($options_submitted && array_key_exists($option_name, $_POST) && !empty($_POST[$option_name]))
			{ 
		 		$network_links_options_array[$option_name] = $_POST[$option_name];  
		   	}
			
		   	$option_value = isset($network_links_options_array[$option_name]) && 
		   					!empty($network_links_options_array[$option_name]) ? 
		   						  $network_links_options_array[$option_name] : "";
		     
		    echo "<tr>";
		    echo "<td style='text-align:right'>" . $network->name . ":&nbsp;</td>";
		    echo "<td><input style='width:250px' type='text' value='" . $option_value . "' name='" . $option_name . "'/></td>";
		    echo "</tr>";
		    
			if (0 == $count % ($networks_per_column + 1))
			{
				$new_table = true;	
			}
		    if (true == $new_table)
		    {
				echo "</table>";		    	
		    }
		    $count++;
		  }
		  if (false == $new_table)
		  {
		  	echo "</table><br />";
		  }
		  
		  echo "<div style='clear:both'></div>";
		  update_option( "odigger_search_network_links", $network_links_options_array);
		?>
		
		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
		</p>
		</form>
		<?php
	
	}
	
	// Utility function to grab xml network data from the oDigger API 
	// 
	function odigger_search_getNetworksXML($all_networks)
	{
			// First get all networks and counts to set up select drop down
			//
			$xml = simplexml_load_string($all_networks, 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR);
			
			if (!$xml)
			{
				wp_die( __("Sorry, we cannot get a list of networks at this time.  Please make sure you have generated an API Key from the 'General Settings' menu. (1)"));
			}
			
			// Select the network node from the xml
			//
			if (!$result = $xml->xpath("//network"))
			{
				wp_die( __("Sorry, we cannot get a list of networks at this time. (2)"));
			}
	
		return $result;
	}
	
	// Determine if oDigger Search results are on this page and update the widget options accordingly
	//
	function odigger_search_results_page_check ($post_id) {
		
		$odigger_search_page_id = get_option("odigger_search_page_id");
		
		if (0 < $odigger_search_page_id)
			return $post_id;
		
		$post = get_post($post_id);

		$pattern = get_shortcode_regex();
		$matches = array();
		$matches_count = preg_match('/'.$pattern.'/s', $post->post_content, $matches);
		
		if ($matches_count > 0) {
			foreach ($matches as $k=>$match) {
				if ("odigger_offer_search" == $match)
					update_option("odigger_search_page_id", $post_id);
			}
		}

		return $post_id;
	}
?>
