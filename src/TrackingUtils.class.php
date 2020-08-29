<?php
/*------------------------------------------------------------*/
class TrackingUtils extends Mcontroller {
	/*------------------------------------------------------------*/
	private $loginName;
	private $loginType;
	private $loginId;
	private $ttl;
	/*------------------------------------------------------------*/
	public function __construct() {
		parent::__construct();

		$this->ttl = 30;
	}
	/*------------------------------------------------------------*/
	public function prior($controller, $action, $loginName, $loginType, $loginId) {
		$this->loginName = $loginName;
		$this->loginType = $loginType;
		$this->loginId = $loginId;
		$this->Mview->assign(array(
			'controller' => $controller,
			'action' => $action,
			'loginName' => $this->loginName,
			'loginType' => $this->loginType,
			'loginId' => $this->loginId,
		));
		$this->registerFilters();
	}
	/*------------------------------*/
	private function registerFilters() {
		$this->Mview->register_modifier("numberFormat", array("Mutils", "numberFormat",));
		$this->Mview->register_modifier("terse", array("Mutils", "terse",));
		$this->Mview->register_modifier("makeLinks", array("Mutils", "makeLinks",));
		$this->Mview->register_modifier("timeUnit", array("TrackingUtils", "timeUnit",));
		$this->Mview->register_modifier("weekday", array("TrackingUtils", "weekday",));
		$this->Mview->register_modifier("monthlname", array("Mdate", "monthlname",));
	}
	/*------------------------------------------------------------*/
	public static function timeUnit($timeSlot) {
		$timeUnits = array(
			'thisMinute' => 'minute',
			'thisHour' => 'hour',
			'today' => 'day',
			'thisMonth' => 'month',
			'thisYear' => 'year',
			'allTime' => 'allTime',
		);
		return($timeUnits[$timeSlot]);

	}
	/*------------------------------------------------------------*/
	public static function sendPixel() {
		header("Content-type: image/gif");
		$onePixelPath = "../images/onePixel.gif";
		readfile($onePixelPath);
	}
	/*------------------------------------------------------------*/
}
