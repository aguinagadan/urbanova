<?php
require_once(__DIR__ . '/../config.php');

require_login();

$urlparams = array();
$PAGE->set_url('/local/busqueda/index.php', $urlparams);

$PAGE->set_title($SITE->fullname);
echo $OUTPUT->header();
include('home.html');
echo $OUTPUT->footer();