<?php
/*------------------------------------------------------------*/
require_once("trackingConfig.php");
require_once(M_DIR."/mfiles.php");
require_once("trackingFiles.php");
/*------------------------------------------------------------*/
$ua = @$_SERVER['HTTP_USER_AGENT'];
if (
	! $ua
	|| stristr($ua, "bot")
	|| stristr($ua, "crawl")
	|| stristr($ua, "spider")
	) {
	http_response_code(204);
	exit;
}
/*------------------------------------------------------------*/
global $Mview;
global $Mmodel;
$Mview = new Mview;
$Mmodel = new Mmodel;
$Mview->holdOutput();
/*------------------------------------------------------------*/
$trackingLogin = new TrackingLogin;
$trackingLogin->enterSession();
$tracking = new Tracking;
$tracking->control();
$Mview->flushOutput();
/*------------------------------------------------------------*/
/*------------------------------------------------------------*/
