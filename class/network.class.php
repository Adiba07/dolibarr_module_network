<?php

class TNetMsg extends TObjetStd{
/*
 * Ordre de fabrication d'équipement
 * */
 	static $regex_hashtag = '/#((\\w|-)+)/';
	static $regex_arobase = '/@((\\w|-)+)/';
	static $regex_colon =   '/:((\\w|-)+)/';
 
 	function __construct() {
 		global $user;
		
		$this->set_table(MAIN_DB_PREFIX.'netmsg');
	  
		$this->add_champs('fk_object,fk_user',array('type'=>'int', 'index'=>true));
		$this->add_champs('type_object,ref',array('type'=>'string', 'index'=>true, 'length'=>50));
		$this->add_champs('comment',array('type'=>'string', 'index'=>true, 'length'=>140));
		
		$this->start();
		
		//$this->setChild('TNetMsgTag','fk_netmsg');
		
		$this->fk_user = $user->id;
		
	}
	
	function getNomUrl() {
		global $db;
		
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
	
	function save(&$PDOdb) {
		
		/*$this->set_tags('/(^|\s)@(\w*)/');
		$this->set_tags('/(^|\s):(\w*)/');
		$this->set_tags('/(^|\s)#(\w*)/');
		*/
		//$PDOdb->debug=true;
		parent::save($PDOdb);
	}
	
	function getComment() {
		 $comm = $this->comment;
		
		 $comm = preg_replace(TNetMsg::$regex_arobase,'<a class="user" href="'.dol_buildpath('/network/hashtag.php?tag=$1&type_tag=user',1).'">$0</a>',$comm);
		 $comm = preg_replace(TNetMsg::$regex_hashtag,'<a class="object" href="'.dol_buildpath('/network/hashtag.php?tag=$1&type_tag=hashtag',1).'">$0</a>',$comm);
		 $comm = preg_replace(TNetMsg::$regex_colon,'<a class="rel" href="'.dol_buildpath('/network/hashtag.php?tag=$1&type_tag=rel',1).'">$0</a>',$comm);
		
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
		else if($object->element == 'societe' ) TNetMsg::simpleString($ref = $object->name);
		else if($object->element == 'contact' ) {
			dol_include_once('/societe/class/societe.class.php');
			
			$soc=new Societe($db);
			$soc->fetch($object->socid);
			$ref = TNetMsg::simpleString(( !empty( $soc->code_client ) ? $soc->code_client : $soc->name ).'_'.$object->lastname);
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
				return TNetMsg::getRefByObject($object);
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
			
			$TTag = TNetMsg::extractTags($obj->comment, array(TNetMsg::$regex_arobase,TNetMsg::$regex_hashtag ));
			if($obj->fk_object>0 && !empty($obj->type_object)) $TTag[] = TNetMsg::getTag($element, TNetMsg::getRef($obj->fk_object, $obj->type_object));
			
			$TTagRel = TNetMsg::extractTags($obj->comment, array( TNetMsg::$regex_colon ));
			if(empty($TTagRel))$TTagRel=array(' ');
			//var_dump($tag,$TTag,$TTagRel);
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
						
						TNetMsg::getLinkFor($Tab, 0, '',$t, $level+1);
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
	
	/*
	function set_tags($pattern) {
		$TTag = array();
		//print $this->comment.' '.$pattern.'<br>';
		$nb = preg_match_all($pattern, $this->comment, $TTag);
		if($nb>0) {
		//print '<pre>';
		//var_dump($TTag);
			
			$PDOdb=new TPDOdb;
			
			foreach($TTag[0] as $tag) {
				$found=false;
				foreach($this->TNetMsgTag as &$t) {
					if($t->tag === $tag) {
						$found = true;
						break;
					}
					
				}
				
				if(!$found) {
					$k = $this->addChild($PDOdb, 'TNetMsgTag');
					$this->TNetMsgTag[$k]->tag = $tag;
				}
				
			}
			
		}
		
	}
	*/
}

/*
class TNetMsgTag extends TObjetStd{
 	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'netmsg_tag');
	  
		$this->add_champs('fk_netmsg,fk_object',array('type'=>'int', 'index'=>true));
		$this->add_champs('type_object,tag',array('type'=>'string', 'index'=>true));
		
		$this->start();
		
		
	}
	
}*/
