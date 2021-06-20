<?php
global $CFG, $PAGE, $OUTPUT, $USER;

require_once($CFG->dirroot .'/config.php');

require_login();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);

$title = 'Catálogo de Cursos';
$url = '/catalogo/index.php';
$PAGE->set_url($url);
$PAGE->set_pagetype('site-index');
$PAGE->set_title($title);

echo $OUTPUT->header();
include('index.html');
echo $OUTPUT->footer();