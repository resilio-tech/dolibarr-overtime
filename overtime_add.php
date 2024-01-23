<?php

$object->date_start = $date_start;
$object->date_end = $date_end;
$object->hours = $hours;
$object->fk_user = $user_id;
$object->status = 1;
$object->reason = $reason;

$db->begin();

$result = $object->create($user);

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
	$action = 'create';
}
