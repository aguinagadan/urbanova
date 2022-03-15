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

	private function execCurl($data, $isPhoto=false) {
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
			//print curl_error($curl);
		}

		curl_close($curl);
		if(!$isPhoto) {
			return json_decode($response,true);
		} else {
			return $response;
		}
	}

	public function getToken($scope, $grantType, $isLogin=false, $params=array()) {
		$url = 'https://login.microsoftonline.com/'. $this->constants::AD_URL .'/oauth2/v2.0/token';
		$fields = array(
			"client_id" => $this->constants::AD_CLIENT_ID,
			"client_secret" => $this->constants::AD_CLIENT_SECRET,
			"scope" => $scope,
			"grant_type" => $grantType
		);

		if($isLogin) {
			$fields['code'] = $params['getCode'];
			$fields['redirect_uri'] = $params['redirectUri'];
		}

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

	private function getADUsersRaw($key, $skipToken) {
		if($key>0) {
			$skipToken = '&$skiptoken='.$skipToken;
		}

		$accessToken = $this->getToken($this->constants::SCOPE, $this->constants::GRANT_TYPE_CLIENT_CREDENTIALS)->access_token;

		$data = array(
			'url' => 'https://graph.microsoft.com/v1.0/users?$select=userPrincipalName,givenName,surname,mail,department'.$skipToken,
			'httpMethod' => 'GET',
			'httpHeader' => array("Authorization: ". $accessToken)
		);
		$responseData = $this->execCurl($data);
		return $responseData;
	}

	public function getADUserPhoto($username) {

		$accessToken = $this->getToken($this->constants::SCOPE, $this->constants::GRANT_TYPE_CLIENT_CREDENTIALS)->access_token;

		$data = array(
			'url' => 'https://graph.microsoft.com/v1.0/users/'. $username . '/photo/$value',
			'httpMethod' => 'GET',
			'httpHeader' => array("Authorization: ". $accessToken)
		);
		$responseData = $this->execCurl($data, true);
		return base64_encode($responseData);
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
				$val['mail'] = strtolower($val['mail']);
				$val['userPrincipalName'] = strtolower($val['userPrincipalName']);
				//si no es urbanova
				if (strpos($val['userPrincipalName'], '@urbanova.com.pe') === false) {
					continue;
				}
				$usersAD[$count] = $val;
				$count++;
			}
		}

		return $usersAD;
	}

}