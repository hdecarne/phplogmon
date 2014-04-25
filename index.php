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
	Options::setKioskMode(ENABLE_ANONYMOUS_KIOSKMODE && (!isset($_SERVER["REMOTE_USER"]) || $_SERVER["REMOTE_USER"] == ""));
	$dbh = new DBH(DBDSN, DBUSER, DBPASS);
	$cmd = WebAccess::getRequest("cmd", false);
	switch($cmd) {
		case "viewservices":
			$access = new WebViewServices($dbh);
			break;
		case "viewservice":
			$access = new WebViewService($dbh);
			break;
		case "viewhostips":
			$access = new WebViewHostips($dbh);
			break;
		case "viewhostip":
			$access = new WebViewHostip($dbh);
			break;
		case "viewhostmacs":
			$access = new WebViewHostmacs($dbh);
			break;
		case "viewhostmac":
			$access = new WebViewHostmac($dbh);
			break;
		case "viewusers":
			$access = new WebViewUsers($dbh);
			break;
		case "viewuser":
			$access = new WebViewUser($dbh);
			break;
		case "viewevents":
			$access = new WebViewEvents($dbh);
			break;
		case "viewabout":
			$access = new WebViewAbout($dbh);
			break;
		case "streamlogs":
			$access = new WebStreamLogs($dbh);
			break;
		default:
			$access = new WebViewServices($dbh);
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
$access->sendResponse();
if(isset($dbh)) {
	$dbh->close();
}
Log::close();
?>
