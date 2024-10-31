<?php

	/*
	* Widgets
	*/

	// This function sets up the oDigger Search Sidebar Widget.
	// This is a small sidebar form that allows the user to do an oDigger Search from 
	// any page on the blog that isn't the main search results page.  They just enter a
	// search term into the form, hit "search" and they are taken to the search results page
	// for their search term.
	//
	function odigger_search_sidebar_widget($args)
	{
		extract($args, EXTR_SKIP);

		add_action('wp_head', 'odigger_widget_css');

		$options = get_option('odigger_search_sidebar_widget_options');
		$title = empty($options['title']) ? __('oDigger Affiliate Offer Search') : apply_filters('widget_title', $options['title']);
		$subtitle = empty($options['subtitle']) ? __('oDigger Affiliate Offer Search') : apply_filters('widget_title', $options['subtitle']);
		
		$show_attribution = $options['show_attribution'] == 0 ? false : true;

		// For backwards compatability
		//
		if (!$page_id = get_option('odigger_search_page_id')) 
			$page_id = (int) $options['page_id'];
		
		$button_style = get_option("odigger_search_button_color");
		$button_style = isset($button_style) && "default" != strtolower($button_style) ? "style='background:url(" . WP_PLUGIN_URL . "/odigger-search/images/search_button_" . $button_style . ".jpg) no-repeat left top;height:23px;width:104px;'" : "";
		
		// Don't show sidebar widget on results page
		//
		$current_page = get_the_ID();
		if ($current_page != $page_id)
		{
			$site_url = get_site_url();
			echo $before_widget . $before_title . $title . $after_title;
			echo "<div class='odigger_search_sidebar_widget'>";
			echo "<div class='sub-heading'>" . $subtitle . "</div>";
			echo "<form method='POST' action='{$site_url}?page_id=" . $page_id . "' >
				    <input type='text' id='odigger_query' name='query' class='query' onclick='odigger_remove_pre_fill(document.getElementById(\"odigger_query\"));'></input>
				    <input type='submit' class='search-button' " . $button_style . " value='' onclick='odigger_remove_pre_fill(document.getElementById(\"odigger_query\"));'></input>
				    <div class='clearDiv'></div>";
			
			if ($show_attribution)
			{
				echo "<a class='attribution' href='http://odigger.com' title='oDigger.com Affiliate Offers Search Engine'></a>";
			}
			
			echo "</form>";
						
			echo "<script type='text/javascript'>odigger_pre_fill('odigger_query');</script>";
			echo "<div class='clearDiv'></div>";
			echo "</div>";
			echo $after_widget;
		}
	}
	
	// This function sets up the options for the oDigger Search sidebar widget.
	// So far the available options are:
	//   Title -- the heading of the sidebar element
	//   Subtitle -- the sub heading of the sidebar element
	//   Page ID -- the page id of the oDigger Search results page
	//   Show Attribution -- whether or not the oDigger logo shows up on the sidebar widget with a link to oDigger.com
	//
	function odigger_search_sidebar_widget_control() 
	{
		$options = get_option('odigger_search_sidebar_widget_options') ? get_option('odigger_search_sidebar_widget_options') : array();
		
		if ( isset($_POST["odigger_search_sidebar_widget_submit"]) ) 
		{
			$options['title'] = strip_tags(stripslashes($_POST["odigger_search_sidebar_widget_title"]));
			$options['subtitle'] =  strip_tags(stripslashes($_POST["odigger_search_sidebar_widget_subtitle"]));
//			$options['page_id'] = (int) $_POST["odigger_search_sidebar_widget_page_id"];
			$options['show_attribution'] = isset($_POST["odigger_search_sidebar_widget_show_attribution"]) &&
										         "on" == $_POST["odigger_search_sidebar_widget_show_attribution"] ? true : false;
			
			update_option('odigger_search_sidebar_widget_options', $options);
		}	
		
		$title = esc_attr($options['title']);
		$subtitle = esc_attr($options['subtitle']);
//		$page_id = $options['page_id'];
		$show_attribution = $options["show_attribution"];
		
?>		
		<p>
			<label for="odigger_search_sidebar_widget_title"><?php _e('Title:'); ?> <input class="widefat" id="odigger_search_sidebar_widget_title" name="odigger_search_sidebar_widget_title" type="text" value="<?php echo $title; ?>" /></label>
		</p>
		<p>
			<label for="odigger_search_sidebar_widget_subtitle"><?php _e('Subtitle:'); ?> <input class="widefat" id="odigger_search_sidebar_widget_subtitle" name="odigger_search_sidebar_widget_subtitle" type="text" value="<?php echo $subtitle; ?>" /></label>
		</p>
<!-- 		<p>
			<label for="odigger_search_sidebar_widget_page_id"><?php _e('Results Page ID:'); ?> <input style="width: 25px; text-align: center;" id="odigger_search_sidebar_widget_page_id" name="odigger_search_sidebar_widget_page_id" type="text" value="<?php echo $page_id; ?>" /></label><br />
			<label style='font-size:10px;color:#999'>This should be the page ID of the main oDigger Search Results page of your blog. See <a href='http://www.techtrot.com/wordpress-page-id/'>"Finding The WordPress Page ID"</a>.</label>
		</p>
 -->		
		<p>
			<label for="odigger_search_sidebar_widget_show_attribution"><?php _e('Show oDigger Logo In Widget:'); ?> <input type="checkbox" id="odigger_search_sidebar_widget_show_attribution" name="odigger_search_sidebar_widget_show_attribution" <?php if ($show_attribution) echo 'checked="checked"'; ?>/></label>
			<label style='font-size:10px;color:#999'>We work hard to make this plugin free for all.  Please help us out by letting people know that oDigger is on your site.  Thanks!</label>
		</p>
		<input type="hidden" id="odigger_search_sidebar_widget_submit" name="odigger_search_sidebar_widget_submit" value="1" />
<?php

	}
 
	// Initialize the odigger search widgets/
	// This just calls the appropriate WP functions to get our widget to show up in the admin screens.
	//
	function odigger_search_widgets_init() 
	{
		if (function_exists("wp_register_sidebar_widget"))
		{
			wp_register_sidebar_widget('odigger_search_sidebar_widget', 'oDigger Search Sidebar Widget', 'odigger_search_sidebar_widget');
			wp_register_widget_control('odigger_search_sidebar_widget', 'oDigger Search Sidebar Widget', 'odigger_search_sidebar_widget_control');
		}
		else
		{
			register_sidebar_widget('oDigger Search Sidebar Widget', 'odigger_search_sidebar_widget');
			register_widget_control('oDigger Search Sidebar Widget', 'odigger_search_sidebar_widget_control');
		}
	}
		
	add_action('widgets_init', 'odigger_search_widgets_init');
?>
