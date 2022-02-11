<?php 

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/daktico/lib.php');

class mod_daktico_mod_form extends moodleform_mod {

    function definition() {
        // global $CFG, $DB, $OUTPUT;
        global $CFG, $DB, $OUTPUT, $PAGE;

        $PAGE->force_settings_menu();

        $mform =& $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));

        $mform->addElement('text', 'name', get_string('dakticoname', 'daktico'), array('size'=>'64'));

        // $mform->addElement('text', 'name', get_string('dakticoname', 'daktico'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('file', 'iframeFile', 'Archivo HTML');

        // $mform->addElement('filepicker', 'iframeFile','Archivo HTML', null,
        //            array('maxbytes' => 1000000, 'accepted_types' => array('.html')));
        // $mform->addRule('iframeFile', null, 'required', null, 'client');
        // $mform->setType('iframeFile', PARAM_RAW);

        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionattendanceenabled) || !$autocompletion) {
                $data->completionattendance = 0;
            }
        }
    }
}