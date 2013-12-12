<?php
require_once("lib/autoload.php");

Options::setDebug(true);
try {
	$config = dirname(__FILE__)."/logmon.conf.php";
	$requiredConfigs = array($config);
	CheckConfig::configs($requiredConfigs);
	require_once($config);
	Options::setDebug(DEBUG);

	$requiredExtensions = array("mbstring", "PDO", "json");
	CheckConfig::extensions($requiredExtensions);

	mb_internal_encoding("UTF-8");
	Log::open(__FILE__, Options::debug(), false, Options::debug());
	$dbh = new DBH(DBDSN, DBUSER, DBPASS);
	$cmd = WebAccess::getRequest("cmd", false);
	switch($cmd) {
		case "viewevents":
			$view = new WebViewEvents($dbh);
			break;
		default:
			$view = new WebViewHostip($dbh);
	}
} catch(Exception $e) {
	Log::err($e);
	Log::close();
	if(Options::debug()) {
		WebAccess::reportExceptionAndExit($e);
	} else {
		WebAccess::sendStatusAndExit(WebAccess::STATUS_SERVICE_UNAVAILABLE);
	}
}
$view->printHtml();
if(isset($dbh)) {
	$dbh->close();
}
Log::close();
?>
