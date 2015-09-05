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