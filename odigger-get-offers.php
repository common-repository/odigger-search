<?php

	// This function gets the oDigger Search affiliate offer results from the oDigger API
	// and displays them in a sortable table.  
	//
	function odigger_search_get_offers()
	{
		// Since this file can be loaded with AJAX, make sure that it includes
		// the proper setup files.
		//
		$base = dirname(__FILE__);
		if (@file_exists($base . "/odigger-util.php")) {
		    	include_once ($base . "/odigger-util.php");	
	    		include_once ($base . "/odigger-setup.php");
	    	}		

		// Make sure includes worked
		//
		if (!class_exists("Odigger_Util"))
		{
			die("Sorry, cannot get affiliate offers at this time (1)");
		}
		
		// Make sure session is started
		//
		Odigger_Util::my_session_start();

		// Setup globals
		//
		global $odigger_search_api_get_offers_url;
		global $odigger_search_homepage;
		global $odigger_search_site_name;
		global $odigger_search_network_signup;
		
		// Gather search settings from SESSION and POST variables. 
		// 
		$q = Odigger_Util::getArrayParam($_SESSION, "odigger_q");
		$page = Odigger_Util::getArrayParam($_SESSION, "odigger_page");
		$order = Odigger_Util::getArrayParam($_SESSION, "odigger_order");
		$by = Odigger_Util::getArrayParam($_SESSION, "odigger_by");
		$nid = Odigger_Util::getArrayParam($_SESSION, "odigger_network"); 
		$limit = Odigger_Util::getArrayParam($_SESSION, "odigger_limit");
		
		$new_order = Odigger_Util::getArrayParam($_POST, "odigger_order");
		$new_page = Odigger_Util::getArrayParam($_POST, "odigger_page");
		$new_by = Odigger_Util::getArrayParam($_POST, "odigger_by");
		$new_limit = Odigger_Util::getArrayParam($_POST, "odigger_limit");

		// Determine which settings have changed
		//
		if (isset($new_limit) && !empty($new_limit) && $limit != $new_limit)
		{
			$limit = $new_limit;
			$_SESSION["odigger_limit"] = $limit;
		}
		if (isset($new_page) && !empty($new_page) && $page != $new_page)
		{
			$page = $new_page;
			$_SESSION["odigger_page"] = $page;
		}
		if (isset($new_order) && !empty($new_order) && $order != $new_order)
		{
			$order = $new_order;
			$_SESSION["odigger_order"] = $order;
		}
		if (isset($new_by) && !empty($new_by) && $by != $new_by)
		{
			$by = $new_by;
			$_SESSION["odigger_by"] = $by;
		}
		
		// set default limit
		//
		if (!isset($limit) || $limit < 20 || $limit == "undefined")
		{
			$limit = 20;
		}
		
		// Boundry case: pages start at 1
		// 
		if ($page <= 0 || $page == "undefined")
		{
			$page = 1;
		}
		
		// set default order by 
		if (!isset($by) || empty($by) || $by == "undefined")
		{
			$by = "desc";
		}
		
		if (!isset($order) || empty($order) || $order == "undefined")
		{
			$order = "added";
		}
		
		// Build the getOffers API request URL
		//
		$query_string = "&q=" . urlencode($q) . "&pg=" . $page . "&order=" . $order . "&by=" . $by . "&network_id=" . $nid . "&num=" . $limit;

		// Execute the API call to get the search results
		//
		$all_offers = file_get_contents($odigger_search_api_get_offers_url . $query_string);
		
		// Load xml search results into xml object
		//
		$xml = simplexml_load_string($all_offers, 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR);
		if ( !$xml ||
		 	 !$result = $xml->xpath("/getOffers/result"))
		{
			die ("Sorry.  Could not get affiliate offers at this time (2).");
		}
		
		// Bit of a hack: the way the result node gets read in by xpath makes it the first element of the result array
		// 
		$result = $result[0];
		$offers = $xml->xpath("//offer"); 
				
		// If the search returned offers, display them
		//
		if (count($offers) > 0)
		{
			// Get the search query used to get these results
			//
			$search_string = empty($q) ? "All Affiliate Offers" : ucfirst($q);

			// Create Results Info text.  I.E. Results 1 - 20 of 20,000 for Diet Pills
			//  
			$range_max = ((int)$result->offset + (int)$result->limit) > (int)$result->total_rows
							? (int)$result->total_rows
							: ($result->offset + $result->limit);

			echo "<div class='results-info'>";
			echo "<div class='search-info'>Results <b>" . ((int)$result->offset + 1) . " - " . $range_max . "</b> of <b>" . number_format((int)$result->total_rows) . "</b> for <b>" . $search_string . "</b></div>";
			
			// Create results per page drop down
			//
			?>
			<form id='offers-limit' name='offers-limit' class='offers-limit'><div style='float:left'>Results Per Page: </div><select class='results-per-page' onchange='javascript:
																								odigger_remove_pre_fill(document.getElementById("odigger_query"));
																								odigger_get_offers(
																								this.options[this.selectedIndex].value,
																								<?php echo $page ?>,
																								"<?php echo $order ?>",
																								"<?php echo $by;?>")'>
			<?php
			for ($limit_options = 20; $limit_options <= 100; $limit_options += 20)
			{
				if ($limit_options == $limit)
				{
					$selected_text = "selected";
				}
				else
				{
					$selected_text = "";
				}
				
				echo "<option " . $selected_text . " value='" . $limit_options . "'>" . $limit_options . "</option>";
			}
		
			echo "</select></form>";
			echo "<div class='clearDiv'></div></div>";
			
			// Create results table
			//
			$arrow_class = "";
			$headings = array("name" => "Name",
							  "network" => "Network",
							  "payout" => "Payout",
							  "payout_type" => "Type",
							  "added" => "Added");
			

			echo "<table cellspacing='0' cellpadding='0'><tr>";
			$by_opposite = $by == "asc" ? "desc" : "asc";
			foreach ($headings as $param => $heading)
			{
				$arrow = "";
				if ($param == $order)
				{
					$arrow = $by == "asc" ? "&#9650;" : "&#9660;";
				}
				
				?>
				<th class='<?php echo $param?>'>
					<a href='#' rel='nofollow' onclick='javascript:odigger_get_offers(<?php echo $limit ?>,<?php echo $page ?>,"<?php echo $param ?>","<?php echo ($param == $order) ? $by_opposite : $by ?>" );'><?php echo $heading ?><?php echo $arrow ?><div class='<?php echo $arrow_class ?>'</div></a>
				</th>
				<?php  
			}
			echo "</tr>";
			
			$class = "shaded";
			$network_links_options_array = get_option("odigger_search_network_links");
			
			foreach ($offers as $offer)
			{
				$option_name = "odigger_" . $offer->network_id . "_link";
				$network_url = isset($network_links_options_array[$option_name]) &&
							   !empty($network_links_options_array[$option_name]) ?
							   		$network_links_options_array[$option_name] : 
							   		$offer->network_url;
				
				$offer_link = isset($offer->landing_page) && !empty($offer->landing_page) 
								? "<a href='" . esc_attr($offer->landing_page) . "' target='_blank'>" . $offer->title . "</a>"
								: "<span>" . $offer->title . "</span>"; 
				
							   		
				$class = "shaded" == $class ? "" : "shaded";

				// Determine if we should show a $ value or % value for the offer payout
				//
				$commission = isset($offer->commission) && (0 < $offer->commission)
									? (float) $offer->commission 
									: 0;

				$payout = isset($offer->payout) && (0 < $offer->payout)
									? (float)  $offer->payout 
									: 0;								
										
				$offer_payout_string = (($commission >= $payout) && ("commission" == $offer->payout_type)) ||
				 						(($commission >= $payout) && (!isset($offer->payout_type) || empty($offer->payout_type) || 0 == $payout))
											? " " . number_format($commission, 2) . "%" 
											: "$" . number_format($payout, 2);
											
				$offer_payout_type_string = (($commission >= $payout) && ("commission" == $offer->payout_type)) ||
				 							(($commission >= $payout) && (!isset($offer->payout_type) || empty($offer->payout_type) || 0 == $payout))
												? "commission"
												: $offer->payout_type;
																
				// Fill in the rows of the search results
				//
				echo "<tr class='$class'>";
				echo "<td class='landing-page'>{$offer_link}</td>";
				echo "<td class='network-url'><a href='" . esc_attr($network_url) . "' target='_blank'>" . $offer->network_name . "</a></td>";
				echo "<td class='payout'>" . $offer_payout_string . "</td>";
				echo "<td class='payout-type'>" . $offer_payout_type_string . "</td>";
				echo "<td class='added'>" . Odigger_Util::getPrettyDateTime($offer->added) . "</td>";
				echo '</tr>';
		
			}
			echo '</table>';
			
			// Build paging
			//
			$total_pages = ceil($result->total_rows / $result->limit);
			$current_page = ($result->offset / $result->limit) + 1;
		

			if ($total_pages > 1)
			{
				echo '<div class="pages">';
				if ($current_page != 1)
				{
				?>
					<div><a href='#' onclick='javascript:odigger_get_offers("<?php echo $current_page - 1?>","", "<?php echo $by?>");'><&nbsp;Previous&nbsp;</a></div>
				<?php
				}
	
				for ($page = 1; $page <= $total_pages; $page++)
				{
					$divider = "";
					if ($page > ($current_page - 10) && $page < ($current_page + 9))
					{
						$divider = '<div class="divider">|</div>';
					}
					if ($page == $current_page)
					{
						echo '<span class="curr_page">' . $current_page . '</span>' . $divider;
					}
					else if ($page > $current_page - 10 && $page < $current_page + 10)
					{
				?>
				<div><a href="#" onclick="javascript:odigger_get_offers(<?php echo $limit ?>, '<?php echo $page ?>','', '<?php echo $by?>');"><?php echo $page ?></a></div><?php echo $divider; ?>
				<?php
					}
				}

				if ($current_page != $total_pages) {
				?>
					<div>&nbsp;<a href='#' onclick='javascript:odigger_get_offers(<?php echo $limit ?>, "<?php echo $current_page + 1?>","", "<?php echo $by?>");'>Next&nbsp;></a></div>
				<?php
				}
				echo '</div>';
			}
			
			//echo '</div> <!-- end odigger-results -->';
		}
		else
		{
			echo '<p class="no-results">No results were found.  Please check your spelling or try another search. (0)</p>';
		}

		
		// Show the "Add Your Network" link
		//
		$odigger_network_signup_href_start = "<a rel='nofollow' href='" . esc_attr($odigger_search_network_signup) . "' title='Add Your Network To oDigger'>";
		echo "<div class='odigger-network-signup'>{$odigger_network_signup_href_start}Add Your Network Here</a></div>";
		echo '<div class="clearDiv"></div>';		
	}
	 
	// Since this file can be loaded with AJAX, make sure all the WordPress settings are loaded
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

	odigger_search_get_offers();
?>