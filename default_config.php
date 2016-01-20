<?php
$useProduction = false;
//--------------------------------------------------
//you may delete 'default_' prefix for custom config
//--------------------------------------------------

//---------------------------------------------------------------
//settings for TMS2 - please edit the second string on every line
//---------------------------------------------------------------

//main directory for TMS2
if ($useProduction) define('ROOT', 'http://yourserver.com/TMS2');
else define('ROOT', 'http://localhost/TMS2');
define('NAME', 'TMS2'); //how will be this system called in application
define('VERSION', '0.13'); //number of actual version for correct displaying
define('EMAIL', 'TMS2@yourdomain.com'); //default email for communication with users of TMS2
define('BRUTEFORCE_LOCKED_TIME', 1800); //in seconds; time how long login anti-brutforce system will be active; default is 1800 (half an hour)
define('BRUTEFORCE_NUMBER_OF_ATTEMPTS', 5); //max number of attempts before bruteforce send email and lock account; default is 5
define('CHANGE_PASS_TIME_VALIDITY', 1800); //in seconds; time how long will be link for changing password; default is 1800 (half an hour)
define('ALLOW_MAKE_ADMIN', false); //default false - true is INSECURE!

//settings for localhost database
define('DB_SERVER_LOCAL', 'localhost');
define('DB_LOGIN_LOCAL', 'root');
define('DB_PASSWORD_LOCAL', '');
define('DB_DATABASE_LOCAL', 'tms');

//settings for production database
define('DB_SERVER_PROD', 'localhost');
define('DB_LOGIN_PROD', 'your_login');
define('DB_PASSWORD_PROD', 'your_password');
define('DB_DATABASE_PROD', 'your_database');

//settings for BitcoinPay
define('BITCOINPAY_TOKEN', 'your_bitcoinpay_token');

//settings for Fakturoid
define('FAKTUROID_SLUG', 'your_slug'); //in Fakturoid know also as old subdomain
define('FAKTUROID_EMAIL', 'your_fakturoid_email');
define('FAKTUROID_API_KEY', 'your_api_key');
define('FAKTUROID_USER_AGENT', 'TMS2 ('.EMAIL.')');