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
 * @fileoverview
 *
 * This script migrates the Network module from v1.0 to v3.0.
 *
 * Use http parameter dryrun=1 if you want to simulate the migration
 * before actually affecting the database.
 */

$res = @include '../../main.inc.php'; // From htdocs directory
if (! $res) {
    $res = @include '../../../main.inc.php'; // From "custom" directory
}

require_once '../class/network.class.php';
$langs->loadLangs(array('network@network'));
/*
class DB extends mysqli
{
    function escape($txt) {
        return str_replace("'", "\\'", str_replace('"', '\\"', $txt));
    }
    function num_rows($res) {
        return $res->num_rows;
    }
    function fetch_object($res) {
        return $res->fetch_object();
    }
}
$db = new DB('localhost', 'admin', 'admin', 'ecri_dolibarr');
define('MAIN_DB_PREFIX', 'llx_');
*/

$tableNameToType = array(
    'user'      => 'User',
    'usergroup' => 'UserGroup',
    'societe'   => 'Societe',
    'socpeople' => 'Contact',
    'propal'    => 'Propal',
    'commande'  => 'Commande',
    'facture'   => 'Facture',
    'product'   => 'Product',
    'projet'    => 'Project',
    'netmsg'    => 'Rel',
);

/**
 * Appends [table, rowid] to $TForeignKey for every matching row found using the same rules as in the old hashtag.php
 *
 * @param DoliDB $db
 * @param string $table
 * @param string $keyField
 * @param string $matchType either '=', 'LIKE%' (match start) or '%LIKE%' (match anywhere).
 * @param string $tag
 * @param array $TForeignKey
 * @param string $sql  Optional: provide your own sql query if too complex to be built using just the other args.
 * @return int  How many rows were found that match the criteria
 */
function fetchMatchingRowids($db, $table, $keyField, $matchType, $tag, &$TForeignKey, $sql='') {
    if (empty($sql)) $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . $table;
    switch ($matchType) {
        case '=':
            $sql .= ' WHERE ' . $keyField . ' = \'' .$db->escape($tag).'\'';
            break;
        case 'LIKE':
            $sql .= ' WHERE ' . $keyField . ' LIKE \'' .$db->escape($tag).'\'';
            break;
        case 'LIKE%':
            $sql .= ' WHERE ' . $keyField . ' LIKE \'' .$db->escape($tag).'%\'';
            break;
        case '%LIKE':
            $sql .= ' WHERE ' . $keyField . ' LIKE \'%' .$db->escape($tag).'\'';
            break;
        case '%LIKE%':
            $sql .= ' WHERE ' . $keyField . ' LIKE \'%' .$db->escape($tag).'%\'';
            break;
    }
    $res = $db->query($sql);
    if (!$res) { return 0; }
    $count = $db->num_rows($res);
    if (!$count) { return 0; }
    for ($i = 0; $i < $count; $i++) {
        $obj = $db->fetch_object($res);
        $TForeignKey[] = array($table, $obj->rowid);
    }
    return $count;
}

/**
 * @param DoliDB $db
 * @param string $type
 * @param string $tag
 * @return array
 */
function findMatching($db, $type, $tag) {
    $TForeignKey = array();
    if ($type == '@') {
        fetchMatchingRowids($db, 'user'     , 'login'      , '='    , $tag, $TForeignKey);
        fetchMatchingRowids($db, 'usergroup', 'nom'        , 'LIKE%', $tag, $TForeignKey);

        @list($code, $nom) = explode('|', $tag);

        $skip_company_search = false;
        if ($nom) {
            @list($nom, $discarded) = explode('_', $nom);
            $skip_company_search = true;
        } else {
            @list($code, $nom) = explode('_', $tag);
        }

        if (!$skip_company_search) {
            $found = fetchMatchingRowids($db, 'societe'  , 'code_client', '='    , $code, $TForeignKey);
            if (!$found) $found = fetchMatchingRowids($db, 'societe'  , 'nom'        , 'LIKE' , $tag, $TForeignKey);
            if (!$found) $found = fetchMatchingRowids($db, 'societe'  , 'nom'        , 'LIKE%', $code, $TForeignKey);
        }

        $sql = "SELECT p.rowid FROM ".MAIN_DB_PREFIX."socpeople p"
             . " LEFT JOIN ".MAIN_DB_PREFIX."societe s ON (p.fk_soc=s.rowid)"
             . " WHERE (s.code_client = '".$db->escape($code)."' OR s.nom LIKE '".$db->escape($code)."' ) AND p.lastname='".$db->escape($nom)."'";
        fetchMatchingRowids($db, 'socpeople', '', '', $tag, $TForeignKey, $sql);

    } elseif ($type == '#') {
        fetchMatchingRowids($db, 'propal' , 'ref'      , '='    , $tag, $TForeignKey);
        fetchMatchingRowids($db, 'facture', 'facnumber', '='    , $tag, $TForeignKey);
        fetchMatchingRowids($db, 'product', 'ref'      , '='    , $tag, $TForeignKey);
        fetchMatchingRowids($db, 'projet' , 'ref'      , 'LIKE%', $tag, $TForeignKey);
    } elseif ($type == ':') {
        fetchMatchingRowids($db, 'netmsg' , 'comment', '%LIKE%', ':' . $tag, $TForeignKey);
    }
    return $TForeignKey;
}

/**
 * Checks that the source object of a v1.0 table row exists in database.
 * @param $db
 * @param int $rowid
 * @param string $type
 * @return int  -1: database error, 0: no row, 1: row found
 */
function check_source_exists($db, $rowid, $type)
{
    $table_element = $type;
    if ($type == 'contact') $table_element = 'socpeople';
    $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . $table_element . ' WHERE rowid = ' . $rowid;
    $resql = $db->query($sql);
    if (!$resql) return -1;
    return $db->num_rows($resql);
}

/**
 * Migrates data from llx_netmsg (v1.0) to llx_network (v3.0).
 * @param DoliDB $db
 * @param bool $dryRun  If true, print the SQL queries instead of executing them.
 */
function migrate1To3($db, $dryRun = true)
{
    global $langs, $user, $tableNameToType;
    // qualificateur de relation suivi de '#', '@' ou ':', suivi du code du tag, suivi de n’importe quoi.
    $regex_tag = '/([#@:])([\\w-\|]+)/';
    $sql = 'SELECT rowid, fk_object, fk_user, type_object, comment FROM '.MAIN_DB_PREFIX.'netmsg';
    $res = $db->query($sql);
    $TNetworkToImport = array();
    $TNetworkNotImported = array();
    $TNetworkNotImportedReasons = array();
    if ($res)
    {
        $count = $db->num_rows($res);
        for ($i = 0; $i < $count; $i++)
        {
            $obj = $db->fetch_object($res);
            $networkRow = array(
                'import_key' => $obj->rowid,
                'fk_source' => $obj->fk_object,
                'sourcetype' => ucfirst($obj->type_object),
                'fk_target' => null,
                'targettype' => null,
                'link' => $obj->comment
            );
            $matches = null;
            preg_match_all($regex_tag, $obj->comment, $matches);
            //preg_match_all($regex_tag, 'toto @tata @ttu :tau', $matches);
            $matchCount = count($matches[0]);
            if (!$matchCount) {
                // aucun tag --> ajouter à la liste des erreurs
                $TNetworkNotImported[] = $obj;
                $TNetworkNotImportedReasons[] = 'NoTagFound';
                continue;
            }
            $source_exists = check_source_exists($db, $obj->fk_object, $obj->type_object);
            if ($source_exists == -1) {
                $TNetworkNotImported[] = $obj;
                $TNetworkNotImportedReasons[] = 'DatabaseError';
                dol_print_error($db);
                continue;
            } elseif ($source_exists == 0) {
                $TNetworkNotImported[] = $obj;
                $TNetworkNotImportedReasons[] = 'SourceNotFound';
                continue;
            }

            for ($matchN = 0; $matchN < $matchCount; $matchN++) {
                $type = $matches[1][$matchN];
                $tag = $matches[2][$matchN];
                if (is_string($tag)) {} else {echo "TAG: " . $tag . "\n\n"; exit;}
                $TForeignKey = findMatching($db, $type, $tag);
                if (count($TForeignKey) == 0) {
                    // tag found but nothing matches in the database
                    $TNetworkNotImported[] = $obj;
                    $TNetworkNotImportedReasons[] = 'NoMatchForTag';
                } elseif (count($TForeignKey) == 1) {
                    // tag found, exactly one match in the database
                    $networkRow['targettype'] = $tableNameToType[$TForeignKey[0][0]];
                    $networkRow['fk_target'] = $TForeignKey[0][1];
                    $networkRow['link'] = trim(preg_replace($regex_tag, '', $obj->comment));
                    $TNetworkToImport[] = $networkRow;
                } else {
                    // tag found but ambiguous (more than one match)
                    $TNetworkNotImported[] = $obj;
                    $TNetworkNotImportedReasons[] = 'TagAmbiguous';
                }
            }
        }
    }

    $insertValues = array();
    foreach ($TNetworkToImport as $networkRow) {
        $insertValues[] = sprintf(
            '("%s", %d, %d,"%s",%d,"%s","%s", %d)',
            $db->idate(dol_now()),
            $user->id,
            $networkRow['fk_source'],
            $db->escape($networkRow['sourcetype']),
            $networkRow['fk_target'],
            $db->escape($networkRow['targettype']),
            $db->escape($networkRow['link']),
            $networkRow['import_key']);
    }
    $insertValues = join(','."\n", $insertValues);

    $insertQuery = 'INSERT INTO '.MAIN_DB_PREFIX.'network (date_creation, fk_user, fk_source, sourcetype, fk_target, targettype, link, import_key) VALUES ' . "\n"
                 . $insertValues;
    if ($dryRun) {
        echo "<h2>" . $langs->trans('SimulationFinished', count($TNetworkToImport)) . "</h2>";
        echo '<textarea style="width: 100%; height: 40vh; font-size: 80%;">';
        echo $insertQuery;
        echo '</textarea>';
    } else {
        $db->begin();
        $res = $db->query($insertQuery);
        if (empty($res)) {
            $db->rollback();
        } else {
            $db->commit();
        }
        echo "<h2>" . $langs->trans("MigrationFinished", count($TNetworkToImport)) . "</h2>";
    }
    echo "<br><br>";
    echo "<h2>" . $langs->trans("RowsNotMigrated", count($TNetworkNotImported)) . "</h2><table class=\"border\" width='100%'>";
    echo '<tr><th>ID</th><th>'.$langs->trans('Source').'</th><th>'.$langs->trans('Comment').'</th><th>'.$langs->trans('Reason').'</th>';

    for ($i = 0; $i < count($TNetworkNotImported); $i++) {
        $obj = $TNetworkNotImported[$i];
        $reason = $TNetworkNotImportedReasons[$i];
        $link = dol_buildpath($obj->type_object . '/card.php?id=' . $obj->fk_object, 1);
        printf("<tr><td>%d </td><td><a href=\"%s\">%s %d</a></td><td> '%s' </td><td>%s</td><td></td></tr>\n", $obj->rowid, $link, $obj->type_object, $obj->fk_object, $obj->comment, $langs->trans($reason));
    }
    echo '</table>';
}
llxHeader();
$dryRun = GETPOST('dryrun', 'int');
$dryRun = (int) $dryRun;
migrate1To3($db, $dryRun);
llxFooter();
