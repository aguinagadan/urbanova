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
			$returnArr = obtenerCursosByCat($_POST['idCat']);
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
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

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

function obtenerCursosByCat($idCat) {
	global $USER;

	$courses = array();
	$allcourses = core_course_category::get($idCat)->get_courses(
		array('recursive' => true, 'coursecontacts' => true, 'sort' => array('idnumber' => 1)));

	foreach($allcourses as $course) {
		$percentage = round(progress::get_course_progress_percentage($course, $USER->id));
		$courses[] = [
			'title'=> strtoupper($course->fullname),
			'content'=> strip_tags($course->summary),
			'link'=> '/course/view.php?id='.$course->id,
			'porcent' => $percentage + 1,
			'image' => \theme_remui_coursehandler::get_course_image($course, 1),
		];
	}

	$response['status'] = true;
	$response['data'] = $courses;

	return $response;
}

function obtenerCursosPendientes() {
	global $USER;
	$returnArr = array();
	$userCourses = enrol_get_users_courses($USER->id, true);

	foreach($userCourses as $course) {
		$percentage = progress::get_course_progress_percentage($course, $USER->id);
		if($percentage == 100) {
			continue;
		}
		$returnArr[] = [
			'title' => strtolower($course->fullname),
			'content' => strip_tags($course->summary),
			'progress' => round($percentage) + 1,
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
	$userCourses = enrol_get_users_courses($USER->id, true);

	var_dump($userCourses);
	exit;

	foreach($userCourses as $course) {
		$percentage = progress::get_course_progress_percentage($course, $USER->id);
		$returnArr[] = [
			'title'=> strtolower($course->fullname),
			'content' => strip_tags($course->summary),
			'progress' => round($percentage) + 1,
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