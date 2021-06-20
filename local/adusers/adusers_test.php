<?php
require_once(dirname(__FILE__) . '/../../config.php');

spl_autoload_register(function ($clase) {
	include $clase . '.php';
});

$provider = new ADProvider();
var_dump($provider->getUsers());
exit;

/*
const AD_URL = 'estrategicaperu.onmicrosoft.com';
const AD_CLIENT_ID = '15694cd7-31c2-4b7c-acf8-f257c754d499';
const AD_CLIENT_SECRET = '30rhja.scIjTqcv_.~61-M2gSrzbvO71Z9';
const SCOPE = 'https://graph.microsoft.com/.default';

// Build API Token Url
$url = "https://login.microsoftonline.com/" . AD_URL . "/oauth2/v2.0/token";
// Url passed parameters
$fields = array(
	"client_id" => AD_CLIENT_ID,
	"client_secret" => AD_CLIENT_SECRET,
	"scope" => SCOPE,
	"grant_type" => "client_credentials"
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

	var_dump($result->access_token);
	exit;

/*const AD_URL = 'estrategicaperu.onmicrosoft.com';
const AD_CLIENT_ID = '15694cd7-31c2-4b7c-acf8-f257c754d499';
const AD_CLIENT_SECRET = '30rhja.scIjTqcv_.~61-M2gSrzbvO71Z9';

function execCurl2($data) {
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

$data = array(
	'url' => AD_URL,
	'postFields' => http_build_query(array(
		'grant_type' => 'client_credentials',
		'client_id' => AD_CLIENT_ID,
		'client_secret' => AD_CLIENT_SECRET,
		'scope' => 'https://graph.microsoft.com/.default'), '', '&'),
	'httpMethod' => 'POST',
	'httpHeader' => array(
		'host: login.microsoftonline.com',
		'Content-Type: application/x-www-form-urlencoded',
		'Cookie: buid=0.AQYASG_it5IiFEqjVRrrhImuPRgFD1jTGCJMjrrTt_PN72QGAAA.AQABAAEAAAAGV_bv21oQQ4ROqh0_1-tAW_7_lkPgDNNQcc9ndJ6-VT_fKycsxUQA_fsiaenVHh0m1dZmFiOVou0VgVUcdSWQcKUXNWy0yeSTtMjrE4vBvIZsvOjiuXWYPgfnevpPNZAgAA; fpc=AlKOys_Nd-FDqTUucSXhED6Lv60HAQAAAEAZ29YOAAAA; x-ms-gateway-slice=estsfd; stsservicecookie=estsfd')
);

$responseData = execCurl2($data);
var_dump($responseData);
exit;

return 'eyJ0eXAiOiJKV1QiLCJub25jZSI6ImFrWlFkeF9kYm1nT012dmdMRU4tWmdya3BSS2owVXRmNEptOTdZNUZZZ2ciLCJhbGciOiJSUzI1NiIsIng1dCI6Im5PbzNaRHJPRFhFSzFqS1doWHNsSFJfS1hFZyIsImtpZCI6Im5PbzNaRHJPRFhFSzFqS1doWHNsSFJfS1hFZyJ9.eyJhdWQiOiJodHRwczovL2dyYXBoLm1pY3Jvc29mdC5jb20iLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8zZTAyODljMi1lMzUxLTRjNzktOTk4Yi04MTY3YzgzYTA2ZDMvIiwiaWF0IjoxNjI0MjE0MzE5LCJuYmYiOjE2MjQyMTQzMTksImV4cCI6MTYyNDIxODIxOSwiYWlvIjoiRTJaZ1lIaC9YSjVySStlbHJYZm1DajZOWXJ6TkJ3QT0iLCJhcHBfZGlzcGxheW5hbWUiOiJBdWxhIFZpcnR1YWwgVXJiYW5vdmEiLCJhcHBpZCI6IjE1Njk0Y2Q3LTMxYzItNGI3Yy1hY2Y4LWYyNTdjNzU0ZDQ5OSIsImFwcGlkYWNyIjoiMSIsImlkcCI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzNlMDI4OWMyLWUzNTEtNGM3OS05OThiLTgxNjdjODNhMDZkMy8iLCJpZHR5cCI6ImFwcCIsIm9pZCI6IjVjNTY5Y2E1LTEwOWQtNDI1Zi04MzZmLTRjMzUyMDRkOTQ3YyIsInJoIjoiMC5BUVlBd29rQ1BsSGplVXlaaTRGbnlEb0cwOWRNYVJYQ01YeExyUGp5VjhkVTFKa0dBQUEuIiwicm9sZXMiOlsiRGlyZWN0b3J5LlJlYWQuQWxsIiwiVXNlci5SZWFkLkFsbCJdLCJzdWIiOiI1YzU2OWNhNS0xMDlkLTQyNWYtODM2Zi00YzM1MjA0ZDk0N2MiLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiU0EiLCJ0aWQiOiIzZTAyODljMi1lMzUxLTRjNzktOTk4Yi04MTY3YzgzYTA2ZDMiLCJ1dGkiOiJGME5FN2ZrOU5FTzRRSVhlbTJvRkFRIiwidmVyIjoiMS4wIiwid2lkcyI6WyIwOTk3YTFkMC0wZDFkLTRhY2ItYjQwOC1kNWNhNzMxMjFlOTAiXSwieG1zX3RjZHQiOjEzNjAxMTAxNzd9.Qs-fHa_fwMJOMwbxPa5Dc33ZgDv9Ynp1EijOQ3DcgEGGQq1z4K7uiqdopBt-N9nvcaAAGrxCVmyK-uZnS6bn5jDX0JarhDEwFZCgxYNzWWmwf2PJLyOpE6Kmo7e25HVcIbNTnMGXih_A2rwOJ6U3oapxsXepxA9lGTw2M6HdzN47_huUkFDZf1G5MlwPsPu5BJJO7hTHAy81Xuj7xsLGy2nFY3bx7SSUu2mboI8SX-7hjNd0RjBEUhhHeOHTExEPlPQwOYVHJzyh2IQr1yiK8zy-3pmRfaKjXmt79xqKLh0GZveZC_d7fTfFvLMpDHVg_Y6ZOeUBmhwZ9jPAEyRuBw';
*/