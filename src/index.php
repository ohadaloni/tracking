<?php
/*------------------------------------------------------------*/
require_once("trackingConfig.php");
require_once(M_DIR."/mfiles.php");
require_once("trackingFiles.php");
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
