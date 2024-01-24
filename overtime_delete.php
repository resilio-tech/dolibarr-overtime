<?php

$db->begin();

$result = $object->delete($user);

if ($result) {
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
