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
		
		 $comm = preg_replace('/@(\\w+)/','<a href="'.dol_buildpath('/twiiitor/hashtag.php?tag=$1&type_tag=user',1).'">$0</a>',$comm);
		 $comm = preg_replace('/#(\\w+)/','<a href="'.dol_buildpath('/twiiitor/hashtag.php?tag=$1&type_tag=hashtag',1).'">$0</a>',$comm);
		 $comm = preg_replace('/:(\\w+)/','<a href="'.dol_buildpath('/twiiitor/hashtag.php?tag=$1&type_tag=rel',1).'">$0</a>',$comm);
		
		 return $comm;
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