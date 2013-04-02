<?php
require('./config.php');

define('WEB_ROOT','/web/tonglierp');
define('WWW_PATH','http://test.heavenk.com');

define('APP_NAME','Erp');
define('APP_PATH','./Erp/');



define('THINK_PATH','./ThinkPHP/');

define('APP_DEBUG', true);		// Use debug 

require(APP_PATH.'Common/Function.php');
require(THINK_PATH.'ThinkPHP.php');

?>