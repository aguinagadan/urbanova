<?php
global $CFG;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

//autoload resources
spl_autoload_register(function ($clase) {
	global $CFG;
	include $CFG->dirroot.'/msintegration/' . $clase . '.php';
});

function check_enrol($courseid, $userid, $roleid) {
	global $DB;
	$user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
	$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
	$context = context_course::instance($course->id);
	if (!is_enrolled($context, $user)) {
		$enrol = enrol_get_plugin('manual');
		if ($enrol === null) {
			return false;
		}
		$instances = enrol_get_instances($course->id, true);
		$manualinstance = null;
		foreach ($instances as $instance) {
			if ($instance->name == 'manual') {
				$manualinstance = $instance;
				break;
			}
		}
		if ($manualinstance !== null) {
			$instanceid = $enrol->add_default_instance($course);
			if ($instanceid === null) {
				$instanceid = $enrol->add_instance($course);
			}
			$instance = $DB->get_record('enrol', array('id' => $instanceid));
		}
		$enrol->enrol_user($instance, $userid, $roleid);
	}
	return true;
}

function migrate_users_task() {
	$provider = new AzureProvider();
	createUsers($provider->getUsers());
}
function createUsers($usersAD) {
	global $DB;
	if(!empty($usersAD)) {
		foreach($usersAD as $key=>$userAD) {
			$userObj = new stdClass();
			$userPrincipalName = $userAD['userPrincipalName'];
			$userObj->username = isset($userPrincipalName) ? $userPrincipalName : ' ';
			$userObj->firstname = isset($userAD['givenName']) ? $userAD['givenName'] : ' ';
			$userObj->lastname =  isset($userAD['surname']) ? $userAD['surname'] : ' ';
			$userObj->email =  isset($userAD['mail']) ? $userAD['mail'] : ' ';
			$userObj->department =  isset($userAD['department']) ? $userAD['department'] : ' ';
			$userObj->lang = 'es';
			$userObj->institution = 'azure';

			$user = $DB->get_record('user', array('username' => $userPrincipalName));

			if(empty($user) || !$user) {
				$userObj->auth       = 'manual';
				$userObj->confirmed  = 1;
				$userObj->mnethostid = 1;
				$userObj->id = $DB->insert_record('user', $userObj);

				$matriculas = $DB->get_records_sql("SELECT * FROM {urbanova_matricula} WHERE department = ? and isnew = 1", array($userObj->department));

				foreach ($matriculas as $matricula) {
					check_enrol($matricula->courseid, $userObj->id, 5);
				}

			} else {
				$userObj->id = $user->id;
				$userObj->deleted = 0;
				$DB->update_record('user', $userObj);

				$matriculas = $DB->get_records_sql("SELECT * FROM {urbanova_matricula} WHERE department = ? and isnew = 1", array($userObj->department));

				foreach ($matriculas as $matricula) {
					check_enrol($matricula->courseid, $userObj->id, 5);
				}
			}
		}
	}
}