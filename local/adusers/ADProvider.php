<?php

namespace azure\provider;

use moodleconstants\Constants;

class ADProvider {

	public function __construct() {
	}

	private static function execCurl($data) {
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

	private static function getToken($scope, $grantType) {
		$url = 'https://login.microsoftonline.com/'. Constants::AD_URL .'/oauth2/v2.0/token';
		$fields = array(
			"client_id" => Constants::AD_CLIENT_ID,
			"client_secret" => Constants::AD_CLIENT_SECRET,
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
		return $result->access_token;
	}

	private static function getADUsersRaw($key, $skipToken) {
		if($key>0) {
			$skipToken = '&$skiptoken='.$skipToken;
		}

		$data = array(
			'url' => 'https://graph.microsoft.com/v1.0/users'.$skipToken,
			'httpMethod' => 'GET',
			'httpHeader' => array("Authorization: ". self::getToken(Constants::SCOPE, Constants::GRANT_TYPE_CLIENT_CREDENTIALS))
		);
		$responseData = self::execCurl($data);
		return $responseData;
	}

	public static function getUsers() {
		$key = 0;
		$skipToken = '';
		$usersAD = array();
		$allUsers = array();

		while(true) {
			if($key>1 && $skipToken=='') {
				break;
			}
			$allUsers[] = self::getADUsersRaw($key, $skipToken);

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

		return $allUsers;
	}

}