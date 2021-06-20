<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A two column layout for the remui theme.
 *
 * @package   theme_remui
 * @copyright 2016 Damyon Wiese
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->libdir . '/completionlib.php');

use \theme_remui\toolbox;

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
user_preference_allow_ajax_update('pin_aside', PARAM_ALPHA);
user_preference_allow_ajax_update('course_view_state', PARAM_ALPHA);
user_preference_allow_ajax_update('enable_focus_mode', PARAM_BOOL);
user_preference_allow_ajax_update('remui_dismised_announcement', PARAM_BOOL);

global $PAGE, $CFG, $COURSE, $USER;

$PAGE->requires->strings_for_js(['sidebarpinned', 'sidebarunpinned', 'pinsidebar', 'unpinsidebar', 'changelog'], 'theme_remui');

// RemUI Usage Tracking (RemUI Analytics).
$ranalytics = new \theme_remui\usage_tracking();
$ranalytics->send_usage_analytics();

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    $rightsidebar = (get_user_preferences('pin_aside', 'true') == 'true');
    // Always pinned for quiz and book activity.
    $activities = array("book", "quiz");
    if (isset($PAGE->cm->id) && in_array($PAGE->cm->modname, $activities) || $PAGE->user_is_editing()) {
        $rightsidebar = true;
        $navdraweropen = false;
    }
} else {
    $navdraweropen = false;
    $rightsidebar = false;
}
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
// Message drawer html and drawer toggle in sidebar tabs.
$mergemessagingsidebar = \theme_remui\toolbox::get_setting('mergemessagingsidebar');
$messagedrawer = '';
$messagetoggle = '';
if ($mergemessagingsidebar) {
    $messagedrawer = core_message_standard_after_main_region_html();
    $messagetoggle = \theme_remui\usercontroller::render_navbar_output();
}
$unreadrequestcount = 0;
if ($messagetoggle) {
    $unreadcount = \core_message\api::count_unread_conversations($USER);
    $requestcount = \core_message\api::get_received_contact_requests_count($USER->id);
    $unreadrequestcount = $unreadcount + $requestcount;
}
$usercanmanage = \theme_remui\utility::check_user_admin_cap();
$initrightsidebar = false;
$hasmessaging = empty($messagedrawer) !== true;

$extraclasses = ['remui-customizer'];

if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
if ($PAGE->pagelayout == 'mypublic') {
    $extraclasses [] = ' page-profile ';
}
if ($hasblocks) {
    $extraclasses[] = 'hasblocks';
    if ($rightsidebar) {
        $extraclasses[] = 'sidebar-pinned';
    }
} else {
    $messagetoggle = str_replace(
        'class="nav-link popover-region-toggle p-3"',
        'class="nav-link popover-region-toggle active show p-3"',
        $messagetoggle
    );
}

if ($hasblocks || $usercanmanage || $hasmessaging) {
    $initrightsidebar = true;
}

if ($mergemessagingsidebar) {
    $extraclasses[] = 'mergemessagingsidebar';
}

// Focus Mode Code
$focusdata = [];
if (($PAGE->pagelayout === 'course' || $PAGE->pagelayout === 'incourse') && $PAGE->pagetype !== "enrol-index") {
    $focusdata['enabled'] = \theme_remui\toolbox::get_setting('enablefocusmode');
    $focusdata['on'] = get_user_preferences('enable_focus_mode', false) && $focusdata['enabled'];
    if ($focusdata['on']) {
        $extraclasses[] = 'focusmode';
        $focusdata['btnbg'] = 'btn-danger';
        $focusdata['btnicon'] = 'fa-compress';
    } else {
        $focusdata['btnbg'] = 'btn-primary';
        $focusdata['btnicon'] = 'fa-expand';
    }
    $focusdata['coursename'] = $COURSE->fullname;
    if ($PAGE->pagelayout === 'incourse') {
        $focusdata['courseurl'] = $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id;
    }

    $coursecontext = context_course::instance($COURSE->id);

    if (is_enrolled($coursecontext, $USER->id)) {
        $completion = new \completion_info($COURSE);
        if ($completion->is_enabled()) {
            $percentage = \core_completion\progress::get_course_progress_percentage($COURSE, $USER->id);
            if ($percentage === null) {
                $percentage = 0;
            }
            $focusdata['progress'] = (int)$percentage;
        }
    }
}

/*Enrolment Page setup*/
$enrolconfig = get_config('theme_remui', 'enrolment_page_layout');
if ($PAGE->pagetype == "enrol-index" && $enrolconfig == "1") {

    $templatecontext['enableenrollayout'] = $enrolconfig;
    $extraclasses[] = 'enableenrollayout';
}

/*Course Archive Page setup*/
$pagelayout = get_config('theme_remui', 'categorypagelayout');
if ($PAGE->pagelayout == "coursecategory" && $pagelayout !== "0") {
    $extraclasses[] = 'category-layout'.$pagelayout;
}

$customizer = \theme_remui\customizer\customizer::instance();
$extraclasses[] = 'header-site-identity-' . $customizer->get_config('logoorsitename');
$extraclasses[] = 'header-primary-layout-desktop-' . $customizer->get_config('header-primary-layout-desktop');
$extraclasses[] = 'header-primary-layout-mobile-' . $customizer->get_config('header-primary-layout-mobile');
$bodyattributes = $OUTPUT->body_attributes($extraclasses);

$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$sitecolor = get_config('theme_remui', 'sitecolor');
$sitecolor = ($sitecolor == "") ? 'primary' : $sitecolor;
$sitecolorhex = get_config('theme_remui', 'sitecolorhex');
if (stripos($sitecolorhex, '#') === false) {
    $sitecolorhex = '#'.$sitecolorhex;
}
$navbarinverse = get_config('theme_remui', 'navbarinverse');
$sidebarcolor = get_config('theme_remui', 'sidebarcolor');
$lcontroller = new \theme_remui\controller\LicenseController();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'cansignup' => (!empty($CFG->registerauth) && (!isloggedin() || isguestuser())),
    'signupurl' => new moodle_url('/login/signup.php'),
    'footerdata' => \theme_remui\utility::get_footer_data(),
    'usercanmanage' => $usercanmanage,
    'messagedrawer' => $messagedrawer,
    'initrightsidebar' => $initrightsidebar,
    'messagetoggle' => $messagetoggle,
    'navbarinverse' => $navbarinverse,
    'sidebarcolor' => $sidebarcolor,
    'unreadrequestcount' => $unreadrequestcount,
    'pinaside' => $rightsidebar,
    $sitecolor => true,
    'sitecolorhex' => $sitecolorhex,
    'focusdata' => $focusdata,
    'cansendfeedback' => (is_siteadmin()) ? true : false, // check to show feedback button if admin user
    'feedbacksender_emailid' => isset($USER->email) ? $USER->email : '', // email id of the person sending feedback, for auto fill the email field in feedback overview modal
    'feedback_loading_image' => $OUTPUT->image_url('a/loading', 'core'),
    'licensestatus_forfeedback' => ($lcontroller->get_data_from_db() == 'available') ? 1 : 0
];

if (isset($fullwidthenrol)) {
    $templatecontext['fullwidthenrol'] = $fullwidthenrol;
}

$flatnavigation = theme_remui_get_flat_navigation($PAGE->flatnav);

$templatecontext['flatnavigation'] = $flatnavigation;
$templatecontext['firstcollectionlabel'] = $flatnavigation->get_collectionlabel();
$templatecontext['navfootermenu'] = \theme_remui\utility::get_left_nav_footer_menus();

if (isloggedin() && \theme_remui\toolbox::get_setting('enablerecentcourses')) {
    $courses = \theme_remui_coursehandler::get_recent_accessed_courses(5);
    $finalarr = array();
    foreach ($courses as $key => $course) {
        $templatecontext['hasrecent'] = true;
        $finalarr[] = array (
            'id' => $course->courseid,
            'fullname' => format_text($course->fullname)
        );
    }
    $templatecontext['recentcourses'] = $finalarr;
}

$templatecontext['enabledictionary'] = \theme_remui\toolbox::get_setting('enabledictionary');
if (strpos($bodyattributes, 'editing') !== false) {
    $templatecontext['editingenabled'] = true;
}

if (get_user_preferences('course_cache_reset')) {
    $coursehandler = new \theme_remui_coursehandler();
    $coursehandler->invalidate_course_cache();
} else if (get_config('theme_remui', 'cache_reset_time') > get_user_preferences('cache_reset_time')) {
    $coursehandler = new \theme_remui_coursehandler();
    $coursehandler->invalidate_course_cache();
}

// Setup Wizard Prompt
if (is_siteadmin()) {
    $swflag = get_config('theme_remui', 'flagsetupwizard');

    if (!$swflag) {
        $templatecontext['promptsetupwizard'] = true;
    }
}

// feedback JS needs this URL to funtion properly.
$canvasurl = new moodle_url($CFG->wwwroot . '/theme/remui/amd/src/html2canvas.js');
$templatecontext['canvasurl'] = $canvasurl->__toString();

// Unset the EDD_LICENSE_ACTION i.e. license notice- for activated themes.
toolbox::remove_plugin_config(EDD_LICENSE_ACTION);
