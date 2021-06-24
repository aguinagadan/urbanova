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
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

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
		$testimonios[] = ['name' => $testimonial['name'],'content' => strip_tags($testimonial['text']), 'url' => $testimonial['image']];
	}

	$response['status'] = true;
	$response['data'] = $testimonios;

	return $response;
}