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
 * Plugin administration pages are defined here.
 *
 * @package     theme_remui
 * @category    admin
 * @copyright   2019 wisdmlabs <support@wisdmlabs.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
global $PAGE;

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string("setupwizard", "theme_remui"));
$PAGE->set_heading(get_string("setupwizard", "theme_remui"));
$PAGE->set_url($CFG->wwwroot.'/theme/remui/setupwizard.php');

// now we'll deal with the case that the admin has submitted the form with new settings
if ($data = data_submitted() and confirm_sesskey()) {
    $count = admin_write_settings($data);
    redirect($CFG->wwwroot);
}

$templatecontext = [];

$stepData = \theme_remui\utility::setup_wizard_step_wise_data();

$templatecontext['steps'] = $stepData['steps'];
$templatecontext['hidden'] = $stepData['hidden'];
$templatecontext['totalSteps'] = $stepData['totalSteps'];
$templatecontext['stepsStatus'] = get_config(THEME_REMUI, 'swstepstatus');

$templatecontext['sesskey'] = sesskey();

// Conditional Logic for Settings
$remuisettings = [];
$remuisettings['logoorsitename'] = [[
    'value'  => 'logo',
    'show' => ['logo', 'logomini'],
    'hide' => ['siteicon']
], [
    'value'  => 'iconsitename',
    'show' => ['siteicon'],
    'hide' => ['logo', 'logomini']
], [
    'value'  => 'sitenamewithlogo',
    'show' => ['logo', 'logomini'],
    'hide' => ['siteicon']
]];

$remuisettings['enrolment_page_layout'] = [[
    'value' => 0,
    'hide' => ['showcoursepricing']
], [
    'value' => 1,
    'show' => ['showcoursepricing']
]];

$remuisettings['showcoursepricing'] = [[
    'value' => 0,
    'hide' => ['enrolment_payment']
], [
    'value' => 1,
    'show' => ['enrolment_payment']
]];

$PAGE->requires->data_for_js('remuisettings', $remuisettings);
$PAGE->requires->js(new moodle_url('/theme/remui/settings.js'));
$PAGE->requires->js_call_amd('theme_remui/settings', 'init');
// Conditional Logic for Settings

echo $OUTPUT->header();
// echo $OUTPUT->container_start();

echo $OUTPUT->render_from_template('theme_remui/setupwizard', $templatecontext);

// echo $OUTPUT->container_end();

echo $OUTPUT->footer();
