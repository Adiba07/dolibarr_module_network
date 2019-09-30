<?php
/**
 * Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
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
 * Class ActionsNetwork
 */
class ActionsNetwork
{
    /**
     * @var DoliDb		Database handler (result of a new DoliDB)
     */
    public $db;

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
     * @var User|UserGroup|Societe|Contact|Product|Propal|Commande|Facture|SupplierProposal|CommandeFournisseur|FactureFournisseur $currentObject
     */
	public $currentObject;

	/**
	 * Constructor
     * @param DoliDB    $db    Database connector
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
	    $this->currentObject = $object;

		return 0;
	}

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action        Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function printCommonFooter($parameters, &$object, &$action, $hookmanager)
    {
        global $langs, $user;


        if (!empty($this->currentObject->id))
        {
            $langs->load('network@network');

            ?>
            <div id="network-container" class="tabBar">
            <?php

            if (!empty($user->rights->network->write))
            {
            ?>
                <div id="network-panel">
                    <div id="network-header" rel="header" class="login_block_elem">
                        <img src="<?php echo dol_buildpath('/network/img/network.png', 1); ?>" border="0" align="absmiddle" />
                        &nbsp;<?php echo Form::textwithtooltip('<b>'.$langs->trans('Network').'</b>', $langs->trans('NetworkHowToUse'), 2, 1, '<span class="fa fa-question-circle" aria-hidden="true"></span>', 'networkHelp', 2); ?>
                    </div>
                    <div id="network-current-object" rel="current_object" class="login_block_elem center nowrap tdoverflowmax300"><b><?php echo $this->currentObject->getNomUrl(1); ?> : </b></div>
                    <div id="network-writer" rel="writer" class="login_block_elem">
                        <?php echo ajax_autocompleter('', 'network_link', dol_buildpath('/network/script/interface.php', 1), '&action=getLinks&json=1', 2, 0, array()); ?>
                        <input type="text" class="" name="search_network_link" id="search_network_link" value="" placeholder="<?php echo $langs->trans('NetworkPlaceHolderLink'); ?>" />

                        <?php echo ajax_autocompleter('', 'network_target', dol_buildpath('/network/script/interface.php', 1), '&action=search&json=1&fk_source='.$this->currentObject->id.'&sourcetype='.get_class($this->currentObject), 2, 0, array()); ?>
                        <style type="text/css">.ui-autocomplete { z-index: 250; }</style>
                        <input type="text" class="" name="search_network_target" id="search_network_target" value="" placeholder="<?php echo $langs->trans('NetworkPlaceHolderTarget'); ?>" />

                    </div>
                    <div id="network-add-comment" rel="add_comment" class="login_block_elem">
                        <input type="button" name="btcomment" class="button butAction" value="<?php echo $langs->trans('NetworkAddLink'); ?>">
                    </div>

                    <div class="clearboth"></div>
                </div>
            <?php
            }

            if (!empty($user->rights->network->read))
            {
            ?>
                <script type="text/javascript" src="<?php echo dol_buildpath('/network/js/network.js.php?fk_source='.$this->currentObject->id.'&sourcetype='.get_class($this->currentObject), 1); ?>"></script>
                <div id="network-comments" class="comments"></div>
            <?php
            }

            ?>
            </div>
            <?php
        }

        return 0;
    }

    function printTopRightMenu($parameters, &$object, &$action, $hookmanager)
    {
        global $user,$langs;

        if (empty($user->rights->network->read)) return 0;

        $langs->load('network@network');
        $text = '<a id="network_block_other" href="'. dol_buildpath('network/list.php', 1).'"><span class="fa fa-hashtag atoplogin" aria-hidden="true"></span></a>';
        $hookmanager->resPrint.= Form::textwithtooltip('', $langs->trans("networkToolTip"), 2, 1, $text, 'network_block_other', 2);

        return 0;
    }
}
