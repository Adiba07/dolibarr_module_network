<?php

	require('config.php');
	dol_include_once('/twiiitor/class/twiiitor.class.php');
	dol_include_once('/societe/class/societe.class.php');
	dol_include_once('/contact/class/contact.class.php');
	
	
	$tag = GETPOST('tag');
	$type_tag = GETPOST('type_tag');
	
	if($type_tag == 'user') {
		
		$res = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE login = '".$db->escape($tag)."'");
		while($obj = $db->fetch_object($res)) {
			$o=new User($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'user'
			) ;
			
		}
		$res = $db->query("SELECT rowid  FROM ".MAIN_DB_PREFIX."societe WHERE code_client = '".$db->escape($tag)."'");
		while($obj = $db->fetch_object($res)) {
			$o=new Societe($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'societe'
			) ;
			
				
		}
		
		list($code, $nom) = explode('_', $tag);
	
		$res = $db->query("SELECT p.rowid 
					FROM ".MAIN_DB_PREFIX."socpeople p LEFT JOIN ".MAIN_DB_PREFIX."societe s ON (p.fk_soc=s.rowid)
					WHERE (s.code_client = '".$db->escape($code)."' OR s.nom='".$db->escape($code)."' ) AND p.lastname='".$db->escape($nom)."'");
					
		while($obj = $db->fetch_object($res)) {
			$o=new Contact($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'contact'
			) ;
			
				
		}
	
	$res = $db->query("SELECT  CONCAT(s.code_client,'_',p.lastname,' ',p.firstname) as nom,CONCAT(p.lastname,' ',p.firstname) as nom_default FROM ".MAIN_DB_PREFIX."socpeople p 
						LEFT JOIN ".MAIN_DB_PREFIX."societe s ON (p.fk_soc=s.rowid)
				WHERE p.firstname LIKE '".$db->escape($name)."%' OR p.lastname LIKE '".$db->escape($name)."%'");
				
				
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
		preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/i', $Tab[0]['link0'], $match);
		
		if(!empty($match[2][0])) {
			header('location:'.$match[2][0]); 
			exit;
			
		}
	}

	
	llxHeader();
	
	dol_fiche_head();
	
	if(empty($Tab)) {
		print "Aucun object ne correspond à ce tag.";		
	}
	else{
		foreach($Tab as $link) {
			
			print $link['link'].'<br >';
			
		}
		
	}
	
	
	
	dol_fiche_end();
	
	llxFooter();