<?php
require_once("lib/autoload.php");

try {
	$config = dirname(__FILE__)."/logmon.conf.php";
	$requiredConfigs = array($config);
	CheckConfig::configs($requiredConfigs);
	require_once($config);
	$requiredExtensions = array("mbstring", "PDO");
	CheckConfig::extensions($requiredExtensions);

	mb_internal_encoding("UTF-8");

	Log::open(__FILE__, DEBUG, false, DEBUG);

	$dbh = new DBH(DBDSN, DBUSER, DBPASS);
	$view = View::match($dbh);
} catch(Exception $e) {
	Log::err(sprintf("Request processing failed with exception: %s\nDetails: %s", $e->getMessage(), $e));
	$view = new ViewError($e);
}

?>
<!DOCTYPE HTML>
<html lang="<?php print $view->lang(); ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width" />
<title><?php $view->renderTitle(); ?></title>
<link rel="stylesheet" type="text/css" href="<?php print $view->stylesheet(); ?>" />
</head>
<body>
<?php $view->renderBody(); ?>

<hr>
<address>
<?php
$signature = Version::signature();
if(isset($_SERVER["REQUEST_TIME_FLOAT"])) {
	$signature .= " (";
	$elapsed = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3);
	$signature .= $elapsed;
	$signature .= " s)";
}
print htmlentities($signature);
?>

</address>
</body>
</html>
<?php
if(isset($dbh)) {
	$dbh->close();
}
Log::close();
?>
