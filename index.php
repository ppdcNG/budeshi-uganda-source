<?php
//////Index PHP base file
////// Change the $init key parameters to suit your application
///// ****NOTE IT IS PREFERABLE TO MAKE ALL PATHS relative to this file i.e "index.php"
///// 1. $init["config_file_name"] = the name of your init file
///// 2. $init["app_folder_path"] = the path to the "app" folder Defaults 


$init["config_file_name"] = 'init.php';
$init["app_folder_path"] = '/app/';

define("INIT","app/init.php");
require_once(INIT);
$app = new App;

?>