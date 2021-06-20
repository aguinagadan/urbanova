<?php
require_once(dirname(__FILE__) . '/../../config.php');

//autoload resources
spl_autoload_register(function ($clase) {
	include dirname(__FILE__) . '/../../msintegration/' . $clase . '.php';
});

$provider = new AzureProvider();
echo '<pre>';
var_dump(json_encode($provider->getUsers(),1));
exit;