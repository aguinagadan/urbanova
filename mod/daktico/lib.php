<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * Customcert module core interaction API
 *
 * @package    mod_customcert
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

// define("LABEL_MAX_NAME_LENGTH", 50);

function daktico_supports(string $feature): ?bool {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

function get_daktico_name($data) {
    $name = strip_tags(format_string($data->intro,true));
    if (core_text::strlen($name) > LABEL_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, LABEL_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','daktico');
    }

    return $name;
}

/**
 * Add customcert instance.
 *
 * @param stdClass $data
 * @param mod_customcert_mod_form $mform
 * @return int new customcert instance id
 */
function daktico_add_instance($data) {
    global $DB, $USER;

    $data->timecreated = time();
    $data->userid = $USER->id;

    $iframeFile = $_FILES['iframeFile'];
    if(!empty($iframeFile)) {
        $filename = urlencode(preg_replace("/[^a-zA-Z0-9.]/", "", time().$iframeFile['name']));
        $pathName = __DIR__."/iframes/".$filename;

        if(!file_exists(dirname($pathName))) {
            mkdir(dirname($pathName), 0777, true);
        }

        // var_dump($pathName);

        $location = $pathName;
        $uploadOk = 1;
        $fileType = pathinfo($location,PATHINFO_EXTENSION);

        /* Valid Extensions */
        $valid_extensions = array("html");
        /* Check file extension */
        if( !in_array(strtolower($fileType), $valid_extensions) ) {
            $uploadOk = 0;
        }

        if($uploadOk == 0){
            $responseStatus = false;
        }else{
            /* Upload file */
            $fullPathLocation = $pathName;
            if(!move_uploaded_file($iframeFile['tmp_name'], $fullPathLocation)){
                echo 0;
            }
        }
        $data->imagen = $filename;
        $data->pathfile = $pathName;
        $data->moduleid = $data->coursemodule;
        $data->jsondata = json_encode($data);
    }

    $id = $DB->insert_record("daktico", $data);
    return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $label
 * @return bool
 */
function daktico_update_instance($data) {
    global $DB;

    // $data->titulo = get_daktico_name($data);
    $data->timemodified = time();
    $data->id = $data->instance;
    $data->introformat = 0;

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'daktico', $data->id, $completiontimeexpected);

    return $DB->update_record("daktico", $data);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function daktico_delete_instance($id) {
    global $DB;

    if (! $data = $DB->get_record("daktico", array("id"=>$id))) {
        return false;
    }

    $result = true;

    $cm = get_coursemodule_from_instance('daktico', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'daktico', $data->id, null);

    if (! $DB->delete_records("daktico", array("id"=>$data->id))) {
        $result = false;
    }

    return $result;
}