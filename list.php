<?php
/* Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
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

require 'config.php';
dol_include_once('network/class/network.class.php');

if(empty($user->rights->network->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('network@network');


$massaction = GETPOST('massaction', 'alpha');
$confirmmassaction = GETPOST('confirmmassaction', 'alpha');
$toselect = GETPOST('toselect', 'array');

$object = new Network($db);

$hookmanager->initHooks(array('networklist'));

if ($object->isextrafieldmanaged)
{
    $extrafields = new ExtraFields($db);
    $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
}

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend')
{
    $massaction = '';
}


if (empty($reshook))
{
    // do action from GETPOST ... 
}


/*
 * View
 */

llxHeader('', $langs->trans('NetworkList'), '', '');

//$type = GETPOST('type');
//if (empty($user->rights->network->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$keys = array_keys($object->fields);
$fieldList = 't.'.implode(', t.', $keys);
if (!empty($object->isextrafieldmanaged))
{
    $keys = array_keys($extralabels);
    if(!empty($keys)) {
        $fieldList .= ', et.' . implode(', et.', $keys);
    }
}

$sql = 'SELECT '.$fieldList;

// Add fields from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$sql.= ' FROM '.MAIN_DB_PREFIX.'network t ';

if (!empty($object->isextrafieldmanaged))
{
    $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'network_extrafields et ON (et.fk_object = t.rowid)';
}

$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('Network', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;

// Add where from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_network', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;
//echo $sql;exit;
$r = new Listview($db, 'network');
echo $r->render($sql, array(
        'view_type' => 'list' // default = [list], [raw], [chart]
        ,'allow-fields-select' => true
        ,'limit'=>array(
        'nbLine' => $nbLine
    )
    ,'list' => array(
        'title' => $langs->trans('NetworkList')
        ,'image' => 'title_generic.png'
        ,'picto_precedent' => '<'
        ,'picto_suivant' => '>'
        ,'noheader' => 0
        ,'messageNothing' => $langs->trans('NoNetwork')
        ,'picto_search' => img_picto('', 'search.png', '', 0)
//        ,'massactions'=>array(
//            'yourmassactioncode'  => $langs->trans('YourMassActionLabel')
//        )
    )
    ,'subQuery' => array()
    ,'link' => array()
    ,'type' => array(
//        'date_creation' => 'date' // [datetime], [hour], [money], [number], [integer]
//        ,'tms' => 'date'
    )
    ,'search' => array(
//        'sourcetype' => array('search_type' => true, 'table' => 't', 'field' => 'ref')
        'link' => array('search_type' => true, 'table' => array('t'), 'field' => array('link')) // input text de recherche sur plusieurs champs
//        ,'targettype' => array('search_type' => Network::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
    )
    ,'translate' => array()
    ,'hide' => array(
        'rowid' // important : rowid doit exister dans la query sql pour les checkbox de massaction
    )
    ,'title'=>array(
        'sourcetype' => $langs->trans('Source')
        ,'link' => $langs->trans('Label')
        ,'targettype' => $langs->trans('Target')

    )
    ,'eval'=>array(
        'sourcetype' => '_getObjectNomUrl(\'@val@\', \'@fk_source@\')'
        ,'targettype' => '_getObjectNomUrl(\'@val@\', \'@fk_target@\')'
//		,'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
    )
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');
$db->close();

/**
 * TODO remove if unused
 */
function _getObjectNomUrl($className, $fk_object)
{
    global $db;

    $o = new $className($db);
    $o->fetch($fk_object);

    $url = '';
    if(method_exists($o, 'getNomUrl'))
    {
        if ($o->element === 'user') $url = $o->getNomUrl(1, '', 0, 0, 50, 1);
        else $url = $o->getNomUrl(1);

        if (!empty($url))
        {
            $url = preg_replace('/class="/', 'class="network_element '.$o->element.' '.$addclass.' ', $url, 1);
        }
//        $obj->url = $url;
    }
    elseif($o->element == 'usergroup')
    {
        $url = '<a class="network_element '.$o->element.'" href="'.dol_buildpath('/user/group/card.php?id='.$o->id, 1).'">'.$o->name.'</a>';
//        $obj->url = $url;
    }

    return $url;
}

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
    global $db;

    $u = new User($db);
    if ($u->fetch($fk_user) > 0)
    {
        return $u->getNomUrl(1);
    }

    return '';
}