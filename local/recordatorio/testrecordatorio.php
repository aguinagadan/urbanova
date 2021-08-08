<?php

global $DB;

$recordatorios = $DB->get_records_sql("SELECT * FROM {mdl_urbanova_recordatorio}");

var_dump($recordatorios);
exit;

$supportuser = core_user::get_support_user();
$subject = 'URBANOVA - Mensaje de seguimiento de curso';

function enviarRecordatorios($courseId) {
	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	//ver que trae
	var_dump($context);
	exit;

	foreach($users as $user) {
		$foruser = core_user::get_user($user->id);
		$message = 'Por favor no se olvide de completar su curso pendiente';
		email_to_user($foruser, $supportuser, $subject, $message);
	}
}

function obtenerDiasDiferenciaHoy($fecha) {
	//terminar
	return 3;
}


foreach ($recordatorios as $recordatorio) {
	$day = date('D');

	if($day == 'Mon' &&  $recordatorio->lunes == 1) {
		echo '1';
		exit;
		enviarRecordatorios($recordatorio->courseid);
		continue;
	}
	if($day == 'Fri' &&  $recordatorio->viernes == 1) {
		echo '2';
		exit;
		enviarRecordatorios($recordatorio->courseid);
		continue;
	}
	if($recordatorio->tresdias == 1) {
		echo '3';
		exit;
		$recordatorio = $DB->get_record_sql("SELECT * FROM {mdl_course} WHERE id = ?", $recordatorio->courseid);
		if(obtenerDiasDiferenciaHoy($recordatorio->dateend) == 3) {
			enviarRecordatorios($recordatorio->courseid);
			continue;
		}
	}
	if($day == 'Fri' &&  $recordatorio->undia == 1) {
		echo '4';
		exit;
		$recordatorio = $DB->get_record_sql("SELECT * FROM {mdl_course} WHERE id = ?", $recordatorio->courseid);
		if(obtenerDiasDiferenciaHoy($recordatorio->dateend) == 1) {
			enviarRecordatorios($recordatorio->courseid);
			continue;
		}
	}
}