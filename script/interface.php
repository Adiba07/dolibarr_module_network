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
			
			print _comments(GETPOST('id'),GETPOST('ref'), GETPOST('element'));
			
			break;
	}

	switch ($put) {
		case 'comment':
			
			print _comment(GETPOST('id'),GETPOST('ref'), GETPOST('element'), GETPOST('comment'));
			
			break;
	}

function _comment($fk_object,$ref,$element,$comment) {
	$PDOdb=new TPDOdb;
	
	$t=new TTwiiit;
	
	/*if($element == 'user' || $element =='company' || $element == 'contact') {
		$element_tag = '@';
	}
	else {
		$element_tag = '#';
	}
	
	//$element_tag.=$element.':';
	
	$element_tag.=$ref;
	*/
	$t->fk_object = $fk_object;
	$t->comment = $comment;
	$t->type_object = $element;
	$t->ref= $ref;
	$t->save($PDOdb);
	
}

function _comments($id,$ref, $element) {
	
	if($element == 'user' || $element =='company' || $element =='societe' || $element == 'contact') {
		$element_tag = '@';
	}
	else {
		$element_tag = '#';
	}
	$element_tag.=$ref;
	
	$PDOdb=new TPDOdb;
	$r='';
	$Tab = $PDOdb->ExecuteAsArray("SELECT DISTINCT t.rowid
	FROM ".MAIN_DB_PREFIX."twiiit t  
	 WHERE (t.fk_object=".(int)$id." AND t.type_object='".$element."') OR (t.comment LIKE '%".$element_tag."%')
	 ORDER BY t.date_cre DESC");
	foreach($Tab as &$row) {
				
		$twiiit = new TTwiiit;
		$twiiit->load($PDOdb, $row->rowid);		
		
		$r.='<div class="comm">'.$twiiit->getComment().'<div class="date">'.dol_print_date($twiiit->date_cre, 'dayhourtextshort').'</div></div>';
		
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
	
	$res = $db->query("SELECT CONCAT(code_client,' ',nom) as nom FROM ".MAIN_DB_PREFIX."societe WHERE nom LIKE '".$db->escape($name)."%'");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = $obj->nom;
	}
	
	$res = $db->query("SELECT CONCAT(lastname,' ',firstname) as nom FROM ".MAIN_DB_PREFIX."socpeople WHERE firstname LIKE '".$db->escape($name)."%' OR lastname LIKE '".$db->escape($name)."%'");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = $obj->nom;
	}
	
	natsort($Tab);
	
	return $Tab;
	
	
}
