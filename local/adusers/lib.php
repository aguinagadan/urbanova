<?php
global $CFG;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

//autoload resources
spl_autoload_register(function ($clase) {
	global $CFG;
	include $CFG->dirroot.'/msintegration/' . $clase . '.php';
});

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
			$userObj->lang = 'es';
			$userObj->institution = 'azure';

			$user = $DB->get_record('user', array('username' => $userPrincipalName));

			if(empty($user) || !$user) {
				$userObj->auth       = 'manual';
				$userObj->confirmed  = 1;
				$userObj->mnethostid = 1;
				$userObj->id = $DB->insert_record('user', $userObj);
			} else {
				$userObj->id = $user->id;
				$userObj->deleted = 0;
				$DB->update_record('user', $userObj);
			}
		}
	}
}