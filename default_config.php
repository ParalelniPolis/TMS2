<?php
if ($_SERVER['SERVER_NAME'] == 'localhost')
	$useProduction = false;
else
	$useProduction = true;

//---------------------------------------------------------------
// you may delete 'default_' prefix for custom .gitignored config
//---------------------------------------------------------------

//TODO - make nice installation script (where all this will be set)

//----------------------------------------------------------------
// settings for TMS2 - please edit the second string on every line
//----------------------------------------------------------------

//main directory for TMS2
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
if ($useProduction)
	define('ROOT', $scheme.'://example.com/TMS2'); else define('ROOT', 'http://localhost/TMS2');
define('NAME', 'TMS2'); //how will be this system called in application
define('EMAIL_HUB_MANAGER', 'hub.manager@example.com'); //email on your Hub Manager
define('VERSION', '0.21'); //number of actual version for correct displaying
define('EMAIL', 'TMS2@example.com'); //default email for communication with users of TMS2
define('BRUTEFORCE_LOCKED_TIME', 1800); //in seconds; time how long login anti-brutforce system will be active; default is 1800 (half an hour)
define('BRUTEFORCE_NUMBER_OF_ATTEMPTS', 5); //max number of attempts before bruteforce send email and lock account; default is 5
define('CHANGE_PASS_TIME_VALIDITY', 1800); //in seconds; time how long will be link for changing password; default is 1800 (half an hour)
define('TOLERANCE_TIME_ON_SENDING_REMINDING_EMAILS', 7); //in days
define('SEND_TICKET_EMAILS', false); //set true when you want to copy all ticket messages into your email, else set false
define('ALLOW_MAKE_ADMIN', false); //default false - true is INSECURE!

//settings for payments
define('VAT_RATE_INVOICE_EXTRAS', 21);

//settings for locks
define('MASTER_LOCK_PASS', 'your_master_password');

//settings for dev database
define('DB_SERVER_LOCAL', 'db');
define('DB_LOGIN_LOCAL', 'root');
define('DB_PASSWORD_LOCAL', 'root');
define('DB_DATABASE_LOCAL', 'tms');

//settings for production database
define('DB_SERVER_PROD', 'localhost');
define('DB_LOGIN_PROD', 'your_login');
define('DB_PASSWORD_PROD', 'your_password');
define('DB_DATABASE_PROD', 'your_database');

//settings for BitcoinPay.com
define('BITCOINPAY_TOKEN', 'your_bitcoinpay_token');
define('BITOINPAY_CALLBACK_PASS', 'your_bitcoinpay_callback_password'); //it should be set in Settings->API

//settings for Fakturoid.cz
define('FAKTUROID_SLUG', 'your_slug'); //in Fakturoid know also as old subdomain
define('FAKTUROID_EMAIL', 'your_fakturoid_email');
define('FAKTUROID_API_KEY', 'your_api_key');
define('FAKTUROID_USER_AGENT', 'TMS2 ('.EMAIL.')');