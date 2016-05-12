<?php

	require('../config.php');
	dol_include_once('/network/class/network.class.php');
	dol_include_once('/projet/class/project.class.php');
	dol_include_once('/product/class/product.class.php');
	

	$get = GETPOST('get');
	$put = GETPOST('put');
	
	switch ($get) {
		case 'search-user':
			
			__out(_search_user(GETPOST('q')),'json');
						
			break;
		case 'search-tag':
			
			__out(_search_tag(GETPOST('q')),'json');
						
			break;
		case 'search-element':
			
			__out(_search_element(GETPOST('q')),'json');
						
			break;
		case 'comments':
			
			print _comments(GETPOST('id'),GETPOST('ref'), GETPOST('element'));
			
			break;
		case 'graph':
			
			__out(_graph(GETPOST('id'),GETPOST('ref'), GETPOST('element')),'json');
			
			break;
			
	}

	switch ($put) {
		case 'comment':
			
			print _comment(GETPOST('id'),GETPOST('ref'), GETPOST('element'), GETPOST('comment'));
			
			break;
	}

function _graph($fk_object,$ref,$element) {
	$TLink=array();
	TNetMsg::getLinkFor($TLink,$fk_object, $element, TNetMsg::getTag($element, $ref) );
	
	return $TLink;
}

function _comment($fk_object,$ref,$element,$comment) {
	$PDOdb=new TPDOdb;
	
	$t=new TNetMsg;
	
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
	
	$element_tag = TNetMsg::getTag($element, $ref);
	
	$PDOdb=new TPDOdb;
	$r='';
	$Tab = $PDOdb->ExecuteAsArray("SELECT DISTINCT t.rowid
	FROM ".MAIN_DB_PREFIX."netmsg t  
	 WHERE (t.fk_object=".(int)$id." AND t.type_object='".$element."') OR (t.comment LIKE '%".$element_tag."%')
	 ORDER BY t.date_cre DESC");
	foreach($Tab as &$row) {
				
		$netmsg = new TNetMsg;
		$netmsg->load($PDOdb, $row->rowid);		
		
		$r.='<div class="comm">';
		
		if($id!=$netmsg->fk_object || $element!=$netmsg->type_object) {
			$origin_element = $netmsg->getNomUrl();
			if(!empty($origin_element)) $r.='<div class="object">'.$origin_element.'</div> ';	
		}
		
		
		$r.=$netmsg->getComment();
		
		$r.='<div class="date">'.dol_print_date($netmsg->date_cre, 'dayhourtextshort').'</div>';
		
		$r.='</div>';
		
	}
	
	return $r;
}

function _search_tag($tag) {
	global $db;
	$Tab = array();
	
	$reg = '/:(\\w+)/';
	
	$res = $db->query("SELECT LOWER(comment) as comment FROM ".MAIN_DB_PREFIX."netmsg
	 WHERE comment LIKE '%:".$db->escape($tag)."_%' LIMIT 100");
	// var_dump($db);
	while($obj = $db->fetch_object($res)) {
		
		preg_match_all($reg, $obj->comment, $match);
		foreach($match[1] as &$m) {
			$Tab[md5($m)] = $m;	
		}
		
	}
	
	
	natsort($Tab);
	
	return $Tab;
}

function _search_element($tag) {
	
}

function _search_user($tag) {
	global $db;
	
	$Tab = array();
	
	$res = $db->query("SELECT login FROM ".MAIN_DB_PREFIX."user WHERE login LIKE '".$db->escape($tag)."%' LIMIT 20");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = trim($obj->login);
	}
	
	$res = $db->query("SELECT CONCAT(code_client,' ',nom) as nom, nom as nom_default  FROM ".MAIN_DB_PREFIX."societe WHERE nom LIKE '".$db->escape($tag)."%' LIMIT 20");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = trim( !empty($obj->nom) ? $obj->nom : $obj->nom_default );	
	}
	
	$res = $db->query("SELECT  CONCAT(s.code_client,'_',p.lastname,' ',p.firstname) as nom,CONCAT(s.nom,'_',p.lastname,' ',p.firstname) as nom_default FROM ".MAIN_DB_PREFIX."socpeople p 
						LEFT JOIN ".MAIN_DB_PREFIX."societe s ON (p.fk_soc=s.rowid)
				WHERE p.firstname LIKE '".$db->escape($tag)."%' OR p.lastname LIKE '".$db->escape($tag)."%' LIMIT 20");
				
	while($obj = $db->fetch_object($res)) {
		
			$Tab[] = trim( !empty($obj->nom) ? $obj->nom : $obj->nom_default );	
		
	}
	
	natsort($Tab);
	
	return $Tab;
	
	
}
