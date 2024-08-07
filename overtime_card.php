<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2024 SuperAdmin
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
 *   	\file       overtime_card.php
 *		\ingroup    overtime
 *		\brief      Page to create/edit/view overtime
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("MAIN_SECURITY_FORCECSP"))   define('MAIN_SECURITY_FORCECSP', 'none');	// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
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
// Try main.inc.php using relative path
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/overtime/class/overtime.class.php');
dol_include_once('/overtime/lib/overtime_overtime.lib.php');
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

// Load translation files required by the page
$langs->loadLangs(array("overtime@overtime", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$date_start = dol_mktime(0, 0, 0, GETPOST('date_debutmonth', 'int'), GETPOST('date_debutday', 'int'), GETPOST('date_debutyear', 'int'));
$date_end = dol_mktime(0, 0, 0, GETPOST('date_finmonth', 'int'), GETPOST('date_finday', 'int'), GETPOST('date_finyear', 'int'));
$hours = GETPOST('hours', 'int');
$reason = GETPOST('reason', 'alpha');
$ref = GETPOST('ref', 'alpha');
$user_id = GETPOST('user_id', 'int');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');					// if not set, a default page will be used
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');	// if not set, $backtopage will be used
$backtopagejsfields = GETPOST('backtopagejsfields', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');
$linked = GETPOST('linked', 'alpha');

if (!empty($backtopagejsfields)) {
	$tmpbacktopagejsfields = explode(':', $backtopagejsfields);
	$dol_openinpopup = $tmpbacktopagejsfields[0];
}

// Initialize technical objects
$object = new Overtime($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->overtime->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('overtimecard', 'globalcard')); // Note that conf->hooks_modules contains array

$childids = $user->getAllChildIds(1);

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->hasRight('overtime', 'overtime', 'read');
	$permissiontoadd = $user->hasRight('overtime', 'overtime', 'write'); // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->hasRight('overtime', 'overtime', 'delete') || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->hasRight('overtime', 'overtime', 'write'); // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->hasRight('overtime', 'overtime', 'write'); // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = isset($object->status) && $object->status == $object::STATUS_DRAFT;
	$permissionnote = 1;
	$permissiondellink = 1;
}
$permissiontochangestatus = $user->hasRight('overtime', 'overtime', 'status');
$upload_dir = $conf->overtime->multidir_output[isset($object->entity) ? $object->entity : 1].'/overtime';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->module, $object, $object->table_element, $object->element, 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled("overtime")) {
	accessforbidden();
}
if (!$permissiontoread) {
	accessforbidden();
}


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/overtime/overtime_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/overtime/overtime_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	if (!empty($permissionedit) && empty($permissionadd)) {
		$permissionadd = $permissionedit;
	}

	if ($cancel) {
		if (!empty($backtopageforcancel)) {
			header("Location: $backtopageforcancel");
			exit();
		} elseif (!empty($backtopage)) {
			header("Location: $backtopage");
			exit();
		}
		$action = '';
	}

	if ($action == 'refund' && $id > 0) {
		$result = $object->setOvertimeRefunded();
		if ($result) {
			header("Location: overtime_list.php");
			exit;
		} else {
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
		}
	}

	if ($action == 'count' && $id > 0) {
		require_once './class/overtimehourskeep.class.php';

		$hourskeep = new OvertimeHoursKeep($db);
		$hourskeep->fetchByUser($object->fk_user);

		$hourskeep->hourskeeped += $object->hours;

		$result = $hourskeep->counted($user, $object);

		if ($result > 0) {
			header("Location: overtime_list.php");
			$object->setOvertimeCounted();
			exit;
		} else {
			$error++;
			if (!empty($hourskeep->errors)) {
				setEventMessages(null, $hourskeep->errors, 'errors');
			} else {
				setEventMessages($hourskeep->error, null, 'errors');
			}
		}
	}

	if ($action == 'confirm_delete' && $confirm == 'yes') {
		$db->begin();

		$result = $object->delete($user);

		if ($result > 0) {
			$db->commit();
			header("Location: overtime_list.php");
			exit;
		} else {
			$db->rollback();

			// Creation KO
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
		}
	}

	if ($action == 'update') {
		$object->date_start = $date_start;
		$object->date_end = $date_end;
		$object->hours = $hours;
		$object->reason = $reason;

		$db->begin();

		$object->status = 0;
		$result = $object->update($user);

		if ($result > 0) {
			$db->commit();

		} else {
			$db->rollback();

			// Creation KO
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
			$action = 'edit';
		}
	}

	if ($action == 'confirm_validate') {
		$db->begin();

		$object->status = Overtime::STATUS_VALIDATED;
		$result = $object->update($user);

		if ($result > 0) {
			$db->commit();
			header("Location: overtime_list.php");
			exit;
		} else {
			$db->rollback();

			// Creation KO
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
		}

	}

	if ($action == 'add') {
		$object->date_start = $date_start;
		$object->date_end = $date_end;
		$object->hours = $hours;
		$object->fk_user = $user_id;
		$object->status = Overtime::STATUS_DRAFT;
		$object->reason = $reason;

		$db->begin();

		$result = $object->create($user);

		require_once './class/overtimehourskeep.class.php';

		$overtimehourskeep = new OvertimeHoursKeep($db);
		$overtimehourskeep->fetchByUser($user_id);

		if ($result > 0) {
			$db->commit();

		} else {
			$db->rollback();

			// Creation KO
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
			$action = 'create';
		}
	}

	if ($action == 'link' && !empty($linked)) {
		$object->fk_payment = $linked;
		$result = $object->update($user);

		if ($result <= 0) {
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
		} else {
			$action = 'view';
		}
	}

	if ($action == 'createlink') {
		require_once DOL_DOCUMENT_ROOT.'/salaries/class/salary.class.php';

		$salary = new Salary($db);

		$salary->fk_user = $object->fk_user;
		$salary->datep = dol_now();
		$salary->datev = dol_now();
		$salary->datesp = $object->date_start;
		$salary->dateep = $object->date_end;
		$salary->amount = 0;
		$salary->label = 'Heures supplémentaires';
		$salary->type_payment = 0;

		$r = $salary->create($user);
		if ($r > 0) {
			$object->fk_payment = $r;
			$result = $object->update($user);
			if ($result > 0) {
				header("Location:/salaries/card.php?id=".$r);
				exit;
			} else {
				$error++;
				if (!empty($object->errors)) {
					setEventMessages(null, $object->errors, 'errors');
				} else {
					setEventMessages($object->error, null, 'errors');
				}
			}
		} else {
			$error++;
			if (!empty($salary->errors)) {
				setEventMessages(null, $salary->errors, 'errors');
			} else {
				setEventMessages($salary->error, null, 'errors');
			}
		}
	}

	if ($action == 'unlink') {
		$object->fk_payment = null;
		$result = $object->update($user);

		if ($result <= 0) {
			$error++;
			if (!empty($object->errors)) {
				setEventMessages(null, $object->errors, 'errors');
			} else {
				setEventMessages($object->error, null, 'errors');
			}
		} else {
			$action = 'view';
		}
	}
}




/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Overtime");
$help_url = '';
llxHeader('', $title, $help_url);

// Example : Adding jquery code
// print '<script type="text/javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden('NotEnoughPermissions', 0, 1);
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Overtime")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}
	if ($backtopagejsfields) {
		print '<input type="hidden" name="backtopagejsfields" value="'.$backtopagejsfields.'">';
	}
	if ($dol_openinpopup) {
		print '<input type="hidden" name="dol_openinpopup" value="'.$dol_openinpopup.'">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent">'."\n";

	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("User").'</td>';
	print '<td>';
	print $form->select_dolusers($user->id, 'user_id', 1, 0, 0, 'hierarchyme');
	print '</td>';
	print '</tr>';

	// Date start
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("DateStart").'</td>';
	print '<td>';
	print $form->selectDate($date_start ? $date_start : -1, 'date_debut', 0, 0, 0, '', 1, 1);
	print '</td>';
	print '</tr>';

	// Date end
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("DateEnd").'</td>';
	print '<td>';
	print $form->selectDate($date_end ? $date_end : -1, 'date_fin', 0, 0, 0, '', 1, 1);
	print '</td>';
	print '</tr>';

	// Hours
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Hours").'</td>';
	print '<td>';
	print '<input type="number" name="hours" value="'.$hours.'">';
	print '</td>';
	print '</tr>';

	// Reason
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Reason").'</td>';
	print '<td>';
	print '<input type="text" name="reason" value="'.$reason.'">';
	print '</td>';
	print '</tr>';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Overtime"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Date start
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("DateStart").'</td>';
	print '<td>';
	print $form->selectDate($object->date_start ? $object->date_start : -1, 'date_debut', 0, 0, 0, '', 1, 1);
	print '</td>';
	print '</tr>';

	// Date end
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("DateEnd").'</td>';
	print '<td>';
	print $form->selectDate($object->date_end ? $object->date_end : -1, 'date_fin', 0, 0, 0, '', 1, 1);
	print '</td>';
	print '</tr>';

	// Hours
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Hours").'</td>';
	print '<td>';
	print '<input type="number" name="hours" value="'.$object->hours.'">';
	print '</td>';
	print '</tr>';

	// Reason
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Reason").'</td>';
	print '<td>';
	print '<input type="text" name="reason" value="'.$object->reason.'">';
	print '</td>';
	print '</tr>';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$head = overtimePrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("Overtime"), -1, $object->picto, 0, '', '', 0, '', 0);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteOvertime'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionOvertime', $object->ref);
		/*if (isModEnabled('notification'))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('MYOBJECT_CLOSE', $object->socid, $object);
		}*/

		$formquestion = array();

		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/overtime/overtime_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
		// Ref customer
		$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string', '', 0, 1);
		$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, $usercancreate, 'string'.(getDolGlobalInt('THIRDPARTY_REF_INPUT_SIZE') ? ':'.getDolGlobalInt('THIRDPARTY_REF_INPUT_SIZE') : ''), '', null, null, '', 1);
		// Thirdparty
		$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1, 'customer');
		if (!getDolGlobalInt('MAIN_DISABLE_OTHER_LINK') && $object->thirdparty->id > 0) {
			$morehtmlref .= ' (<a href="'.DOL_URL_ROOT.'/commande/list.php?socid='.$object->thirdparty->id.'&search_societe='.urlencode($object->thirdparty->name).'">'.$langs->trans("OtherOrders").'</a>)';
		}
		// Project
		if (isModEnabled('project')) {
			$langs->load("projects");
			$morehtmlref .= '<br>';
			if ($permissiontoadd) {
				$morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
				if ($action != 'classify') {
					$morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&token='.newToken().'&id='.$object->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
				}
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
			} else {
				if (!empty($object->fk_project)) {
					$proj = new Project($db);
					$proj->fetch($object->fk_project);
					$morehtmlref .= $proj->getNomUrl(1);
					if ($proj->title) {
						$morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
					}
				}
			}
		}
	*/
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {

			// Back to draft
//			if ($object->status == $object::STATUS_VALIDATED) {
//				print dolGetButtonAction('', $langs->trans('SetToDraft'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
//			}

			if ($object->status == $object::STATUS_DRAFT || $permissiontochangestatus && $object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);
			}

			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction('', $langs->trans('Validate'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Status is Validated and user has permission to change status
			if ($permissiontochangestatus) {
				if ($object->status == $object::STATUS_VALIDATED) {
					// Reopen
					print dolGetButtonAction('', $langs->trans('Count_Overtime'), 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=count&token=' . newToken(), '', $permissiontochangestatus);
					print dolGetButtonAction('', $langs->trans('Refund_Overtime'), 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=refund&token=' . newToken(), '', $permissiontochangestatus);
				}
				if ($object->status == $object::STATUS_REMBOURSED && $action != 'link') {
					// Reopen
					print dolGetButtonAction('', $langs->trans('Link_Overtime'), 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=link&token=' . newToken(), '', $permissiontochangestatus);
					print dolGetButtonAction('', $langs->trans('Create_Link_Overtime'), 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=createlink&token=' . newToken(), '', $permissiontochangestatus);
				}
				if ($object->status == $object::STATUS_REMBOURSED && $action == 'link') {
					// print a form select for a payment
					print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=link">';
					print '<input type="hidden" name="token" value="'.newToken().'">';
					print '<input type="hidden" name="action" value="linked">';
					print '<input type="hidden" name="id" value="'.$object->id.'">';

					$array_payments = array();

					$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'salary ORDER BY datec DESC LIMIT 20';
					$resql = $db->query($sql);

					require_once DOL_DOCUMENT_ROOT.'/salaries/class/salary.class.php';

					while ($res = $db->fetch_object($resql)) {
						$line = new Salary($db);
						$line->fetch($res->rowid);

						$amount = floor($line->amount * 100) / 100;
						$value_date = (new DateTime($line->datev))->format('Y-m-d');
						$name = $line->label;
						preg_match('/\((.+)\)/i', $name, $reg);
						if (!empty($reg[1]) && $langs->trans($reg[1]) != $reg[1]) {
							$name = $langs->trans($reg[1]);
							$type = 'salary';
						} else {
							if ($name == '(payment_salary)') {
								$name = $langs->trans('SalaryPayment');
								$type = 'salary';
							} else {
								$name = dol_escape_htmltag($name);
							}
						}

						if (!empty($bank_links[1]['label'])) {
							$name .= ' - '.$bank_links[1]['label'];
						}

						$name = '<a href="'.DOL_URL_ROOT.'/compta/bank/line.php?rowid='.((int) $line->id).'&save_lastsearch_values=1" title="'.dol_escape_htmltag($name, 1).'" class="classfortooltip" target="_blank">'.img_picto('', $line->picto).' '.$line->id.' '.$name.'</a>';

						$array_payments[$line->id] = $name.' - '.$amount.' - '.$value_date;
					}
					print $form->selectMassAction('', $array_payments, 1, 'linked');
				}
			}

			/*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction('', $langs->trans('Disable'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction('', $langs->trans('Enable'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction('', $langs->trans('Cancel'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction('', $langs->trans('Re-Open'), 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/
			if ($object->status == $object::STATUS_DRAFT || $permissiontochangestatus && $object->status == $object::STATUS_VALIDATED) {
				// Delete
				$params = array();
				print dolGetButtonAction('', $langs->trans("Delete"), 'delete', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=delete&token=' . newToken(), 'delete', $permissiontodelete || $permissiontochangestatus, $params);
			}
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter">';
		print '<a name="builddoc"></a>'; // ancre

		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder allwidth" data-block="showLinkedObject" data-element="' . $object->element . '"  data-elementid="' . $object->id . '"   >';

		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans("Type") . '</td>';
		print '<td>' . $langs->trans("Ref") . '</td>';
		print '<td class="center"></td>';
		print '<td class="center">' . $langs->trans("Date") . '</td>';
		print '<td></td>';
		print '</tr>';

		// Fetch linked objects
		$linked_id = $object->fk_payment;
		if ($linked_id > 0) {
			require_once DOL_DOCUMENT_ROOT.'/salaries/class/salary.class.php';

			$linked_object = new Salary($db);
			$linked_object->fetch($linked_id);

			// Show linked object
			print '<tr>';
			print '<td>' . $langs->trans("Payment") . '</td>';
			print '<td>' . $linked_object->getNomUrl(1) . '</td>';
			print '<td class="center"></td>';
			print '<td class="center">' . dol_print_date($linked_object->datep, 'day') . '</td>';
			print '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=unlink&token='.newToken().'">'.img_picto('Unlink', 'object_delete').'</a></td>';
			print '</tr>';
		}
		print '</table>';

		print '</div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'overtime';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->overtime->dir_output;
	$trackid = 'overtime'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
