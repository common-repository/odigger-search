<?php

// This function sets the search preferences of the user.  
// This way, for example, if they change their sort order, their query is still maintained.
// 
function odigger_search_set_preferences()
{
	$base = dirname(__FILE__);
	require_once ($base . '/odigger-util.php');
	Odigger_Util::my_session_start();

	// Move preferences from POST params to Session variables
	//
	$q = Odigger_Util::getArrayParam($_GET, "odigger_q");
	$page = Odigger_Util::getArrayParam($_GET, "odigger_page");
	$order = Odigger_Util::getArrayParam($_GET, "odigger_order");
	$by = Odigger_Util::getArrayParam($_GET, "odigger_by");
	$nid = Odigger_Util::getArrayParam($_GET, "odigger_network");
	$limit = Odigger_Util::getArrayParam($_GET, "odigger_limit");
	
	$_SESSION["odigger_order"] = $_GET;
	$_SESSION["odigger_by"] = $by;
	$_SESSION["odigger_page"] = $page;
	$_SESSION["odigger_q"] = $q;
	$_SESSION["odigger_network"] = $nid;
	$_SESSION["odigger_limit"] = $limit;

}

odigger_search_set_preferences();


?>