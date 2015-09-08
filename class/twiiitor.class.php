<?php

class TTwiiit extends TObjetStd{
/*
 * Ordre de fabrication d'équipement
 * */
 	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'twiiit');
	  
		$this->add_champs('fk_object',array('type'=>'int', 'index'=>true));
		$this->add_champs('type_object',array('type'=>'string', 'index'=>true));
		$this->add_champs('comment',array('type'=>'text'));
		
		$this->start();
		
		$this->setChild('TTwiiitTag','fk_twiiit');
	}
	
	function save(&$PDOdb) {
		
		$this->set_tags('/(^|\s)@(\w*)/');
		$this->set_tags('/(^|\s):(\w*)/');
		$this->set_tags('/(^|\s)#(\w*)/');
		
		parent::save($PDOdb);
	}
	
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
	
}
class TTwiiitTag extends TObjetStd{
/*
 * Ordre de fabrication d'équipement
 * */
 	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'twiiit_tag');
	  
		$this->add_champs('fk_twiiit,fk_object',array('type'=>'int', 'index'=>true));
		$this->add_champs('type_object,tag',array('type'=>'string', 'index'=>true));
		
		$this->start();
		
		
	}
	
}