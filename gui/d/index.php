<?php
require_once("../../lib/autoload.php");

try {
	$config = dirname(__FILE__)."/../../logmon.conf.php";
	$requiredConfigs = array($config);
	CheckConfig::configs($requiredConfigs);
	require_once($config);
	$debug = DEBUG;

	$requiredExtensions = array("mbstring", "PDO", "json");
	CheckConfig::extensions($requiredExtensions);

	mb_internal_encoding("UTF-8");
	Log::open(__FILE__, $debug, false, $debug);
	WebAccess::initSession();

	$lang = WebAccess::lang();
} catch(Exception $e) {
	Log::err($e);
	Log::close();
	if($debug) {
		WebAccess::reportExceptionAndExit($e);
    } else {
		WebAccess::sendStatusAndExit(WebAccess::STATUS_SERVICE_UNAVAILABLE);
	}
}
?>
<!DOCTYPE HTML>
<html lang="<?php Html::out($lang); ?>">
<head>
<meta charset="utf-8" />
<title>LogMon</title>
<link rel="stylesheet" type="text/css" href="<?php Html::out(EXTJS_STYLE_URL); ?>" />
<script type="text/javascript" src="<?php Html::out(EXTJS_SCRIPT_URL); ?>"></script>
<script type="text/javascript" src="app.js"></script>
</head>
<body>
</body>
</html>
