<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}


global $db;

dol_include_once('/network/class/network.class.php');

$o=new NetMsg($db);
$o->init_db_by_vars();

$db->query("ALTER TABLE ".MAIN_DB_PREFIX."netmsg CHANGE rowid rowid int(11) NOT NULL AUTO_INCREMENT FIRST");

$db->query("UPDATE".MAIN_DB_PREFIX."netmsg SET date_creation=date_cre,tms=date_maj WHERE date_creation IS NULL");
