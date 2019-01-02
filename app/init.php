<?php
define("SQL_HOST", "localhost");
define("SQL_USER", "root");
define("SQL_PASS", "victor");
define("SQL_DB", "uganda_budeshi");
define("ABS_PATH", "http://localhost/uganda/");
define("FILE_ROOT", "C:/xampp/htdocs/uganda/");
define("WEB_ROOT", "C:/xampp/htdocs/uganda/");
define("OC_PREFIX", "azam7x");
define("RELEASE_PATH", ABS_PATH ."Raw/");
define("APP_PATH", FILE_ROOT."app/");
define("CONTROLLERS", APP_PATH."controllers/");///path to your controllers
define("MODELS", APP_PATH."models/");
define("VIEWS", APP_PATH."views/");
define('HELPERS', APP_PATH."helpers/");

define("COMPILED_PATH", FILE_ROOT."app/compiled/");
define("DATA_DIR","data/");
define("PERPAGE", 10);
define("REPORT_PATH", FILE_ROOT. 'reports/');
define("MONITORING_PATH", FILE_ROOT.'images/monitoring/');

require_once("core/App.php");
require_once("core/Controller.php");
require_once("core/Model.php");
?>
