<?php
global $CFG;

require_once(dirname(__FILE__) . '/../../config.php');

function recordar_finalizacion_task() {
	global $DB, $USER;

	$recordatorios = $DB->get_records_sql("SELECT * FROM {mdl_urbanova_recordatorio}");

	$supportuser = core_user::get_support_user();
	$subject = 'URBANOVA - Mensaje de seguimiento de curso';


	foreach ($recordatorios as $recordatorio) {
		$context = CONTEXT_COURSE::instance($recordatorio->courseId);
		$users = get_enrolled_users($context);
//
//		foreach($users as $user) {
//
//		}

//		$foruser = core_user::get_user($userId);
//		$message = 'Por favor no se olvide de completar su curso pendiente: ' . ;
//		email_to_user($foruser, $supportuser, $subject, $message);
	}
}