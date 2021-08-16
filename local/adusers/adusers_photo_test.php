<?php
require_once(dirname(__FILE__) . '/../../config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

global $USER, $OUTPUT, $PAGE;


function execCurlPhoto($data) {
	$curl = curl_init();

	$url = $data['url'];
	$postFields = $data['postFields'] ?? null;
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
	return base64_encode($response);

}

function getTokenPhoto($scope, $grantType) {
	$fields_string = '';
	$url = 'https://login.microsoftonline.com/'. 'estrategicaperu.onmicrosoft.com' .'/oauth2/v2.0/token';
	$fields = array(
		"client_id" => '15694cd7-31c2-4b7c-acf8-f257c754d499',
		"client_secret" => '30rhja.scIjTqcv_.~61-M2gSrzbvO71Z9',
		"scope" => $scope,
		"grant_type" => $grantType
	);

	// For each API Url field
	foreach($fields as $key=>$value) {
		$fields_string .= $key . "=" . $value . "&";
	}

	// Trim, prep query string
	rtrim($fields_string, "&");

	// Make HTTP request
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	$result = curl_exec($ch);
	$result = json_decode($result);
	curl_close($ch);

	return $result;
}

function getUsersPhoto() {
	$accessToken = getTokenPhoto('https://graph.microsoft.com/.default','client_credentials')->access_token;

	$data = array(
		'url' => 'https://graph.microsoft.com/v1.0/users/mvargas@urbanova.com.pe/photo/$value',
		'httpMethod' => 'GET',
		'httpHeader' => array("Authorization: ". $accessToken)
	);

	$responseData = execCurlPhoto($data);
	return $responseData;
}

$photo = getUsersPhoto();
var_dump('<img src="data:image/png;base64, ' . getUsersPhoto() . ' " />');

var_dump($OUTPUT->user_picture($USER));
exit;