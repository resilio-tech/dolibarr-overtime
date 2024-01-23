<?php

$object->date_start = $date_start;
$object->date_end = $date_end;
$object->hours = $hours;
$object->reason = $reason;

$db->begin();

$object->status = 0;
$result = $object->update($user);

if ($result) {
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
