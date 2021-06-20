<?php
global $CFG;
global $DB;

require_once(dirname(__FILE__) . '/../../config.php');

const AD_URL = 'estrategicaperu.onmicrosoft.com';
const AD_CLIENT_ID = '15694cd7-31c2-4b7c-acf8-f257c754d499';
const AD_CLIENT_SECRET = '75dda166-0df1-407a-96da-fbf240967738';

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
		'url' => AD_URL,
		'postFields' => http_build_query(array(
			'grant_type' => 'client_credentials',
			'client_id' => AD_CLIENT_ID,
			'client_secret' => AD_CLIENT_SECRET,
			'scope' => 'https://graph.microsoft.com/.default'), '', '&'),
		'httpMethod' => 'POST',
		'httpHeader' => array('host: login.microsoftonline.com',
			'Content-Type: application/x-www-form-urlencoded',
			'Cookie: buid=0.AQYASG_it5IiFEqjVRrrhImuPRgFD1jTGCJMjrrTt_PN72QGAAA.AQABAAEAAAAGV_bv21oQQ4ROqh0_1-tAW_7_lkPgDNNQcc9ndJ6-VT_fKycsxUQA_fsiaenVHh0m1dZmFiOVou0VgVUcdSWQcKUXNWy0yeSTtMjrE4vBvIZsvOjiuXWYPgfnevpPNZAgAA; fpc=AlKOys_Nd-FDqTUucSXhED6Lv60HAQAAAEAZ29YOAAAA; x-ms-gateway-slice=estsfd; stsservicecookie=estsfd')
	);

	$responseData = execCurl($data);
	var_dump($responseData);
	exit;

	return 'eyJ0eXAiOiJKV1QiLCJub25jZSI6ImFrWlFkeF9kYm1nT012dmdMRU4tWmdya3BSS2owVXRmNEptOTdZNUZZZ2ciLCJhbGciOiJSUzI1NiIsIng1dCI6Im5PbzNaRHJPRFhFSzFqS1doWHNsSFJfS1hFZyIsImtpZCI6Im5PbzNaRHJPRFhFSzFqS1doWHNsSFJfS1hFZyJ9.eyJhdWQiOiJodHRwczovL2dyYXBoLm1pY3Jvc29mdC5jb20iLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8zZTAyODljMi1lMzUxLTRjNzktOTk4Yi04MTY3YzgzYTA2ZDMvIiwiaWF0IjoxNjI0MjE0MzE5LCJuYmYiOjE2MjQyMTQzMTksImV4cCI6MTYyNDIxODIxOSwiYWlvIjoiRTJaZ1lIaC9YSjVySStlbHJYZm1DajZOWXJ6TkJ3QT0iLCJhcHBfZGlzcGxheW5hbWUiOiJBdWxhIFZpcnR1YWwgVXJiYW5vdmEiLCJhcHBpZCI6IjE1Njk0Y2Q3LTMxYzItNGI3Yy1hY2Y4LWYyNTdjNzU0ZDQ5OSIsImFwcGlkYWNyIjoiMSIsImlkcCI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzNlMDI4OWMyLWUzNTEtNGM3OS05OThiLTgxNjdjODNhMDZkMy8iLCJpZHR5cCI6ImFwcCIsIm9pZCI6IjVjNTY5Y2E1LTEwOWQtNDI1Zi04MzZmLTRjMzUyMDRkOTQ3YyIsInJoIjoiMC5BUVlBd29rQ1BsSGplVXlaaTRGbnlEb0cwOWRNYVJYQ01YeExyUGp5VjhkVTFKa0dBQUEuIiwicm9sZXMiOlsiRGlyZWN0b3J5LlJlYWQuQWxsIiwiVXNlci5SZWFkLkFsbCJdLCJzdWIiOiI1YzU2OWNhNS0xMDlkLTQyNWYtODM2Zi00YzM1MjA0ZDk0N2MiLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiU0EiLCJ0aWQiOiIzZTAyODljMi1lMzUxLTRjNzktOTk4Yi04MTY3YzgzYTA2ZDMiLCJ1dGkiOiJGME5FN2ZrOU5FTzRRSVhlbTJvRkFRIiwidmVyIjoiMS4wIiwid2lkcyI6WyIwOTk3YTFkMC0wZDFkLTRhY2ItYjQwOC1kNWNhNzMxMjFlOTAiXSwieG1zX3RjZHQiOjEzNjAxMTAxNzd9.Qs-fHa_fwMJOMwbxPa5Dc33ZgDv9Ynp1EijOQ3DcgEGGQq1z4K7uiqdopBt-N9nvcaAAGrxCVmyK-uZnS6bn5jDX0JarhDEwFZCgxYNzWWmwf2PJLyOpE6Kmo7e25HVcIbNTnMGXih_A2rwOJ6U3oapxsXepxA9lGTw2M6HdzN47_huUkFDZf1G5MlwPsPu5BJJO7hTHAy81Xuj7xsLGy2nFY3bx7SSUu2mboI8SX-7hjNd0RjBEUhhHeOHTExEPlPQwOYVHJzyh2IQr1yiK8zy-3pmRfaKjXmt79xqKLh0GZveZC_d7fTfFvLMpDHVg_Y6ZOeUBmhwZ9jPAEyRuBw';
}
function getADUsers($key, $skipToken='') {
	if($key>0) {
		$skipToken = '&$skiptoken='.$skipToken;
	}

	$data = array(
		'url' => 'https://graph.microsoft.com/v1.0/users'.$skipToken,
		'httpMethod' => 'GET',
		'httpHeader' => array("Authorization: ". getADToken())
	);
	$responseData = execCurl($data);
	var_dump($responseData);
	exit;
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
}