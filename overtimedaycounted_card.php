<?php
/* Copyright (C) 2026 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       overtimedaycounted_card.php
 *		\ingroup    overtime
 *		\brief      Page to view and correct the yearly reserve of an employee
 */

// Load Dolibarr environment
$res = 0;
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

dol_include_once('/overtime/class/overtimedaycounted.class.php');

$langs->loadLangs(array("overtime@overtime", "other"));

$id = GETPOSTINT('id');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$object = new OvertimeDayCounted($db);
$extrafields = new ExtraFields($db);
$extrafields->fetch_name_optionals_label($object->table_element);

if ($id > 0) {
	$object->fetch($id);
}

$permissiontoread = $user->hasRight('overtime', 'overtimedaycounted', 'read');
$permissiontoadd = $user->hasRight('overtime', 'overtimedaycounted', 'write');
$permissiontodelete = $user->hasRight('overtime', 'overtimedaycounted', 'delete');

if (!isModEnabled('overtime') || $user->socid > 0 || !$permissiontoread || !($object->id > 0)) {
	accessforbidden();
}


/*
 * Actions
 */

$backurlforlist = dol_buildpath('/overtime/overtimedaycounted_list.php', 1);
if (empty($backtopage)) {
	$backtopage = dol_buildpath('/overtime/overtimedaycounted_card.php', 1).'?id='.$object->id;
}

include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';


/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans('OvertimeDayCounted'));

if ($action == 'edit') {
	print load_fiche_titre($langs->trans('OvertimeDayCounted'), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	print dol_get_fiche_head();
	print '<table class="border centpercent tableforfieldedit">'."\n";
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';
	print '</table>';
	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();
	print '</form>';
} else {
	if ($action == 'delete') {
		print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('Delete'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}

	print load_fiche_titre($langs->trans('OvertimeDayCounted'), '<a href="'.$backurlforlist.'">'.$langs->trans("BackToList").'</a>', 'object_'.$object->picto);

	print dol_get_fiche_head();
	print '<table class="border centpercent tableforfield">'."\n";
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';
	print '</table>';
	print dol_get_fiche_end();

	print '<div class="tabsAction">';
	print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);
	print dolGetButtonAction('', $langs->trans('Delete'), 'delete', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete);
	print '</div>';
}

llxFooter();
$db->close();
