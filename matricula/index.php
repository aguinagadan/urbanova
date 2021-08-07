<?php
require_once(__DIR__ . '/../config.php');

require_login();

$urlparams = array();
$PAGE->set_url('/matricula/', $urlparams);

$PAGE->set_pagetype('site-matricula');
$PAGE->set_title($SITE->fullname);
echo $OUTPUT->header();
include('home.html');
echo $OUTPUT->footer();