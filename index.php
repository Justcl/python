<?php
//phpinfo();die;
define('APPLICATION_PATH', dirname(__FILE__));
define("APP_HOST","http://www.yaf.com/public/");
define("APP_NAME",'yaf_dev');
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
$application->bootstrap()->run();

