<?php

	require('../config.php');
	dol_include_once('/twiiitor/class/twiiitor.class.php');

	$get = GETPOST('get');
	$put = GETPOST('put');
	
	switch ($get) {
		case 'search-user':
			
			__out(_search_user(GETPOST('q')),'json');
						
			break;
		case 'comments':
			
			print _comments(GETPOST('id'), GETPOST('element'));
			
			break;
	}

	switch ($put) {
		case 'comment':
			
			print _comment(GETPOST('id'), GETPOST('element'), GETPOST('comment'));
			
			break;
	}

function _comment($id,$element,$comment) {
	$PDOdb=new TPDOdb;
	
	$t=new TTwiiit;
	$t->fk_object = $id;
	$t->type_object = $element;
	$t->comment = $comment;
	$t->save($PDOdb);
	
}

function _comments($id, $element) {
	
	$PDOdb=new TPDOdb;
	$r='';
	$Tab = $PDOdb->ExecuteAsArray("SELECT DISTINCT t.rowid
	FROM ".MAIN_DB_PREFIX."twiiit t LEFT JOIN ".MAIN_DB_PREFIX."twiiit_tag tg ON (tg.fk_twiiit=t.rowid) 
	 WHERE (t.fk_object=".(int)$id." AND t.type_object='".$element."') OR (tg.fk_object=".(int)$id." AND tg.type_object='".$element."')
	 ORDER BY t.date_cre DESC");
	foreach($Tab as &$row) {
				
		$twiiit = new TTwiiit;
		$twiiit->load($PDOdb, $row->rowid);		
		
		$r.='<div class="comm">'.$twiiit->getComment().'</div>';
		
	}
	
	return $r;
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
