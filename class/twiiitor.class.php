<?php

class TTwiiit extends TObjetStd{
/*
 * Ordre de fabrication d'équipement
 * */
 	function __construct() {
 		global $user;
		
		$this->set_table(MAIN_DB_PREFIX.'twiiit');
	  
		$this->add_champs('fk_object,fk_user',array('type'=>'int', 'index'=>true));
		$this->add_champs('type_object,ref',array('type'=>'string', 'index'=>true, 'length'=>50));
		$this->add_champs('comment',array('type'=>'string', 'index'=>true, 'length'=>140));
		
		$this->start();
		
		//$this->setChild('TTwiiitTag','fk_twiiit');
		
		$this->fk_user = $user->id;
		
	}
	
	function getNomUrl() {
		global $db;
		
		$type= $this->type_object;
		//if($type == 'projet') $type='project';
		
		$object_name = ucfirst($type);
		if(class_exists($object_name)) {
			
			$o=new $object_name($db);
			if($o->fetch($this->fk_object)>0) {
				
				if(method_exists($o, 'getNomUrl')) {
					return $o->getNomUrl(1);
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
		
		 $comm = preg_replace('/@(\\w+)/','<a class="user" href="'.dol_buildpath('/twiiitor/hashtag.php?tag=$1&type_tag=user',1).'">$0</a>',$comm);
		 $comm = preg_replace('/#(\\w+)/','<a class="object" href="'.dol_buildpath('/twiiitor/hashtag.php?tag=$1&type_tag=hashtag',1).'">$0</a>',$comm);
		 $comm = preg_replace('/:(\\w+)/','<a class="rel" href="'.dol_buildpath('/twiiitor/hashtag.php?tag=$1&type_tag=rel',1).'">$0</a>',$comm);
		
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
		else if($object->element == 'societe' ) $ref = $object->name;
		else if($object->element == 'contact' ) {
			dol_include_once('/societe/class/societe.class.php');
			
			$soc=new Societe($db);
			$soc->fetch($object->socid);
			$ref = trim( (!empty( $soc->code_client ) ? $soc->code_client : $soc->name ).'_'.$object->lastname);
		}
		else if($object->element == 'user' && !empty($object->login)) $ref = $object->login;
		elseif(!empty($object->ref))$ref = $object->ref;
			
		
		
		return $ref;
	}
	
	static function getRef($fk_object, $type_object) {
		global $db;
		
		$object_name = ucfirst($type_object);
		if(class_exists($object_name)) {
			
			$object=new $object_name($db);
			if($object->fetch($fk_object)>0) {
				return TTwiiit::getRefByObject($object);
			}
		}
		
		
		return '';
		
	}
	
	static function getLinkFor(&$Tab, $fk_object=0, $element ='',$tag='', $level = 1) {
		global $db;
		
		
		if($level > 5 || strlen($tag)<=1) return false;
		
		$res = $db->query("SELECT fk_object, type_object, comment 
				FROM ".MAIN_DB_PREFIX."twiiit WHERE 
				(fk_object = ".(int)$fk_object." AND type_object='".$db->escape($element)."')
				OR comment LIKE '%".$db->escape($tag)."%'");
		while($obj = $db->fetch_object($res)) {
			
			$TTag = TTwiiit::extractTags($obj->comment, array('/@(\\w+)/','/#(\\w+)/' ));
			if($obj->fk_object>0 && !empty($obj->type_object)) $TTag[] = TTwiiit::getTag($element, TTwiiit::getRef($obj->fk_object, $obj->type_object));
			
			$TTagRel = TTwiiit::extractTags($obj->comment, array( '/:(\\w+)/' ));
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
						
						TTwiiit::getLinkFor($Tab, 0, '',$t, $level+1);
					}
					
				}
				
			}
			
		}
		
	}
	
	static function getTag($element, $ref) {
		$element_tag='';
		if($element == 'user' || $element =='company' || $element =='societe' || $element == 'contact') {
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
				foreach($this->TTwiiitTag as &$t) {
					if($t->tag === $tag) {
						$found = true;
						break;
					}
					
				}
				
				if(!$found) {
					$k = $this->addChild($PDOdb, 'TTwiiitTag');
					$this->TTwiiitTag[$k]->tag = $tag;
				}
				
			}
			
		}
		
	}
	*/
}

/*
class TTwiiitTag extends TObjetStd{
 	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'twiiit_tag');
	  
		$this->add_champs('fk_twiiit,fk_object',array('type'=>'int', 'index'=>true));
		$this->add_champs('type_object,tag',array('type'=>'string', 'index'=>true));
		
		$this->start();
		
		
	}
	
}*/