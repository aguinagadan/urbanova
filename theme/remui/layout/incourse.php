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
 * Edwiser RemUI
 * @package   theme_remui
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once('common.php');

// Prepare activity sidebar context.
global $COURSE, $PAGE, $DB, $CFG;
$isactivitypage = false;
if (isset($PAGE->cm->id) && $COURSE->id != 1 && $COURSE->format != 'singleactivity') {
    $isactivitypage = true;
}
$templatecontext['isactivitypage'] = $isactivitypage;
$templatecontext['courseurl'] = course_get_url($COURSE->id);
$templatecontext['activitysections'] = \theme_remui_coursehandler::get_activity_list();

$flatnavigation = $PAGE->flatnav;
foreach ($flatnavigation as $navs) {
    if ($navs->key === 'addblock') {
        $templatecontext['addblock'] = $navs;
        break;
    }
}
if (isset($templatecontext['focusdata']['enabled']) && $templatecontext['focusdata']['enabled']) {
    if (isset($PAGE->cm->id)) {
        list(
            $templatecontext['focusdata']['sections'],
            $templatecontext['focusdata']['active'],
            $templatecontext['focusdata']['previous'],
            $templatecontext['focusdata']['next']
        ) = \theme_remui\utility::get_focus_mode_sections($COURSE, $PAGE->cm->id);
    } else {
        list(
            $templatecontext['focusdata']['sections'],
            $templatecontext['focusdata']['active']
        ) = \theme_remui\utility::get_focus_mode_sections($COURSE);
    }
}
$enrolconfig = get_config('theme_remui', 'enrolment_page_layout');
/*
 ******************************************
 * Enrollment Page Context data
 */
if ($PAGE->pagetype == "enrol-index" && $enrolconfig == "1") {

    $coursehandler =  new \theme_remui_coursehandler();
    $timezone = core_date::get_user_timezone($USER);
   
    $courseId = (int)$COURSE->id;
    
    $templatecontext['enrollmentpage'] = true;
    
    if ($DB->record_exists('course', array('id' => $courseId))) {

        $chelper = new \coursecat_helper();
        $courseContext = context_course::instance($courseId);

        $courseRecord = $DB->get_record('course', array('id' => $courseId));
        $courseElement = new core_course_list_element($courseRecord);
    
        $templatecontext['courseid'] = $courseId;
        $templatecontext['shortname'] = $courseRecord->shortname;
        $templatecontext['coursename'] = $courseRecord->fullname;
        $templatecontext['coursesummary'] = $chelper->get_course_formatted_summary($courseElement, array('noclean' => true, 'para' => false));
        

        $coursemetadata = get_course_metadata($courseId);
        if (isset($coursemetadata['edwcourseintrovideourlembedded'])) {
            $templatecontext['courseintrovideo'] = $coursemetadata['edwcourseintrovideourlembedded'];
        }
        
        // Category Details
        $categoryId = $courseRecord->category;
        try {
            $courseCategory = core_course_category::get($categoryId);
            $categoryName = $courseCategory->get_formatted_name();
            $categoryUrl = $CFG->wwwroot . '/course/index.php?categoryid='.$categoryId;
        } catch (Exception $e) {
            $courseCategory = "";
            $categoryName = "";
            $categoryUrl = "";
        }

        // Enrollment Data - Pricing Section
        $purchasedetails = $coursehandler->get_course_purchase_details($COURSE->id);
        $timemodified = userdate($courseRecord->timemodified, get_string('strftimedate', 'langconfig'), $timezone);

        $purchasedetails['subtitletext'] = get_string('lastupdatedon', 'theme_remui') . $timemodified;
        
        // Fetch only student users - and has capability 'mod/quiz:attempt'
        $enrolledstudents = count_enrolled_users($courseContext, 'mod/quiz:attempt');
        if ($enrolledstudents !== 0) {
            $strenrol = 'enrolledstudents';
            if ($enrolledstudents == 1 || $enrolledstudents == "1") {
                $strenrol = 'enrolledstudent';
            }
            $enrolledstudents .= get_string($strenrol, 'theme_remui');
            $purchasedetails['enrolledstudents'] = $enrolledstudents;
        }
        
        $templatecontext['purchasesection'] = $purchasedetails; 
        
        // Course Duration 
        if(isset($coursemetadata['edwcoursedurationinhours'])) {
            $courseduration = $coursemetadata['edwcoursedurationinhours'];
            $hourcourse = 'hourscourse';
            if (is_numeric($courseduration)) {
                if ($courseduration == 1 || $courseduration == "1") {
                    $hourcourse = 'hourcourse';
                }
            }
            $courseduration .= get_string($hourcourse, 'theme_remui');
            $templatecontext['purchasesection']['courseduration'] = $courseduration;
        }

        // Course Topic and Course Modules Data   
        $sectioncount = 0;
        $totalcms = 0;
        $totalresources = 0;
        $totaldownloadable = 0;
        $totalassignments = 0;
        $totalquizzes = 0;

        $contentdata = [];
        if ($courseCategory !== "") {
            $contentdata['category']['name'] = $categoryName;
            $contentdata['category']['url'] = $categoryUrl;
        }

        $contentdata['course']['startdate'] = userdate($courseRecord->startdate, get_string('strftimedatefullshort', 'langconfig'), $timezone);

        $modinfo = get_fast_modinfo($COURSE);
        $sections = $modinfo->get_section_info_all();

        // Fetching Course Sections/Topics 
        foreach ($sections as $sectionnum => $section) {
            // Display Sections/Topics even if they are hidden and restricted. 
            if ($section->__get('uservisible') || $section->__get('availableinfo') || $section->__get('available')) {
                if ($sectioncount == 0) {
                    $contentdata['sections'][$sectioncount]['sectionactive'] = true;    
                }
                $contentdata['sections'][$sectioncount]['index'] = $sectioncount;
                $contentdata['sections'][$sectioncount]['name'] = get_section_name($COURSE->id, $sectionnum);
                $sectioncount += 1;
            }
        }
        $totalnotopics = $sectioncount; // Calculating total number of topics.

        // Fetching Course Modules 
        $defaultresources = ['book', 'file', 'folder', 'label', 'page', 'url'];
        $cms = $modinfo->get_cms();
        foreach ($cms as $key => $cm) {
            if ($cm->__get('uservisible') || $cm->__get('availableinfo') || $cm->__get('available')) {
                $activity = [];
                $activity['name'] = $cm->get_formatted_name();
                $activity['icon'] = $cm->get_icon_url()->__toString();
                $contentdata['sections'][$cm->__get('sectionnum')]['activities'][]= $activity;
                
                $modname = $cm->__get('modname');
                
                // Calculating total number of Resources.
                if ($cm->__get('customdata') !== "") {
                    $totaldownloadable += 1;
                    $totalresources += 1;
                } else if(in_array($modname, $defaultresources)) {
                    $totalresources += 1;
                } else if ($modname == 'assign') {
                    $totalassignments += 1;
                } else if ($modname == 'quiz') {
                    $totalquizzes += 1;
                }

                $totalcms += 1; // Calculating total number of activities.
            }
        }
        $totalactivities = $totalcms - $totalresources;
                
        if ($totalcms !== 0) { $templatecontext['totalcms'] = $totalcms;}
        if ($totalnotopics !== 0) { 
            $templatecontext['totaltopics'] = $totalnotopics;
            $templatecontext['featuresection']['lectures'] = $totalnotopics;
        }
        if ($totalresources !== 0) { $templatecontext['totalresources'] = $totalresources;}
        if ($totalactivities !== 0) { $templatecontext['totalactivities'] = $totalactivities;}

        // Purchase Section Data
        if ($totalassignments !== 0) {
            $strdownloadres = $totalassignments . get_string('assignment', 'theme_remui');
            if ($totalassignments != "1" || $totalassignments != 1) { 
                $strdownloadres .= 's';
            }
            $templatecontext['purchasesection']['totalassignments'] = $strdownloadres;
        }

        if ($totaldownloadable !== 0) {
            $strdownloadres = $totaldownloadable . get_string('downloadresource', 'theme_remui');
            if ($totaldownloadable != "1" || $totaldownloadable != 1) { 
                $strdownloadres .= 's';
            }
            $templatecontext['purchasesection']['totaldownloadable'] = $strdownloadres;
        }

        // Feature Section Data
        if ($totalquizzes !== 0) { $templatecontext['featuresection']['totalquizzes'] = $totalquizzes;}
        if(isset($coursemetadata['edwcoursedurationinhours'])) {
            $templatecontext['featuresection']['courseduration'] = $coursemetadata['edwcoursedurationinhours'];
        }
        // Skill Level
        if(isset($coursemetadata['edwskilllevel'])) {
            $templatecontext['featuresection']['skilllevel'] = get_string('skill'.$coursemetadata['edwskilllevel'],'theme_remui');
        }
        
        $language = 'English';
        if ($COURSE->lang != "") {
            $language = $COURSE->lang;
        }
        $templatecontext['featuresection']['language'] = $language;

        if ($COURSE->enablecompletion) {
            $templatecontext['featuresection']['completion'] = true;            
        }

        $templatecontext['featuresection']['startdate'] = userdate($courseRecord->startdate, get_string('strftimedatemonthabbr', 'langconfig'), $timezone);

        // Main Activities and Its section Data - for main content section.
        $templatecontext['coursemaincontent'] = $contentdata;


        $tags = \core_tag_tag::get_item_tags('core', 'course', $courseId);
        
        if (!empty($tags)) {
            foreach ($tags as $key => $tag) {
                $tagarr = [];
                $tagarr['tagname'] = $tag->get_display_name();
                $tagarr['url'] = $tag->get_view_url(0,0,0,1)->__toString();
                $templatecontext['tagsection']['tags'][] = $tagarr;    
            }
            $templatecontext['tagsection']['hastags'] = true;
        } else {
            $templatecontext['tagsection']['notags'] = get_string('notags', 'theme_remui');
        }
    }

    // Get only teachers from course.
    $teachers = get_enrolled_users($courseContext, 'mod/folder:managefiles', 0,'u.id');
    
    $templatecontext['instructors']['hasinstructors'] = false;
    if (!empty($teachers)) {
        $templatecontext['instructors']['hasinstructors'] = true;
        $firstteacher = true;
        $showmore = false;
        foreach ($teachers as $key => $teacher) {
            $instructors = array(); 
            if ($firstteacher) {
                // Load only first teacher.
                $instructors['data'] = \theme_remui\usercontroller::wdmGetUserDetails($teacher->id);
                $templatecontext['instructors']['list'][] = $instructors;

                $templatecontext['courseteacher']['fullname'] = $instructors['data']->fullname;
                $templatecontext['courseteacher']['rawAvatar'] = $instructors['data']->rawAvatar;
                $firstteacher = false; // Set flag false for every other teacher.
            } else {
                // Keep the reference of others.
                $instructors['instructorid'] = $teacher->id;
                $templatecontext['instructors']['list'][] = $instructors;

                $showmore = true; // Enable show more button.
            }
        }

        if ($showmore) {
            $templatecontext['instructors']['showmore'] = true;
        }
    }
}
/*
 * Enrollment Page Context data
 ******************************************
 */

echo $OUTPUT->render_from_template('theme_remui/incourse', $templatecontext);
