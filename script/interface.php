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

require __DIR__.'/../config.php';
dol_include_once('/network/class/network.class.php');

$langs->load('network@network');

$action = GETPOST('action');
$fk_source = GETPOST('fk_source', 'int');
$sourcetype = GETPOST('sourcetype'); // Must be a class name

$object = new Network($db);

switch ($action) {
    case 'search':
        __out($object->getSearchResult(GETPOST('network_target')));
        break;

    case 'getComments':
        $TComment = array_merge(
            Network::getStaticCommentsBySource($db, $fk_source, $sourcetype, (int) GETPOST('start'), (int) GETPOST('limit')),
            Network::getStaticCommentsByTarget($db, $fk_source, $sourcetype, (int) GETPOST('start'), (int) GETPOST('limit'))
        );

        usort($TComment, function ($a, $b) {
            return $b->date_creation - $a->date_creation;
        });

        __out($TComment);
        break;

    case 'addComment':
        $link = GETPOST('link');
        $target = GETPOST('target');
        $target = explode('-', $target);

        $fk_target = $target[0];
        $targettype = $target[1];

        if (empty($fk_target) || empty($targettype)) return __out(array('error' => $langs->transnoentitiesnoconv('network_error_empty_target')));
        if (empty($fk_source) || empty($sourcetype)) return __out(array('error' => $langs->transnoentitiesnoconv('network_error_empty_source')));

        $object->fk_user = $user->id;

        $object->fk_source = $fk_source;
        $object->sourcetype = $sourcetype;
        $object->link = dol_sanitizeFileName($link);
        $object->fk_target = $fk_target;
        $object->targettype = $targettype;

        if ($object->create($user) > 0) __out(array('success' => $langs->transnoentitiesnoconv('network_success_create_comment')));
        else __out(array('error' => $langs->transnoentitiesnoconv('network_error_create_comment', $object->db->lasterror())));

        break;
    case 'deleteComment':
        $object->fetch(GETPOST('id'));
        if ($object->delete($user) > 0) __out(array('success' => $langs->transnoentitiesnoconv('network_success_delete_comment')));
        else __out(array('error' => $langs->transnoentitiesnoconv('network_error_delete_comment', $object->db->lasterror())));

        break;
}
