<?php

	require('config.php');
	dol_include_once('/twiiitor/class/twiiitor.class.php');
	
	
	$tag = GETPOST('tag');
	$type_tag = GETPOST('type_tag');
	
	if($type_tag == 'user') {
		
		$res = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE login = '".$db->escape($tag)."'");
		while($obj = $db->fetch_object($res)) {
			$u=new User($db);
			$u->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$u->getNomUrl(1)
				,'link0'=>$u->getNomUrl(0)
				,'type'=>'user'
			) ;
			
		}
		
		/*$res = $db->query("SELECT CONCAT(code_client,' ',nom) as nom FROM ".MAIN_DB_PREFIX."societe WHERE nom LIKE '".$db->escape($name)."%'");
		while($obj = $db->fetch_object($res)) {
			$Tab[] = $obj->nom;
		}
		
		$res = $db->query("SELECT CONCAT(lastname,' ',firstname) as nom FROM ".MAIN_DB_PREFIX."socpeople WHERE firstname LIKE '".$db->escape($name)."%' OR lastname LIKE '".$db->escape($name)."%'");
		while($obj = $db->fetch_object($res)) {
			$Tab[] = $obj->nom;
		}*/
		
			
	}
	else if($type_tag == 'rel') {
			
	} 
	else {
		
	}
	
	if(count($Tab) == 1) {
		
		$a = new SimpleXMLElement($Tab[0]['link0']);
		
		header('location:'.$a['href']); 
		exit;
	}

	
	llxHeader();
	
	dol_fiche_head();
	
	foreach($Tab as $link) {
		
		print $link['link'].'<br >';
		
	}
	
	
	dol_fiche_end();
	
	llxFooter();