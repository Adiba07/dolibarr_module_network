<?php
/**
 * Copyright (C) @@YEAR@@ ATM Consulting <support@atm-consulting.fr>
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

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call or for session timeout on our module page
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}

require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/supplier_proposal/class/supplier_proposal.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

class Network extends SeedObject
{
	/** @var string $table_element Table name in SQL */
	public $table_element = 'network';

	/** @var string $element Name of the element (tip for better integration in Dolibarr: this value should be the reflection of the class name with ucfirst() function) */
	public $element = 'network';

	/** @var int $isextrafieldmanaged Enable the fictionalises of extrafields */
    public $isextrafieldmanaged = 0;

    /** @var int $ismultientitymanaged 0=No test on entity, 1=Test with field entity, 2=Test with link by societe */
    public $ismultientitymanaged = 1;

    /**
     *  'type' is the field format.
     *  'label' the translation key.
     *  'enabled' is a condition when the field must be managed.
     *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
     *  'noteditable' says if field is not editable (1 or 0)
     *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     *  'default' is a default value for creation (can still be replaced by the global setup of default values)
     *  'index' if we want an index in database.
     *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     *  'position' is the sort order of field.
     *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     *  'css' is the CSS style to use on field. For example: 'maxwidth200'
     *  'help' is a string visible as a tooltip on field
     *  'comment' is not used. You can store here any text of your choice. It is not used by application.
     *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
     */

    public $fields = array(

        'fk_user' => array(
            'type' => 'integer',
            'label' => 'User',
            'enabled' => 1,
            'visible' => 1,
            'notnull' => 1,
            'index' => 1,
            'position' => 10,
        ),

        'entity' => array(
            'type' => 'integer',
            'label' => 'Entity',
            'enabled' => 1,
            'visible' => 0,
            'default' => 1,
            'notnull' => 1,
            'index' => 1,
            'position' => 20
        ),

        'fk_source' => array(
            'type' => 'integer',
            'label' => 'SourceId',
            'enabled' => 1,
            'visible' => 0,
            'notnull' => 1,
            'index' => 1,
            'position' => 30
        ),

        'sourcetype' => array(
            'type' => 'varchar(80)',
            'label' => 'SourceType',
            'enabled' => 1,
            'visible' => 1,
            'notnull' => 1,
            'position' => 40,
        ),

        'link' => array(
            'type' => 'varchar(150)',
            'label' => 'Link',
            'enabled' => 1,
            'visible' => 1,
            'position' => 50,
        ),

        'fk_target' => array(
            'type' => 'integer',
            'label' => 'TargetId',
            'enabled' => 1,
            'visible' => 0,
            'notnull' => 1,
            'index' => 1,
            'position' => 60
        ),

        'targettype' => array(
            'type' => 'varchar(80)',
            'label' => 'TargetType',
            'enabled' => 1,
            'visible' => 1,
            'notnull' => 1,
            'position' => 70,
        ),

//        'fk_user_valid' =>array(
//            'type' => 'integer',
//            'label' => 'UserValidation',
//            'enabled' => 1,
//            'visible' => -1,
//            'position' => 512
//        ),

        'import_key' => array(
            'type' => 'varchar(14)',
            'label' => 'ImportId',
            'enabled' => 1,
            'visible' => -2,
            'notnull' => -1,
            'index' => 0,
            'position' => 1000
        ),

    );

    /** @var int $fk_user User id */
	public $fk_user;

    /** @var int $entity Object entity */
	public $entity;

	/** @var int $fk_source Object source */
	public $fk_source;

	/** @var string $sourcetype Object source type */
	public $sourcetype;

    /** @var string $link Label link */
    public $link;

	/** @var int $fk_source Object source */
	public $fk_target;

	/** @var string $sourcetype Object source type */
	public $targettype;


    /**
     * Network constructor.
     * @param DoliDB    $db    Database connector
     */
    public function __construct($db)
    {
		global $conf;

        parent::__construct($db);

		$this->init();

		$this->entity = $conf->entity;
    }

    /**
     * @param User $user User object
     * @return int
     */
    public function save($user)
    {
        return $this->create($user);
    }

    /**
     * @param User $user User object
     * @return int
     */
    public function delete(User &$user)
    {
        unset($this->fk_element); // avoid conflict with standard Dolibarr comportment
        return parent::delete($user);
    }

    /**
     * @param int    $withpicto     Add picto into link
     * @param string $moreparams    Add more parameters in the URL
     * @return string
     */
    public function getNomUrl($withpicto = 0, $moreparams = '')
    {
		global $langs;

        $result='';
        $label = '<u>' . $langs->trans("ShowNetwork") . '</u>';
        if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;

        $linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $link = '<a href="'.dol_buildpath('/network/card.php', 1).'?id='.$this->id.urlencode($moreparams).$linkclose;

        $linkend='</a>';

        $picto='generic';
//        $picto='network@network';

        if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';

        $result.=$link.$this->ref.$linkend;

        return $result;
    }

    /**
     * @param int       $id             Identifiant
     * @param null      $ref            Ref
     * @param int       $withpicto      Add picto into link
     * @param string    $moreparams     Add more parameters in the URL
     * @return string
     */
    public static function getStaticNomUrl($id, $ref = null, $withpicto = 0, $moreparams = '')
    {
		global $db;

		$object = new Network($db);
		$object->fetch($id, false, $ref);

		return $object->getNomUrl($withpicto, $moreparams);
    }

    public function getSearchResult($queryString)
    {
        global $conf, $langs;

        $TRes = array();

        // @ = user / usergroup / societe / contact
        // # = propal / commande / facture ...
        // si aucun des 2, alors de partout
        // TODO pour chaque ajout d'élément ici, il faudra inclure la class tout en haut de ce fichier et ajouter la clé de traduction issue de la concaténation de la chaine "NetworkItemTitle" + type de l'élément
        $TTableSearchAvailable = array(
            '@' => array(
                'user' => array(
                    'select' => empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION) ? 'CONCAT(lastname, \' \', firstname) AS label' : 'CONCAT(firstname, lastname) AS label'
                    ,'fields' => array('lastname', 'firstname')
                    ,'use_natural_search' => true
                    ,'entity' => true
                    ,'type' => 'User' // Représente le nom de la class
                )
                ,'usergroup' => array(
                    'select' => 'nom AS label'
                    ,'fields' => array('nom')
                    ,'use_natural_search' => true
                    ,'entity' => true
                    ,'type' => 'UserGroup' // Représente le nom de la class
                )
                ,'societe' => array(
                    'select' => 'CONCAT(code_client, \' \', nom) AS label'
                    ,'fields' => array('code_client', 'nom')
                    ,'use_natural_search' => true
                    ,'entity' => true
                    ,'multicompany_element' => 'societe'
                    ,'type' => 'Societe' // Représente le nom de la class
                )
                ,'socpeople' => array(
                    'select' => empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION) ? 'CONCAT(lastname, \' \', firstname) AS label' : 'CONCAT(firstname, lastname) AS label'
                    ,'fields' => array('lastname', 'firstname')
                    ,'use_natural_search' => true
                    ,'entity' => true
                    ,'type' => 'Contact' // Représente le nom de la class
                )
            )
            ,'#' => array(
                'propal' => array(
                    'select' => 'ref AS label'
                    ,'fields' => array('ref')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'multicompany_element' => 'propal'
                    ,'type' => 'Propal' // Représente le nom de la class
                )
                ,'commande' =>  array(
                    'select' => 'ref AS label'
                    ,'fields' => array('ref')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'multicompany_element' => 'commande'
                    ,'type' => 'Commande' // Représente le nom de la class
                )
                ,'facture' =>  array(
                    'select' => ((int) DOL_VERSION < 9.0) ? 'facnumber AS label' : 'ref AS label'
                    ,'fields' => ((int) DOL_VERSION < 9.0) ? array('facnumber') : array('ref')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'multicompany_element' => 'facture'
                    ,'type' => 'Facture' // Représente le nom de la class
                )
                ,'supplier_proposal' =>  array(
                    'select' => 'ref AS label'
                    ,'fields' => array('ref')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'multicompany_element' => 'supplier_proposal'
                    ,'type' => 'SupplierProposal' // Représente le nom de la class
                )
                ,'commande_fournisseur' =>  array(
                    'select' => 'ref AS label'
                    ,'fields' => array('ref')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'type' => 'CommandeFournisseur' // Représente le nom de la class
                )
                ,'facture_fourn' =>  array(
                    'select' => 'ref AS label'
                    ,'fields' => array('ref')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'type' => 'FactureFournisseur' // Représente le nom de la class
                )

                ,'projet' =>  array(
                    'select' => 'CONCAT(ref, \' \', title) AS label'
                    ,'fields' => array('ref', 'title')
                    ,'use_natural_search' => false
                    ,'entity' => true
                    ,'multicompany_element' => 'project'
                    ,'type' => 'Project' // Représente le nom de la class
                )
            )
        );

        if ($queryString[0] === '@')
        {
            $queryString = substr($queryString, 1);
            $TTableSearch = $TTableSearchAvailable['@'];
        }
        elseif ($queryString[0] === '#')
        {
            $queryString = substr($queryString, 1);
            $TTableSearch = $TTableSearchAvailable['#'];
        }
        else $TTableSearch = array_merge($TTableSearchAvailable['@'], $TTableSearchAvailable['#']);

        $sql = '';
        foreach ($TTableSearch as $table => $Tab)
        {
            if (!empty($sql)) $sql.= ' UNION ';
            $sql.= '( SELECT rowid, '.$Tab['select'].', \''.$Tab['type'].'\' AS type FROM '.MAIN_DB_PREFIX.$table;
            if ($Tab['entity'])
            {
                if (!empty($conf->multicompany->enabled) && !empty($Tab['multicompany_element'])) $sql.= ' WHERE entity IN ('.getEntity($Tab['multicompany_element']).')';
                else $sql.= ' WHERE entity = '.$conf->entity;
            }
            else $sql.= ' WHERE 1';
            if (!empty($Tab['use_natural_search'])) $sql.= natural_search($Tab['fields'], $queryString);
            else $sql.= ' AND '.$Tab['fields'].' = \''.$this->db->escape($queryString).'\'';
            $sql.= ' LIMIT 10 )';
        }

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $last_title = null;
            while ($obj = $this->db->fetch_object($resql))
            {
                if ($last_title === null || $last_title != 'NetworkItemTitle'.$obj->type)
                {
                    $last_title = 'NetworkItemTitle'.$obj->type;
                    $TRes[] = array(
                        'label' => '<b class="network-ui-disabled" >'.$langs->trans($last_title).'</b>'
                        ,'disabled' => 1
                    );
                }

                $TRes[] = array(
                    'key' => $obj->rowid.'-'.$obj->type
                    ,'value' => trim($obj->label)
                    ,'label' => trim($obj->label)
                );
            }
        }
        else
        {
            dol_print_error($this->db);
        }
        // Faire les query qui vont bien
//        array_push($outarray, array('key'=>$obj->rowid, 'value'=>$label, 'label'=>$label));

        // Renvoyer le tableau de résultat

        return $TRes;
    }

    /**
     * @param DoliDB    $db             Database connector
     * @param int       $fk_source      Id of source
     * @param string    $sourcetype     Element type of source
     * @param int       $start          Timestamp
     * @param int       $limit          Limit number of return
     * @return array
     */
    public static function getStaticCommentsBySource($db, $fk_source, $sourcetype, $start = 0, $limit = 10)
    {
        return self::getStaticCommentsByElement($db, $fk_source, $sourcetype, 'source', $start, $limit);
    }

    /**
     * @param DoliDB    $db             Database connector
     * @param int       $fk_target      Id of source
     * @param string    $targettype     Element type of source
     * @param int       $start          Timestamp
     * @param int       $limit          Limit number of return
     * @return array
     */
    public static function getStaticCommentsByTarget($db, $fk_target, $targettype, $start = 0, $limit = 10)
    {
        return self::getStaticCommentsByElement($db, $fk_target, $targettype, 'target', $start, $limit);
    }

    /**
     * @param DoliDB    $db             Database connector
     * @param int       $fk_element     Id of source or target
     * @param string    $elementtype    Element type of source or target
     * @param string    $type           Type can be 'source' or anything else for 'target'
     * @param int       $start          Timestamp
     * @param int       $limit          Limit number of return
     * @return array
     */
    private static function getStaticCommentsByElement($db, $fk_element, $elementtype, $type = 'source', $start = 0, $limit = 10)
    {
        global $langs;

        $TCom = array();

        $self = new self($db);
        $sql = 'SELECT '.$self->getFieldList();
        $sql.= ' FROM '.MAIN_DB_PREFIX.$self->table_element;
        if ($type === 'source')
        {
            $sql.= ' WHERE fk_source = '.$fk_element;
            $sql.= ' AND sourcetype = \''.$db->escape($elementtype).'\'';
        }
        else
        {
            $sql.= ' WHERE fk_target = '.$fk_element;
            $sql.= ' AND targettype = \''.$db->escape($elementtype).'\'';
        }

        $sql.= ' ORDER BY date_creation DESC';
        $sql.= ' LIMIT '.$start.', '.($limit + 1);

        $resql = $db->query($sql);
        if ($resql)
        {
            $TUserTmp = array();
            $addclass = 'badge network_badge';
            $i = 0;
            while ($obj = $db->fetch_object($resql))
            {
                if ($type === 'source')
                {
                    $o = new $obj->targettype($db);
                    $res = $o->fetch($obj->fk_target);
                }
                else
                {
                    $o = new $obj->sourcetype($db);
                    $res = $o->fetch($obj->fk_source);
                }

                if ($res > 0)
                {
                    if (empty($obj->link)) $obj->link = '&ndash;';

                    if(method_exists($o, 'getNomUrl'))
                    {
                        if ($o->element === 'user') $url = $o->getNomUrl(1, '', 0, 0, 50, 1);
                        else $url = $o->getNomUrl(1);

                        if (!empty($url))
                        {
                            $url = preg_replace('/class="/', 'class="network_element '.$o->element.' '.$addclass.' ', $url, 1);
                        }
                        $obj->url = $url;
                    }
                    elseif($o->element == 'usergroup')
                    {
                        $url = '<a class="network_element '.$o->element.' '.$addclass.'" href="'.dol_buildpath('/user/group/card.php?id='.$o->id, 1).'">'.$o->name.'</a>';
                        $obj->url = $url;
                    }

                    if (empty($TUserTmp[$obj->fk_user]))
                    {
                        $TUserTmp[$obj->fk_user]=new User($db);
                        $TUserTmp[$obj->fk_user]->fetch($obj->fk_user);
                    }
                    if ($TUserTmp[$obj->fk_user]->id > 0) $obj->author = $TUserTmp[$obj->fk_user]->getFullName($langs);
                    else $obj->author = '';

                    $obj->date = dol_print_date($obj->date_creation, 'dayhourtextshort');
                }

                $obj->date_creation = $db->jdate($obj->date_creation); // Pour conserver la notation en timestamp
                $TCom[$i] = $obj;
                $i++;
            }
        }
        else
        {
            dol_print_error($db);
        }

        return $TCom;
    }
}
