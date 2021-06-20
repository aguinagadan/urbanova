<?php
global $CFG;
global $DB;

require_once(dirname(__FILE__) . '/../../config.php');

$tenant  = 'estrategicaperu.onmicrosoft.com';
$authToken = 'Bearer access_token';
const AD_CLIENT_ID = '15694cd7-31c2-4b7c-acf8-f257c754d499';
const AD_CLIENT_SECRET = '30rhja.scIjTqcv_.~61-M2gSrzbvO71Z9';

// The data to send to the API
$postData = http_build_query(array(
	'grant_type' => 'client_credentials',
	'client_id' => AD_CLIENT_ID,
	'client_secret' => AD_CLIENT_SECRET,
	'scope' => 'https://graph.microsoft.com/.default'
), '', '&');

// Setup cURL
$ch = curl_init("https://login.windows.net/".$tenant."/oauth2/token");
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Authorization: '.$authToken,
		'Content-Type: application/json'
	),
	CURLOPT_POSTFIELDS => json_encode($postData)
));

var_dump($ch);
exit;


// Send the request
$response = curl_exec($ch);

// Check for errors
if($response === FALSE){
	die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);

print_r($responseData);
exit;

/*const AD_URL = 'estrategicaperu.onmicrosoft.com';
const AD_CLIENT_ID = '15694cd7-31c2-4b7c-acf8-f257c754d499';
const AD_CLIENT_SECRET = '30rhja.scIjTqcv_.~61-M2gSrzbvO71Z9';

function execCurl($data) {
	$curl = curl_init();

	$url = $data['url'];
	$postFields = $data['postFields'];
	$httpHeader = $data['httpHeader'];
	$httpMethod = $data['httpMethod'];

	$curlSetOptArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $httpMethod
	);

	if($httpMethod == 'POST') {
		$curlSetOptArray[CURLOPT_POSTFIELDS] = $postFields;
	}
	$curlSetOptArray[CURLOPT_HTTPHEADER] = $httpHeader;

	curl_setopt_array($curl, $curlSetOptArray);
	$response = curl_exec($curl);

	if (curl_errno($curl)) {
		print curl_error($curl);
	}

	curl_close($curl);
	$responseData = json_decode($response,true);
	return $responseData;
}
function getADToken() {
	$data = array(
		'url' => 'estrategicaperu.onmicrosoft.com',
		'postFields' =>
			array(
			'grant_type' => 'client_credentials',
			'client_id' => '15694cd7-31c2-4b7c-acf8-f257c754d499',
			'client_secret' => '30rhja.scIjTqcv_.~61-M2gSrzbvO71Z9',
			'scope' => 'https://graph.microsoft.com/.default'),
		'httpMethod' => 'POST',
		'httpHeader' => array(
			'Content-Type:application/x-www-form-urlencoded'
		)
	);

	$responseData = execCurl($data);
	var_dump($responseData['access_token']);
	exit;

	return $responseData['access_token'];
}
function getADUsers($key, $skipToken='') {
	if($key>0) {
		$skipToken = '&$skiptoken='.$skipToken;
	}

	$data = array(
		'url' => 'https://graph.microsoft.com/v1.0/users?$select=businessPhones,displayName,givenName,jobTitle,mail,mobilePhone,officeLocation,surname,userPrincipalName,id,department,faxNumber,employeeID,postalCode,companyName,city'.$skipToken,
		'httpMethod' => 'GET',
		'httpHeader' => array("Authorization: ". getADToken())
	);
	$responseData = execCurl($data);
	return $responseData;
}

$key = 0;
$skipToken = '';
$usersAD = array();
$allUsers = array();

while(true) {
	if($key>1 && $skipToken=='') {
		break;
	}
	$allUsers[] = getADUsers($key, $skipToken);

	$needle = '$skiptoken=';
	$skipToken = substr($allUsers[$key]['@odata.nextLink'], strpos($allUsers[$key]['@odata.nextLink'], $needle) + strlen($needle));
	$key++;
}

foreach($allUsers as $allUser) {
	foreach($allUser['value'] as $key=>$val) {
		$usersAD[$count] = $val;
		$count++;
	}
}

echo '<pre>';
var_dump($usersAD[0]);
exit;

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
}*/