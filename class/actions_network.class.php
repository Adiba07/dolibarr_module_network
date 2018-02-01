<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_network.class.php
 * \ingroup network
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsnetwork
 */
class Actionsnetwork
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{

		if(empty($object->element)) return 0;
		
		if(defined('TWIIITOR_ADDED')) return 0;
		
		define('TWIIITOR_ADDED',true);
		
		define('INC_FROM_DOLIBARR', true);
		dol_include_once('/network/config.php');
		dol_include_once('/network/class/network.class.php');
		
		$ref = NetMsg::getRefByObject($object);
		
		if(empty($ref)) return 0;
		
		?>
		<script type="text/javascript" src="<?php echo dol_buildpath('/network/js/network.js.php?element='.$object->element.'&id='.$object->id.'&ref='.$ref,1) ?>"></script>
		<?php	
		
	}
	
	function printTopRightMenu($parameters, &$object, &$action, $hookmanager)
	{
		global $user,$langs;
		
		if (empty($user->rights->network->view->all)) return 0;
		
		$langs->load('network@network');

		$text = '<a id="network_block_other" href="'. dol_buildpath('/network/hashtag.php', 1).'"><span class="fa fa-hashtag atoplogin" aria-hidden="true"></span></a>';
		$hookmanager->resPrint.= Form::textwithtooltip('', $langs->trans("networkToolTip"), 2, 1, $text, 'network_block_other', 2);
		
		return 0;
	}
}