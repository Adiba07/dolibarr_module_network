<?php

	require('../config.php');
	dol_include_once('/network/class/network.class.php');
	dol_include_once('/projet/class/project.class.php');
	dol_include_once('/product/class/product.class.php');
	
	$langs->load('network@network');

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
			
			print _comments(GETPOST('id'),GETPOST('ref'), GETPOST('element'), GETPOST('start'));
			
			break;
		case 'graph':
			
			__out(_graph(GETPOST('id'),GETPOST('ref'), GETPOST('element')),'json');
			
			break;
			
	}

	switch ($put) {
		case 'comment':
			
			print _comment(GETPOST('id'),GETPOST('ref'), GETPOST('element'), GETPOST('comment'));
			
			break;
			
		case 'remove-comment':
			
			print _removeComment(GETPOST('id'));
			
			break;
			
	}

function _removeComment($id) {
	
	$PDOdb=new TPDOdb;
	$t=new TNetMsg;
	if($t->load($PDOdb, $id)) {
		$t->delete($PDOdb);
		return 'ok';
	}
	
	
	return 'ko';
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
	$reg = '/:[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ]*/i';
	$comment = preg_replace_callback($reg, function($matches) {
//		var_dump(dol_string_unaccent($matches[0]));
		return strtolower(dol_string_unaccent($matches[0]));
	}, $comment);
	
	$t->fk_object = $fk_object;
	$t->comment = $comment;
	$t->type_object = $element;
	$t->ref= $ref;
	$t->save($PDOdb);
	
}

function _comments($id,$ref, $element, $start = 0, $length=10) {
	global $user,$langs,$db;
	
	$element_tag = TNetMsg::getTag($element, $ref);
	
	$PDOdb=new TPDOdb;
	$r='';
	$Tab = $PDOdb->ExecuteAsArray("SELECT DISTINCT t.rowid,date_cre
	FROM ".MAIN_DB_PREFIX."netmsg t  
	 WHERE 
		(t.fk_object=".(int)$id." AND t.type_object='".$element."') 
		OR (t.comment LIKE '%".$element_tag."%')
	 ORDER BY t.date_cre DESC
	 LIMIT ".$start.",".($length+1));
	 
	$TUser=array();
	 
	foreach($Tab as $k=>&$row) {
				
		if($k>=$length) {
			$r.='<div class="comm showMore" start="'.$start.'" length="'.$length.'" style="text-align:center"><a href="javascript:;" onclick="NetworkLoadComment('.($start+$length).')">&#x25BC; '.$langs->trans('ShowMore').' &#x25BC;</a></div>';
		}
		else{
			$netmsg = new TNetMsg;
			$netmsg->load($PDOdb, $row->rowid);		
			
			$r.='<div class="comm" commid="'.$netmsg->getId().'">'.PHP_EOL;
			
			if($id!=$netmsg->fk_object || $element!=$netmsg->type_object) {
				$origin_element = $netmsg->getNomUrl('badge network_badge origin-element');
				if(!empty($origin_element)) $r.='<div class="object">'.$origin_element.'</div> ';
			}
			
			
			$r.=$netmsg->getComment().PHP_EOL;
			
			if($netmsg->fk_user == $user->id) {
				$author = '';
			} 
			else { 
			 	if(empty($TUser[$netmsg->fk_user])) {
					$TUser[$netmsg->fk_user]=new User($db);
					$TUser[$netmsg->fk_user]->fetch($netmsg->fk_user);
				}
				$author = $TUser[$netmsg->fk_user]->getFullName($langs);
			}
			
			if(($netmsg->fk_user == $user->id && $user->rights->network->write) || $user->rights->network->admin) {
				 $r.='<div class="delete"><a href="javascript:networkRemoveComment('.$netmsg->getId().')">'.img_delete().'</a></div>'.PHP_EOL;
			}
			$r.='<div class="date">'.(empty($author) ? '' : $author.' - ').dol_print_date($netmsg->date_cre, 'dayhourtextshort').'</div>'.PHP_EOL;
			
			$r.='</div>'.PHP_EOL;
			
			
		}	
	}
	
	return $r;
}

function _search_tag($tag) {
	global $db;
	$Tab = array();
	
	$reg = '/:(\\w+)/';
	
	$res = $db->query("SELECT LOWER(comment) as comment FROM ".MAIN_DB_PREFIX."netmsg
	 WHERE comment LIKE '%:".$db->escape($tag)."_%' LIMIT 10");
	// var_dump($db);
	while($obj = $db->fetch_object($res)) {
		
		preg_match_all($reg, $obj->comment, $match);
		foreach($match[1] as &$m) {
			$Tab[md5($m)] = dol_string_unaccent($m);	
		}
		
	}
	
	
	natsort($Tab);
	
	return $Tab;
}

function _search_element($tag) {
	global $db;
	
	$Tab = array();
	$res = $db->query("SELECT CONCAT(ref,' ',label) as label FROM ".MAIN_DB_PREFIX."product WHERE ref LIKE '".$db->escape($tag)."%' OR label LIKE '%".$db->escape($tag)."%' LIMIT 10");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = trim($obj->label);
	}
	
	$res = $db->query("SELECT ref as label FROM ".MAIN_DB_PREFIX."propal WHERE ref LIKE '".$db->escape($tag)."%' LIMIT 10");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = trim($obj->label);
	}
	
	$res = $db->query("SELECT facnumber as label FROM ".MAIN_DB_PREFIX."facture WHERE facnumber LIKE '".$db->escape($tag)."%' LIMIT 10");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = trim($obj->label);
	}
	
	$res = $db->query("SELECT CONCAT(ref,' ',title) as label FROM ".MAIN_DB_PREFIX."projet WHERE ref LIKE '".$db->escape($tag)."%' LIMIT 10");
	while($obj = $db->fetch_object($res)) {
		$Tab[] = trim($obj->label);
	}
	
	natsort($Tab);
	
	return $Tab;
}

function _search_user($tag)
{
    global $db;

    $Tab = array();

    $res = $db->query("SELECT login FROM ".MAIN_DB_PREFIX."user WHERE login LIKE '".$db->escape($tag)."%' LIMIT 10");
    while ($obj = $db->fetch_object($res))
    {
        $Tab[] = trim($obj->login);
    }

    $res = $db->query("SELECT nom FROM ".MAIN_DB_PREFIX."usergroup WHERE nom LIKE '".$db->escape($tag)."%' LIMIT 10");
    while ($obj = $db->fetch_object($res))
    {
        $Tab[] = TNetMsg::simpleString($obj->nom);
    }

    $res = $db->query("SELECT CONCAT(code_client,' ',nom) as nom, nom as nom_default  FROM ".MAIN_DB_PREFIX."societe WHERE nom LIKE '".$db->escape($tag)."%' LIMIT 10");
    while ($obj = $db->fetch_object($res))
    {
        $Tab[] = TNetMsg::simpleString(!empty($obj->nom) ? $obj->nom : $obj->nom_default);
    }

    $res = $db->query("
                SELECT  CONCAT(s.code_client,'|',p.lastname,' ',p.firstname) as nom,CONCAT(s.nom,'|',p.lastname,' ',p.firstname) as nom_default 
                FROM ".MAIN_DB_PREFIX."socpeople p
                LEFT JOIN ".MAIN_DB_PREFIX."societe s ON (p.fk_soc=s.rowid)
				WHERE p.firstname LIKE '".$db->escape($tag)."%' OR p.lastname LIKE '".$db->escape($tag)."%' LIMIT 10");
    while ($obj = $db->fetch_object($res))
    {

        $Tab[] = TNetMsg::simpleString(!empty($obj->nom) ? $obj->nom : $obj->nom_default);

    }

    natsort($Tab);

    return $Tab;
}
