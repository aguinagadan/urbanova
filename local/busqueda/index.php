<?php
require_once(__DIR__ . '/../config.php');

require_login();

$PAGE->set_title($SITE->fullname);
echo $OUTPUT->header();
include('home.html');
echo $OUTPUT->footer();