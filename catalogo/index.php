<?php
global $CFG, $PAGE, $OUTPUT, $USER;

require_once(dirname(__FILE__) . '/../config.php');

require_login();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);

$title = 'Catálogo de Cursos';
$url = new moodle_url("/catalogo/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);

echo $OUTPUT->header();
include('index.html');
echo $OUTPUT->footer();