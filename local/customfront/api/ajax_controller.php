<?php
error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../../../config.php');

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
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

function convertDateToSpanish($timestamp) {
	setlocale(LC_TIME, 'es_ES', 'Spanish_Spain', 'Spanish');
	return strftime("%d de %B de %Y", $timestamp);
}

function getUserImage() {
	global $USER;
	return '/user/pix.php/'.$USER->id.'/f1.jpg';
}

function obtenerSlider() {
	$slides = array();
	$sliderData = \theme_remui\sitehomehandler::get_slider_data();

	foreach($sliderData['slides'] as $slide) {
		$image = str_replace('//aulavirtual.urbanova.com.pe','', $slide['img']);
		$slides[] = ['src' => $image];
	}

	$response['status'] = true;
	$response['data'] = $slides;

	return $response;
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
		'dateReg' => convertDateToSpanish($USER->firstaccess)
	);

	$response['status'] = true;
	$response['data'] = $userArr;

	return $response;
}