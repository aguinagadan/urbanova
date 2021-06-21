<?php
require_once(__DIR__ . '/../config.php');

$urlparams = array();
$PAGE->set_url('/catalogo/index.php', $urlparams);

$PAGE->set_pagetype('site-catalogo');
$PAGE->set_title($SITE->fullname);
echo $OUTPUT->header();
//include('index.html');
echo $OUTPUT->footer();