<?php

// use core_completion\progress;
// use core_course\external\course_summary_exporter;

error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/enrol/externallib.php');

try {
	global $USER, $PAGE;
	$details = $_POST;
	$returnArr = array();

	if (!isset($_REQUEST['request_type']) || strlen($_REQUEST['request_type']) == false) {
		throw new Exception();
	}

	switch ($_REQUEST['request_type']) {
		case 'getPreguntasEncuesta':
			$returnArr = getPreguntasEncuesta();
			break;
		case 'encuestaRespByUser':
			$id = $_REQUEST['id'];
			$puntaje = $_REQUEST['puntaje'];
			$sesskey = $_REQUEST['sesskey'];
			$returnArr = encuestaRespByUser($id, $puntaje, $sesskey);
			break;
		case 'getPreguntasOpcionesEvaluacion':
			$returnArr = getPreguntasOpcionesEvaluacion();
			break;
		case 'insertResultadoEvaluacion':
			$puntaje = $_REQUEST['puntaje'];
			$sesskey = $_REQUEST['sesskey'];
			$returnArr = insertResultadoEvaluacion($puntaje, $sesskey);
			break;
		case 'getMateriales':
			$returnArr = getMateriales();
			break;
		case 'materialesMarcadosByUser':
			$materialid = $_REQUEST['materialid'];
			$sesskey = $_REQUEST['sesskey'];
			$returnArr = materialesMarcadosByUser($materialid, $sesskey);
			break;
		case 'actualizarEstadodakticoByUser':
			$cursoid = $_REQUEST['cursoid'];
			$puntaje = $_REQUEST['puntaje'];
			$sesskey = $_REQUEST['sesskey'];
			$returnArr = actualizarEstadodakticoByUser($cursoid, $puntaje, $sesskey);
			break;
		case 'actividadCompletada':
			$coursemoduleid = $_REQUEST['coursemoduleid'];
			$completionstate = $_REQUEST['completionstate'];
			$sesskey = $_REQUEST['sesskey'];
			$returnArr = actividadCompletada($coursemoduleid, $completionstate, $sesskey);
			break;
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

/** 
 * getPreguntasEncuesta
 * * obtengo las pregunta de la encuesta 
 * ? se deberia excluir las preguntas que ya fueron respondidas?
 * ? se deberia mostrar la puntuacion de la pregunta si ya fue marcada?
 */
function getPreguntasEncuesta() {
	global $DB, $USER;
	$not_in = [];
	$if_exists = $DB->get_records('aq_encuesta_user_data', [
		'userid' => $USER->id
	]);
	if(count($if_exists)){
		foreach ($if_exists as $key => $value) {
			array_push($not_in, $value->preguntaid);
		}
	}
	// TODO: si se decide excluir las preguntas entonces hacer un RAW SQL QUERY 
	return $DB->get_records('aq_encuesta_data', [
		'active' => 1
	]);
}

/**
 * encuestaRespByUser
 * * guarda las respuestas del usuario
 * @param id es el id de la pregunta
 * @param puntaje es el puntaje de la pregunta
 * @param sesskey es la sesion del usuario
 */
function encuestaRespByUser($id, $puntaje, $sesskey){
	global $DB, $USER;
	require_sesskey();

	$if_exists = $DB->get_records('aq_encuesta_user_data', [
		'userid' => $USER->id,
		'preg_encuestaid' => $id,
	]);

	if(count($if_exists)){
		foreach ($if_exists as $key => $value) {
			$data = array(
				'id' => $value->id,
				'puntaje' => $puntaje,
				'updated_at' => time()
			);
			$DB->update_record('aq_encuesta_user_data', $data);
		}
		return 'updated';
	}else{
		$data = array(
			'userid' => $USER->id,
			'preg_encuestaid' => $id,
			'puntaje' => $puntaje,
			'created_at' => time()
		);
		$insert_id = $DB->insert_record('aq_encuesta_user_data', $data);
		return 'inserted';
	}
}

/**
 * getPreguntasOpcionesEvaluacion
 * * obtiene las preguntas de la evaluacion y sus opciones
 */
function getPreguntasOpcionesEvaluacion(){
	global $DB, $USER;

	$result = $DB->get_field('aq_eval_user_puntaje_data', 'puntaje_porcentaje', [
		'userid' => $USER->id
	]);

	$data = [];

	$preguntas = $DB->get_records('aq_evaluacion_data', [
		'active' => 1
	]);

	foreach ($preguntas as $key => $value) {
		array_push($data, (object) array(
			'id' => $value->id,
			'pregunta' => $value->pregunta,
			'opciones' => $DB->get_records('aq_evaluacion_options_data',[
				'preguntaid' => $value->id,
				'active' => 1
			], null, 'id, opcion, preguntaid, is_valid, active')
		));
	}

	$output = [
		'preguntas' => $data,
		'result' => $result == false ? 0 : intval($result) 
	];

	return $output;
}

/**
 * insertResultadoEvaluacion
 * * guarda el resultado de la evaluacion del usuario
 * ! EL PUNTAJE ES PORCENTUAL
 * @param puntaje es el puntaje obtenido por el usuario 
 * @param sesskey es la sesion del usuario
 */
function insertResultadoEvaluacion($puntaje, $sesskey){
	global $DB, $USER;
	require_sesskey();

	$if_exists = $DB->get_records('aq_eval_user_puntaje_data', [
		'userid' => $USER->id
	]);

	if(count($if_exists)){
		foreach ($if_exists as $key => $value) {
			$data = array(
				'id' => $value->id,
				'puntaje_porcentaje' => $value->puntaje_porcentaje > 80 ? $value->puntaje_porcentaje : $puntaje,
				'created_at' => time()
			);
			$DB->update_record('aq_eval_user_puntaje_data', $data);
		}
		return 'updated';
	}else{
		$data = array(
			'userid' => $USER->id,
			'puntaje_porcentaje' => $puntaje,
			'created_at' => time()
		);
		$insert_id = $DB->insert_record('aq_eval_user_puntaje_data', $data);
		return 'inserted';
	}
}

/**
 * getMateriales
 * * obtiene los registros para la actividad revision material
 */
function getMateriales(){
	global $DB, $USER;

	$data = [];
	$materiales = $DB->get_records('aq_material_data', [
		'active' => 1
	]);

	foreach ($materiales as $key => $value) {
		$if_marked = $DB->get_records('aq_material_revisado_data', [
			'userid' => $USER->id,
			'materialid' => $value->id
		]);
		array_push($data, [
			'id' => $value->id,
			'material_title' => $value->material_title,
			'material_icon' => $value->material_icon,
			'link_file' => $value->link_file,
			'format' => $value->format,
			'marked' => count($if_marked) ? true : false

		]);
	}
	return $data;
}

/**
 * materialesMarcadosByUser
 * * son los materiales marcasdos por un usuario
 * @param materialid es el id del material
 * @param sesskey es la sesion del usuario
 */

function materialesMarcadosByUser($materialid, $sesskey){
	global $DB, $USER;
	require_sesskey();

	$if_marked = $DB->get_records('aq_material_revisado_data', [
		'userid' => $USER->id,
		'materialid' => $materialid
	]);

	if(count($if_marked)){
		foreach ($if_marked as $key => $value) {
			$data = array(
				'id' => $value->id,
				'userid' => $USER->id,
				'materialid' => $materialid,
				// 'updated_at' => time()
			);
			$DB->delete_records('aq_material_revisado_data', $data);
		}
		return 'updated';
	}else{
		$data = array(
			'userid' => $USER->id,
			'materialid' => $materialid,
			'created_at' => time()
		);
		$insert_id = $DB->insert_record('aq_material_revisado_data', $data);
		return 'inserted';
	}
}

// TODO: implementar funciones crud de las tablas generadas

/**
 * insertMaterial
 * * funcion para agregar materiales
 * ! los iconos son basados en GOOGLE ICONS
 * @param material_title el titulo del material
 * @param link_file el link del material
 * @param material_icon el icono del material a mostrar
 * @param format el formato del @link_file ej. [.pdf, .mp4, .jpg, .???]
 * @param active 1 = el material se mostrara, 0 = el material NO se mostrara
 * @param sesskey es la sesion del usuario
 */
function insertMaterial($material_title, $link_file, $material_icon, $format, $active, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'material_title' => $material_title,
		'link_file' => $link_file,
		'material_icon' => $material_icon,
		'format' => $format,
		'active' => $active,
		'created_at' => time()
	);
	$insert_id = $DB->insert_record('aq_material_data', $data);
	return 'inserted';
}

/**
 * actualizarMaterial
 * * funcion para actualizar materiales
 * ! los iconos son basados en GOOGLE ICONS
 * @param materialid el identificador del material a actualizar
 * @param material_title el titulo del material
 * @param link_file el link del material
 * @param material_icon el icono del material a mostrar
 * @param format el formato del @link_file ej. [.pdf, .mp4, .jpg, .???]
 * @param active 1 = el material se mostrara, 0 = el material NO se mostrara
 * @param sesskey es la sesion del usuario
 */
function actualizarMaterial($materialid, $material_title, $link_file, $material_icon, $format, $active, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'id' => $materialid,
		'material_title' => $material_title,
		'link_file' => $link_file,
		'material_icon' => $material_icon,
		'format' => $format,
		'active' => $active,
		'updated_at' => time()
	);
	$DB->update_record('aq_material_data', $data);
	return 'updated';
}

/**
 * eliminarMaterial
 * * funcion para eliminar materiales
 * @param materialid el identificador del material a eliminar
 * @param sesskey es la sesion del usuario
 */
function eliminarMaterial($materialid, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'id' => $materialid
	);
	$DB->delete_records('aq_material_data', $data);
	return 'deleted';
}

/**
 * insertarPreguntaEvaluacion
 * * funcion para insertar preguntas para la evaluacion
 * @param pregunta una pregunta de la evaluacion
 * @param active si la pregunta esta activa, 1 = la pregunta se mostrara, 0 = la pregunta NO se mostrara
 * @param sesskey es la sesion del usuario
 */
function insertarPreguntaEvaluacion($pregunta, $active, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'pregunta' => $pregunta,
		'active' => $active,
		'created_at' => time()
	);
	$DB->insert_record('aq_evaluacion_data', $data);
	return 'inserted';
}

/**
 * actualizarPreguntaEvaluacion
 * * funcion para actualizar una pregunta de la evaluacion
 * @param preguntaid es el id de la pregunta a actualizar
 * @param pregunta es el texto de la pregunta
 * @param active si la pregunta esta activa, 1 = la pregunta se mostrara, 0 = la pregunta NO se mostrara
 * @param sesskey es la sesion del usuario
 */
function actualizarPreguntaEvaluacion($preguntaid, $pregunta, $active, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'id' => $preguntaid,
		'pregunta' => $pregunta,
		'active' => $active,
		'updated_at' => time()
	);
	$DB->update_record('aq_evaluacion_data', $data);
	return 'updated';
}

/**
 * eliminarPreguntaEvaluacion
 * * funcion que elimina una pregunta de la evaluacion
 * ? se deberia eliminar las opciones de la pregunta tambien?
 * ? o se deberia impedir la eliminacion de la pregunta si esta tiene opciones asignadas?
 * @param preguntaid es el id de la pregunta a eliminar
 * @param sesskey es la sesion del usuario
 * TODO: implemetar la eliminacion de las opciones / impedir la eliminacion si la pregunta tiene opciones
 */
function eliminarPreguntaEvaluacion($preguntaid, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'id' => $preguntaid
	);
	// $DB->delete_records('aq_evaluacion_data', $data);
	return 'deleted';
}

/**
 * insertarOpcionesPregunta
 * * funcion que inserta las opciones de una pregunta
 */
function insertarOpcionesPregunta($opcion, $preguntaid, $active, $is_valid, $sesskey){
	global $DB;
	require_sesskey();

	$data = array(
		'opcion' => $opcion,
		'is_valid' => $is_valid,
		'active' => $active,
		'preguntaid' => $preguntaid,
		'created_at' => time()
	);
	$DB->insert_record('aq_evaluacion_data', $data);
	return 'inserted';
}

/**
 * actualizarEstadodakticoByUser
 * * funcion que inserta o actualiza el resultado del daktico que obtuvo un usuario
 * ! estado 1 = aprobado / 0 = desaprobado
 * ? la nota minima aprobatoria deberia ser del 80%? 
 * ? cual es el puntaje maximo
 */

function actualizarEstadodakticoByUser($cursoid, $puntaje, $sesskey){
	global $DB, $USER;
	require_sesskey();

	$puntaje_maximo = 100;
	$userid = $USER->id;
	$username = $USER->username;
	$nombrecompleto = $USER->firstname.' '.$USER->lastname;
	$estado = $puntaje * 100 / $puntaje_maximo;

	$if_exists = $DB->get_records('aq_daktico_estado', [
		'cursoid' => $cursoid,
		'userid' => $userid,
	]);

	if($if_exists){
		foreach ($if_exists as $key => $value) {
			$data = array(
				'id' => $value->id,
				'puntaje' => $puntaje,
				'estado' => $estado >= 80 ? 1 : 0,
				'updated_at' => time()
			);
			$DB->update_record('aq_daktico_estado', $data);
		}
		$res = [
			'userid' => $userid,
			'username' => $username,
			'nombrecompleto' => $nombrecompleto,
			'cursoid' => $cursoid,
			'status' => true,
		];
		return $res;
	}else{
		$data = array(
			'cursoid' => $cursoid,
			'userid' => $userid,
			'puntaje_maximo' => $puntaje_maximo,
			'puntaje' => $puntaje,
			'estado' => $estado >= 80 ? 1 : 0,
			'created_at' => time()
		);
		$DB->insert_record('aq_daktico_estado', $data);
		$res = [
			'userid' => $userid,
			'username' => $username,
			'nombrecompleto' => $nombrecompleto,
			'cursoid' => $cursoid,
			'status' => true,
		];
		return $res;
	}
}

function actividadCompletada($coursemoduleid, $completionstate, $sesskey){
	global $DB, $USER;
	require_sesskey();

	$if_exists = $DB->get_records('course_modules_completion', [
		'coursemoduleid' => $coursemoduleid,
		'userid' => $USER->id,
	]);

	if($if_exists){
		foreach ($if_exists as $key => $value) {
			$data = array(
				'id' => $value->id,
				'completionstate' => $completionstate,
				'timemodified' => time()
			);
			$DB->update_record('course_modules_completion', $data);
		}
		$res = [
			'userid' => $USER->id,
			'completionstate' => $completionstate,
			'status' => true,
		];
		return $res;
	}else{
		$data = array(
			'coursemoduleid' => $coursemoduleid,
			'completionstate' => $completionstate,
			'userid' => $USER->id,
			'timemodified' => time()
		);
		$DB->insert_record('course_modules_completion', $data);
		$res = [
			'completionstate' => $completionstate,
			'userid' => $USER->id,
			'status' => true,
		];
		return $res;
	}
}