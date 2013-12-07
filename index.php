<?php
require_once("lib/autoload.php");

$debug = true;
$handler = false;
try {
	$config = dirname(__FILE__)."/logmon.conf.php";
	$requiredConfigs = array($config);
	CheckConfig::configs($requiredConfigs);
	require_once($config);
	$debug = DEBUG;

	$requiredExtensions = array("mbstring", "PDO", "json");
	CheckConfig::extensions($requiredExtensions);

	mb_internal_encoding("UTF-8");
	Log::open(__FILE__, $debug, false, $debug);
	WebAccess::initSession();

	if(isset($_REQUEST["action"])) {
		$handler = true;
	}
} catch(Exception $e) {
	Log::err($e);
	Log::close();
	if($debug) {
		WebAccess::reportExceptionAndExit($e);
	} else {
		WebAccess::sendStatusAndExit(WebAccess::STATUS_SERVICE_UNAVAILABLE);
	}
}
if($handler != false) {
	Log::close();
} else {
	Log::close();
	WebAccess::redirectRootAndExit();
}
?>
