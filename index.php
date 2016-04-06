<?php
mb_internal_encoding("UTF-8");

//autoinclude classes...
function autoloadFunction($class) {
	//is it ending "Controller"...
	if (preg_match('/Controller$/', $class))
		require("controllers/".$class.".php"); //...or is it model
	else
		require("models/".$class.".php");
}

//...and automatic registration of that classes
spl_autoload_register("autoloadFunction");

//load settings
if (file_exists('config.php'))
	require_once('config.php'); else require_once('default_config.php');
session_start();

//connnect to DB
try {
	if ($useProduction)
		Db::connect(DB_SERVER_PROD, DB_LOGIN_PROD, DB_PASSWORD_PROD, DB_DATABASE_PROD); else Db::connect(DB_SERVER_LOCAL, DB_LOGIN_LOCAL, DB_PASSWORD_LOCAL, DB_DATABASE_LOCAL);
} catch (PDOException $e) {
	if (!$useProduction)
		echo $e->getMessage();
	require("views/DBerror.html");
	die();
}

$router = new RouterController('en'); //set default language
$router->process([$_SERVER['REQUEST_URI']]);
$router->render();