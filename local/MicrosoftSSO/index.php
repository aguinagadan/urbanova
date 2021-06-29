<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $DB;

//autoload resources
spl_autoload_register(function ($clase) {
	include dirname(__FILE__) . '/../../msintegration/' . $clase . '.php';
});

$params = array();
$params['getCode'] = $_GET['code'];
$params['redirectUri'] = 'https://aulavirtual.urbanova.com.pe/local/MicrosoftSSO/index.php';

// Set your redirect locations
$authenticated_url = 'https://aulavirtual.urbanova.com.pe';
$error_url = '';
$client_id = "15694cd7-31c2-4b7c-acf8-f257c754d499";
$client_space = "estrategicaperu.onmicrosoft.com";
$scopes = "User.Read";

if(isset($_GET["code"])) {

	$provider = new AzureProvider();
	$accessResult = $provider->getToken('User.Read', 'authorization_code', true, $params);

	// If result has Access Token
	if ($accessResult->access_token) {

		// Set cookies for access and refresh tokens
		setcookie("mg_sso_token", $accessResult->access_token, time() + 3600);
		setcookie("mg_sso_refresh_token", $accessResult->refresh_token, time() + 3600);

		// Make HTTP Request for Graph
		$url = "https://graph.microsoft.com/v1.0/me";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_HTTPHEADER, array("Authorization: bearer " . $accessResult->access_token, "Host: graph.microsoft.com"));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$user = curl_exec($ch);
		$profile = json_decode($user);
		curl_close($ch);

		// If user is authenticated
		if ($user && !empty($profile)) {
			// Set cookie for response
			setcookie("mg_sso_profile", $user, time() + 3600);

			$user = json_decode($user);
			$userObj = $DB->get_record('user', array('email'=>$user->mail));

			if(!$userObj) {
				$userObj = $DB->get_record('user', array('email'=>$user->userPrincipalName));
			}

			complete_user_login($userObj);

			header ("Location: " . $authenticated_url);

		} else {
			// Redirect user to error
			header ("Location: " . $error_url);

		}

	}

} else {
	//Redirect back to login
	header("Location: https://login.microsoftonline.com/" .
		$client_space . "/oauth2/v2.0/authorize?client_id=" .
		$client_id . "&scope=" .
		$scopes . "&resource_mode=query&response_type=code&redirect_uri=" . $params['redirectUri']);
}



