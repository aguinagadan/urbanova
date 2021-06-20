<?php

//autoload resources
spl_autoload_register(function ($clase) {
	include dirname(__FILE__) . '/../local/' . $clase . '.php';
});

class AzureProvider {

	private $constants;

	public function __construct() {
		$this->constants = new Constants();
	}

	private function execCurl($data) {
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

	private function getToken($scope, $grantType) {
		$url = 'https://login.microsoftonline.com/'. $this->constants::AD_URL .'/oauth2/v2.0/token';
		$fields = array(
			"client_id" => $this->constants::AD_CLIENT_ID,
			"client_secret" => $this->constants::AD_CLIENT_SECRET,
			"scope" => $scope,
			"grant_type" => $grantType
		);

		$fields_string = http_build_query($fields,'', '&');

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

		return $result->access_token;
	}

	private function getADUsersRaw($key, $skipToken) {
		if($key>0) {
			$skipToken = '&$skiptoken='.$skipToken;
		}

		$data = array(
			'url' => 'https://graph.microsoft.com/v1.0/users'.$skipToken,
			'httpMethod' => 'GET',
			'httpHeader' => array("Authorization: ". $this->getToken($this->constants::SCOPE, $this->constants::GRANT_TYPE_CLIENT_CREDENTIALS))
		);
		$responseData = $this->execCurl($data);
		return $responseData;
	}

	public function getUsers() {
		$key = 0;
		$skipToken = '';
		$usersAD = array();
		$allUsers = array();

		while(true) {
			if($key>1 && $skipToken=='') {
				break;
			}
			$allUsers[] = $this->getADUsersRaw($key, $skipToken);

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

		var_dump($allUsers[0]);
		exit;

		return $allUsers;
	}

}