<?php

	require('../config.php');

	$get = GETPOST('get');
	
	switch ($get) {
		case 'search-user':
			
			__out(_search_user(GETPOST('q')),'json');
						
			break;
		
	}

function _search_user($name) {
	global $db;
	
	$Tab = array();
	
	$res = $db->query("SELECT login FROM ".MAIN_DB_PREFIX."user WHERE login LIKE '".$db->escape($name)."%'");
	while($obj = $db->fetch_object($res)) {
		
		$Tab[] = $obj->login;
		
	}
	
	return $Tab;
	
	
}
