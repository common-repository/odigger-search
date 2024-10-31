function odigger_set_preferences() 
{	
	if(jQuery('#overlay')) {          jQuery('#overlay').addClass('loading'); }
	if(jQuery('#loading-gif')) {          jQuery('#loading-gif').addClass('visible'); }

	jQuery.ajax({ 
		url: odigger_search_wp_plugin_url + "/odigger-search/odigger-set-prefs.php", 
		data: jQuery('#offers-search').serialize(),
		success: function(){
        	odigger_get_offers();
      }});	
}


function odigger_get_offers(limit, page, order, by) 
{
	if(jQuery('#overlay')) {          jQuery('#overlay').addClass('loading'); }
	if(jQuery('#loading-gif')) {      jQuery('#loading-gif').addClass('visible'); }
	
	
	jQuery('#search-results').load(
			odigger_search_wp_plugin_url + "/odigger-search/odigger-get-offers.php",
			{ odigger_limit:limit, odigger_order:order, odigger_by:by, odigger_page:page }, 
			function() {
				if(jQuery('#overlay')) {          jQuery('#overlay').removeClass('loading'); }
				if(jQuery('#loading-gif')) {          jQuery('#loading-gif').removeClass('visible'); }
	
				odigger_pre_fill("odigger_query");
		});
}

function odigger_remove_pre_fill(element)
{
	if ( element === undefined )
		return;
	
	if ((element.value).toLowerCase() == ("search affiliate offers"))
	{
		element.value = "";
		if (element.id == "odigger_query")
		{
			element.style.color = "#333";
		}
	}
}

function odigger_pre_fill(elementID)
{
	if ( elementID === undefined || elementID == "")
		return;
	
	element = document.getElementById(elementID);
	if (element.value == "")
	{
		element.value = "Search Affiliate Offers";
		element.style.color = "#888";
	}
}