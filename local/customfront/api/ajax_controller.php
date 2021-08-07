<?php

use core_completion\progress;
use core_course\external\course_summary_exporter;

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
		case 'obtenerSlider':
			$returnArr = obtenerSlider();
			break;
		case 'obtenerTestimonios':
			$returnArr = obtenerTestimonios();
			break;
		case 'obtenerUsuario':
			$returnArr = obtenerUsuario();
			break;
		case 'obtenerCursosByCat':
			$limit = isset($_POST['limit']) ? $_POST['limit'] : false;
			$returnArr = obtenerCursosByCat($_POST['idCat'], $limit);
			break;
		case 'obtenerBasicInfo':
			$returnArr = obtenerBasicInfo();
			break;
		case 'obtenerCursosPendientes':
			$returnArr = obtenerCursosPendientes();
			break;
		case 'obtenerTotalCursosbyCat':
			$returnArr = obtenerTotalCursosbyCat($_POST['idCat']);
			break;
		case 'obtenerCursosByQuery':
			$returnArr = obtenerCursosByQuery($_POST['q']);
			break;
		case 'cargarComentarios':
			$returnArr = cargarComentarios($_POST['idCourse']);
			break;
		case 'crearComentario':
			$returnArr = crearComentario($_POST);
			break;
		case 'eliminarComentario':
			$returnArr = eliminarComentario($_POST['id']);
			break;
		case 'obtenerDepartamentos':
			$returnArr = obtenerDepartamentos();
			break;
		case 'matricular':
			$returnArr = matricular($_POST);
			break;
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

function formatCurrentTime($time) {
	if (isset($time)) {
		$time = strtotime(date('Y-m-d H:i', $time));
	}
	return $time;
}

function timeSince($original) {
	$original = formatCurrentTime($original);

	$ta = array(
		array(31536000, "Año", "Años"),
		array(2592000, "Mes", "Meses"),
		array(604800, "Semana", "Semanas"),
		array(86400, "Día", "Días"),
		array(3600, "Hora", "Horas"),
		array(60, "Minuto", "Minutos"),
		array(1, "Segundo", "Segundos")
	);
	$since = time() - $original;
	$res = "";
	$lastkey = 0;
	for ($i = 0; $i < count($ta); $i++) {
		$cnt = floor($since / $ta[$i][0]);
		if ($cnt != 0) {
			$since = $since - ($ta[$i][0] * $cnt);
			if ($res == "") {
				$res .= ($cnt == 1) ? "1 {$ta[$i][1]}" : "{$cnt} {$ta[$i][2]}";
				$lastkey = $i;
			} else if ($ta[0] >= 60 && ($i - $lastkey) == 1) {
				$res .= ($cnt == 1) ? " y 1 {$ta[$i][1]}" : " y {$cnt} {$ta[$i][2]}";
				break;
			} else {
				break;
			}
		}
	}
	return $res;
}

function convertDateToSpanish($timestamp, $comma) {
	setlocale(LC_TIME, 'es_ES', 'Spanish_Spain', 'Spanish');
	return strftime("%d de %B$comma%Y", $timestamp);
}

function getUserImage() {
	global $USER;
	return '/user/pix.php/'.$USER->id.'/f1.jpg';
}

function obtenerSlider() {
	$slides = array();
	$sliderData = \theme_remui\sitehomehandler::get_slider_data();
	$isLoggedIn = false;

	foreach($sliderData['slides'] as $slide) {
		$image = str_replace('//aulavirtual.urbanova.com.pe','', $slide['img']);
		$slides[] = ['src' => $image];
	}

	if (isloggedin()) {
		$isLoggedIn = true;
	}

	$response['status'] = true;
	$response['data'] = $slides;
	$response['loggedIn'] = $isLoggedIn;

	return $response;
}

function getCourseImage($course) {
	$data = new \stdClass();
	$data->id = $course->id;
	$data->fullname = $course->fullname;
	$data->hidden = $course->visible;
	$options = [
		'course' => $course->id,
	];
	$viewurl = new \moodle_url('/admin/tool/moodlenet/options.php', $options);
	$data->viewurl = $viewurl->out(false);
	$category = \core_course_category::get($course->category);
	$data->coursecategory = $category->name;
	$courseimage = course_summary_exporter::get_course_image($data);

	return $courseimage;
}

function obtenerTestimonios() {
	$testimonios = array();
	$testimonialData = \theme_remui\sitehomehandler::get_testimonial_data();

	foreach($testimonialData['testimonials'] as $testimonial) {
		$testimonial['image'] = str_replace('//aulavirtual.urbanova.com.pe','',$testimonial['image']);
		$testimonios[] = ['text' => strip_tags($testimonial['text']), 'avatar' => $testimonial['image']];
	}

	$response['status'] = true;
	$response['data'] = $testimonios;

	return $response;
}

function obtenerUsuario() {
	global $USER;

	$userArr = array(
		'id' => $USER->id,
		'userPhoto' => getUserImage(),
		'username' => strtoupper($USER->firstname . ' ' . $USER->lastname),
		'dateReg' => convertDateToSpanish($USER->firstaccess,' de ')
	);

	$response['status'] = true;
	$response['data'] = $userArr;

	return $response;
}

function obtenerBasicInfo() {
	$allcourses = core_course_category::get(1)->get_courses(
		array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));

	$response['status'] = true;
	$response['data'] = count($allcourses);

	return $response;
}

function obtenerCursosByCat($idCat, $limit=false) {
	global $USER;

	$courses = array();
	$allcourses = core_course_category::get($idCat)->get_courses(
		array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));

	foreach($allcourses as $course) {
		$percentage = round(progress::get_course_progress_percentage($course, $USER->id));
		$courses[] = [
			'id'=> $course->id,
			'title'=> strtoupper($course->fullname),
			'content'=> strip_tags($course->summary),
			'link'=> '/course/view.php?id='.$course->id,
			'porcent' => $percentage + 1,
			'image' => \theme_remui_coursehandler::get_course_image($course, 1),
		];
	}

	if($limit) {
		$courses = array_slice($courses, -$limit, $limit, true);
	}

	$response['status'] = true;
	$response['data'] = $courses;

	return $response;
}

function obtenerCursosPendientes() {
	global $USER;
	$returnArr = array();
	$userCourses = core_course_category::get(1)->get_courses(
		array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));
	$enrolledCourses = enrol_get_users_courses($USER->id, true);
	$enrolledIds = array_keys($enrolledCourses);

	foreach($userCourses as $course) {
		$percentage = progress::get_course_progress_percentage($course, $USER->id);
		if($percentage == 100 || !in_array($course->id, $enrolledIds)) {
			continue;
		}
		$returnArr[] = [
			'title' => strtolower($course->fullname),
			'content' => 'test',
			'progress' => round($percentage),
			'link' => '/course/view.php?id='.$course->id,
			'image' => \theme_remui_coursehandler::get_course_image($course, 1),
			'dateEnd' => !empty($course->enddate) ? convertDateToSpanish($course->enddate,', ') : ''
		];
	}

	$response['status'] = true;
	$response['data'] = $returnArr;

	return $response;
}

function obtenerTotalCursosbyCat($idCat) {
	global $USER;

	$returnArr = array();
	$userCourses = core_course_category::get($idCat)->get_courses(
		array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));
	$enrolledCourses = enrol_get_users_courses($USER->id, true);
	$enrolledIds = array_keys($enrolledCourses);

	foreach($userCourses as $course) {
		if(!in_array($course->id, $enrolledIds)) {
			continue;
		}
		$percentage = progress::get_course_progress_percentage($course, $USER->id);
		$returnArr[] = [
			'title'=> strtolower($course->fullname),
			'content' => strip_tags($course->summary),
			'progress' => round($percentage),
			'link' => '/course/view.php?id='.$course->id,
			'image' => \theme_remui_coursehandler::get_course_image($course, 1),
			'dateEnd' => !empty($course->enddate) ? convertDateToSpanish($course->enddate,', ') : ''
		];
	}

	$response['status'] = true;
	$response['data'] = $returnArr;

	return $response;
}

function obtenerCursosByQuery($q) {
	global $USER;

	$courses = array();
	$allcourses = core_course_category::get(1)->get_courses(
		array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));

	foreach($allcourses as $course) {
		if(strpos(strtolower($course->fullname), strtolower($q)) !== false) {
			$percentage = round(progress::get_course_progress_percentage($course, $USER->id));
			$courses[] = [
				'title' => strtoupper($course->fullname),
				'content' => strip_tags($course->summary),
				'link' => '/course/view.php?id=' . $course->id,
				'porcent' => $percentage + 1,
				'image' => \theme_remui_coursehandler::get_course_image($course, 1),
			];
		}
	}

	$response['status'] = true;
	$response['data'] = $courses;

	return $response;
}
function cargarComentarios($id) {
	global $DB, $USER;
	$returnArr = array();

	$data = $DB->get_records_sql("SELECT * FROM {urbanova_comments} WHERE courseid = ? AND deleted = 0", array($id));

	$comentarios = !empty($data) ? $data : array();

	if(!empty($comentarios)) {
		foreach($comentarios as $comentario) {

			$user = $DB->get_record('user', array('id' => $comentario->userid));

			$returnArr[] = [
				'id'=> $comentario->id,
				'comentario'=> $comentario->comment,
				'user' => $user->firstname . ' ' . $user->lastname,
				'date' => 'Hace ' . timeSince(strtotime($comentario->timecreated)),
				'comentario_user_id' => $user->id,
				'current_user_id' => $USER->id,
			];
		}
	}

	$response['status'] = true;
	$response['data'] = $returnArr;

	return $response;
}

function crearComentario($details) {
	global $DB, $USER;

	$comentario = new stdClass();
	$comentario->courseid = $details['idCourse'];
	$comentario->userid = $USER->id;
	$comentario->comment = $details['commentTxt'];
	$comentario->deleted = 0;
	$comentario->timecreated = date("Y-m-d H:i:s");

	$DB->insert_record('urbanova_comments', $comentario);

	$response['status'] = true;

	return $response;
}

function eliminarComentario($id) {
	global $DB;

	$comentarioObj = $DB->get_record_sql("SELECT * FROM {urbanova_comments} WHERE id = ?", array($id));

	if (!empty($comentarioObj)) {
		$comentarioObj->deleted = 1;
		$comentarioObj->timecreated = date("Y-m-d H:i:s");
		$DB->update_record('urbanova_comments', $comentarioObj);
	}

	$response['status'] = true;

	return $response;
}

function obtenerDepartamentos() {
	global $DB;

	$returnArr = $DB->get_records_sql("SELECT department FROM {user} WHERE department!='' GROUP BY department");

	$response['data'] = array_keys($returnArr);
	return $response;
}

function check_enrol($courseid, $userid) {
	global $DB;
	$user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	$context = context_course::instance($course->id);
	if (!is_enrolled($context, $user)) {
		$enrol = enrol_get_plugin('manual');
		if ($enrol === null) {
			return false;
		}
		$instances = enrol_get_instances($course->id, true);
		$manualinstance = null;
		foreach ($instances as $instance) {
			if ($instance->name == 'manual') {
				$manualinstance = $instance;
				break;
			}
		}
		if ($manualinstance !== null) {
			$instanceid = $enrol->add_default_instance($course);
			if ($instanceid === null) {
				$instanceid = $enrol->add_instance($course);
			}
			$instance = $DB->get_record('enrol', array('id' => $instanceid));
		}
		$enrol->enrol_user($instance, $userid);
	}
	return true;
}

function matricular($detail) {
	global $DB, $USER;

	$idCurso = $detail['idCurso'];
	$departamentos = $detail['departamentos'];
	$isNew = $detail['newUsers'];

	list($insql, $params) = $DB->get_in_or_equal($departamentos);
	$sql = "select * from mdl_user WHERE department $insql GROUP BY department";
	$users = $DB->get_records_sql($sql, $params);

	foreach($users as $user) {
		check_enrol($idCurso, $user->id);
	}

	foreach($departamentos as $departamento) {
		$matricula = new stdClass();
		$matricula->department = $departamento;
		$matricula->courseid = $idCurso;
		$matricula->isnew = $isNew == 'true' ? 1 : 0;
		$matricula->userid = $USER->id;
		$matricula->createddate = date("Y-m-d H:i:s");

		$DB->insert_record('urbanova_matricula', $matricula);
	}

	$response['status'] = true;

	return $response;
}