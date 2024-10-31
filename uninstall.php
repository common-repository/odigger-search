<?php

if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
{
	exit();
}

delete_option('odigger_search_api_key');
delete_option('odigger_search_button_color');
delete_option('odigger_search_results_title');
delete_option('odigger_search_show_attribution');
delete_option('odigger_search_results_title');
delete_option('odigger_search_network_links');
delete_option('odigger_search_sidebar_widget_options');
delete_option('odigger_search_results_width');
delete_option('odigger_search_input_width');
delete_option('odigger_search_page_id');
?>