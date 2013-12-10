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
	WebAccess::initSession();
	$dbh = new DBH(DBDSN, DBUSER, DBPASS);
} catch(Exception $e) {
	Log::err($e);
	Log::close();
	if(Options::debug()) {
		WebAccess::reportExceptionAndExit($e);
	} else {
		WebAccess::sendStatusAndExit(WebAccess::STATUS_SERVICE_UNAVAILABLE);
	}
}
$cmd = WebAccess::getRequest(WebAccess::REQUEST_CMD, false);
$view = new WebViewHostip($dbh);
$view->printHtml();
if(isset($dbh)) {
	$dbh->close();
}
Log::close();
?>
