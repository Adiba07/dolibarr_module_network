<?php

class NetMsg extends SeedObject {
/*
 * Ordre de fabrication d'équipement
 * */
 	static $regex_hashtag = '/#((\\w|-)+)/';
	static $regex_arobase = '/@((\\w|-)+)/';
	static $regex_colon =   '/:((\\w|-)+)/';
 
	public $element = 'netmsg';
	
	public $table_element = 'netmsg';
	
	function __construct($db) {
		
		$this->db = &$db;
		
 		global $user;
	
 		$this->fields=array(
 				'fk_object'=>array('type'=>'integer', 'index'=>true)
 				,'fk_user'=>array('type'=>'integer', 'index'=>true)
 				,'type_object'=>array('type'=>'string','length'=>50, 'index'=>true)
 				,'ref'=>array('type'=>'string','length'=>50, 'index'=>true)
 				,'comment'=>array('type'=>'string','length'=>140)
 		);
 		
 		$this->init();
 		
		$this->fk_user = $user->id;
		
	}
	
	function getNomUrl() {
		
		$db = &$this->db;
		
		$type= $this->type_object;
		//if($type == 'projet') $type='project';
		
		$object_name = ucfirst($type);
		
		if($object_name=='Societe') dol_include_once('/societe/class/societe.class.php');
		else if($object_name=='Contact') dol_include_once('/contact/class/contact.class.php');
		else if($object_name=='Facture')dol_include_once('/compta/facture/class/facture.class.php');
		else if($object_name=='Propal')dol_include_once('/comm/propal/class/propal.class.php');
		else if($object_name=='Product')dol_include_once('/product/class/product.class.php');
		else if($object_name=='Project')dol_include_once('/projet/class/project.class.php');
		else if($object_name=='Usergroup')dol_include_once('/user/class/usergroup.class.php');
		
		if(class_exists($object_name)) {
			
			$o=new $object_name($db);
			if($o->fetch($this->fk_object)>0) {
				
				if(method_exists($o, 'getNomUrl')) {
					return $o->getNomUrl(1);
				}
				else if($o->element == 'usergroup') {
					$link = '<a href="'.dol_buildpath('/user/group/card.php?id='.$o->id,1).'">'.$o->name.'</a>';
					return $link;
				}
			}
			
			
		}

		return '';		
	}
	
	function getComment() {
		 $comm = $this->comment;

		 $comm = preg_replace(NetMsg::$regex_arobase,'<a class="user badge network_badge" href="'.dol_buildpath('/network/hashtag.php?tag=$1&type_tag=user',1).'">$0</a>',$comm);
		 $comm = preg_replace(NetMsg::$regex_hashtag,'<a class="object badge network_badge" href="'.dol_buildpath('/network/hashtag.php?tag=$1&type_tag=hashtag',1).'">$0</a>',$comm);
		 $comm = preg_replace(NetMsg::$regex_colon,'<a class="rel badge network_badge" href="'.dol_buildpath('/network/hashtag.php?tag=$1&type_tag=rel',1).'">$0</a>',$comm);
		
		 return $comm;
	}
	
	static function extractTags($s, $TReg) {
		$Tab = array();
	
		foreach($TReg as $reg) {
			preg_match_all($reg, $s, $match);
			foreach($match[0] as &$m) {
				if(strlen($m)>1) $Tab[md5($m)] = $m;	
			}
			
		}
	
		return $Tab;
	}
	
	static function getRefByObject(&$object) {
		global $db;
		
		$ref = '';
				
		if($object->element == 'societe' && !empty($object->code_client)) $ref = $object->code_client;
		else if($object->element == 'societe' ) NetMsg::simpleString($ref = $object->name);
		else if($object->element == 'contact' ) {
			dol_include_once('/societe/class/societe.class.php');
			
			$soc=new Societe($db);
			$soc->fetch($object->socid);
			$ref = NetMsg::simpleString(( !empty( $soc->code_client ) ? $soc->code_client : $soc->name ).'_'.$object->lastname);
		}
		else if($object->element == 'user' && !empty($object->login)) $ref = $object->login;
		else if($object->element == 'usergroup' && !empty($object->name)) $ref = self::simpleString($object->name);
		elseif(!empty($object->ref))$ref = $object->ref;
			
		
		
		return $ref;
	}
	
	static function simpleString($s) {
		
		return dol_string_unaccent(dol_string_nospecial($s));
		
	}
	
	static function getRef($fk_object, $type_object) {
		global $db;
		
		$object_name = ucfirst($type_object);
		if(class_exists($object_name)) {
			
			$object=new $object_name($db);
			if($object->fetch($fk_object)>0) {
				return NetMsg::getRefByObject($object);
			}
		}
		
		
		return '';
		
	}
	
	static function getLinkFor(&$Tab, $fk_object=0, $element ='',$tag='', $level = 1) {
		global $db;
		
		
		if($level > 5 || strlen($tag)<=1) return false;
		
		$res = $db->query("SELECT fk_object, type_object, comment 
				FROM ".MAIN_DB_PREFIX."netmsg WHERE 
				(fk_object = ".(int)$fk_object." AND type_object='".$db->escape($element)."')
				OR comment LIKE '%".$db->escape($tag)."%'");
		while($obj = $db->fetch_object($res)) {
			
			$TTag = NetMsg::extractTags($obj->comment, array(NetMsg::$regex_arobase,TNetMsg::$regex_hashtag ));
			if($obj->fk_object>0 && !empty($obj->type_object)) $TTag[] = NetMsg::getTag($element, NetMsg::getRef($obj->fk_object, $obj->type_object));
			
			$TTagRel = NetMsg::extractTags($obj->comment, array( NetMsg::$regex_colon ));
			if(empty($TTagRel))$TTagRel=array(' ');
			
			foreach($TTag as $t) {
				if($tag == $t || strlen($tag)<=1) continue;
				
				foreach($TTagRel as $rel) {
					
					if(empty($rel)) continue;
					
					$checksum = md5( $tag.'.'.$t.'.'.$rel ) ;
					
					if(!isset($Tab[$checksum])) {
						$Tab[$checksum] = array(
							'from'=>$tag
							,'to'=>$t
							,'label'=>$rel
						);
						
						NetMsg::getLinkFor($Tab, 0, '',$t, $level+1);
					}
					
				}
				
			}
			
		}
		
	}
	
	static function getTag($element, $ref) {
		$element_tag='';
		if($element == 'user' || $element == 'usergroup' || $element =='company' || $element =='societe' || $element == 'contact') {
			$element_tag = '@';
		}
		else {
			$element_tag = '#';
		}
		$element_tag.=$ref;
		
		return strtr($element_tag, array( ' '=> '_' ));
	}
	
}