<?php

	require('config.php');
	dol_include_once('/network/class/network.class.php');
	dol_include_once('/societe/class/societe.class.php');
	dol_include_once('/contact/class/contact.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/product/class/product.class.php');
	dol_include_once('/projet/class/project.class.php');
	
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
		
		$trouve = false;
		while($obj = $db->fetch_object($res)) {
			$trouve = true;
			$o=new Societe($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'societe'
			) ;
		}

		if(!$trouve) {
		$res = $db->query("SELECT rowid  FROM ".MAIN_DB_PREFIX."societe WHERE nom LIKE '".$db->escape($tag)."%'");
		while($obj = $db->fetch_object($res)) {
			$o=new Societe($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'societe'
			) ;
		}
			
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
			
	}
	else if($type_tag == 'rel') {
		
		$res = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."netmsg WHERE comment LIKE '%:".$db->escape($tag)."%'");
		
		$PDOdb=new TPDOdb;
		while($obj = $db->fetch_object($res)) {
					
			$netmsg = new TNetMsg;
			$netmsg->load($PDOdb, $obj->rowid);		
			
			$Tab[] = array(
				'link'=>$netmsg->getNomUrl()
				,'text'=>$netmsg->getComment()
			) ;
		
		}
		
		
	}
	else if($type_tag == 'hashtag') {
		
		$res = $db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."propal WHERE ref = '".$db->escape($tag)."'");
		while($obj = $db->fetch_object($res)) {
			$o=new Propal($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'user'
			) ;
			
		}
		$res = $db->query("SELECT rowid  FROM ".MAIN_DB_PREFIX."facture WHERE facnumber = '".$db->escape($tag)."'");
		while($obj = $db->fetch_object($res)) {
			$o=new Facture($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'societe'
			) ;
			
				
		}
		
				
		$res = $db->query("SELECT rowid  FROM ".MAIN_DB_PREFIX."product WHERE ref = '".$db->escape($tag)."'");
		while($obj = $db->fetch_object($res)) {
			$o=new Product($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'societe'
			) ;
			
				
		}
		
		$res = $db->query("SELECT rowid  FROM ".MAIN_DB_PREFIX."projet WHERE ref LIKE '".$db->escape($tag)."%'");
		while($obj = $db->fetch_object($res)) {
			$o=new Project($db);
			$o->fetch($obj->rowid);
			$Tab[] = array(
				'link'=>$o->getNomUrl(1)
				,'link0'=>$o->getNomUrl(0)
				,'type'=>'societe'
			) ;
			
				
		}
		
				
		
		
			
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
		
		print '<table class="border" width="100%">
		<tr class="liste_titre">
			<td class="liste_titre">'.$langs->trans('Elements').'</td>
		</tr>
		';
		
		$class= '';
		foreach($Tab as $link) {
			$class = ($class == 'impair') ? 'pair' : 'impair';
			
			print '<tr class="'.$class.'"><td>';
			
			print $link['link'];
			
			if(!empty($link['text'])) print ' '. $link['text']; 
			
			print '</td></tr>';
			
		}
		
		print '</table>';
		
	}
	
	
	
	dol_fiche_end();
	
	llxFooter();