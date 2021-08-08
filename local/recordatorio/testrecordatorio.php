<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $PAGE;

$context = context_system::instance();
$PAGE->set_context($context);

$recordatorios = $DB->get_records_sql("SELECT * FROM {urbanova_recordatorio}");

function enviarRecordatorios($courseId) {
	$subject = 'URBANOVA - Mensaje de seguimiento de curso';
	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $user) {
		$foruser = core_user::get_user($user->id);
		$message = 'Por favor no se olvide de completar su curso pendiente';
		email_to_user($foruser, \core_user::get_noreply_user(), $subject, $message);
	}
}

function obtenerDiasDiferenciaHoy($fecha) {
	return floor(abs(strtotime(date('c')) - $fecha)/60/60/24);
}

foreach ($recordatorios as $recordatorio) {
	$day = date('D');

	if($day == 'Mon' &&  $recordatorio->lunes == 1) {
		enviarRecordatorios($recordatorio->courseid);
		continue;
	}
	if($day == 'Fri' &&  $recordatorio->viernes == 1) {
		enviarRecordatorios($recordatorio->courseid);
		continue;
	}
	if($recordatorio->tresdias == 1) {
		$curso = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($recordatorio->courseid));

		if(isset($curso->enddate) && obtenerDiasDiferenciaHoy($curso->enddate) == 3) {
			enviarRecordatorios($recordatorio->courseid);
			continue;
		}
	}
	if($recordatorio->undia == 1) {
		$curso = $DB->get_record_sql("SELECT * FROM {course} WHERE id = ?", array($recordatorio->courseid));
		if(isset($curso->enddate) && obtenerDiasDiferenciaHoy($curso->enddate) == 1) {
			enviarRecordatorios($recordatorio->courseid);
			continue;
		}
	}
}