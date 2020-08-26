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
		$this->Mview->register_modifier("weekday", array("TrackingUtils", "weekday",));
		$this->Mview->register_modifier("terse", array("TrackingUtils", "terse",));
		$this->Mview->register_modifier("timeUnit", array("TrackingUtils", "timeUnit",));
		$this->Mview->register_modifier("makeLinks", array("TrackingUtils", "makeLinks",));
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
	public static function terse($str, $numWords = 7) {
		$cnt = strlen($str);
		$words = explode(" ", $str);
		$cnt = count($words);
		if ( $cnt <= $numWords )
			return($str);
		$words = array_slice($words, 0, $numWords);
		$str = implode(" ", $words)." ...($cnt)";
		return($str);
	}
	/*------------------------------------------------------------*/
        // from:
        // http://krasimirtsonev.com/blog/article/php--find-links-in-a-string-and-replace-them-with-actual-html-link-tags
        //
        // if
        //        {$row.story|nl2br|makeLinks}
        // makeLinks sticks a br in the middle if the link title
        // so try
        //        {$row.story|makeLinks|nl2br}
        public static function makeLinks($str) {
                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                $urls = array();
                $urlsToReplace = array();
                if(preg_match_all($reg_exUrl, $str, $urls)) {
                        $numOfMatches = count($urls[0]);
                        $numOfUrlsToReplace = 0;
                        for($i=0; $i<$numOfMatches; $i++) {
                                $alreadyAdded = false;
                                $numOfUrlsToReplace = count($urlsToReplace);
                                for($j=0; $j<$numOfUrlsToReplace; $j++) {
                                        if($urlsToReplace[$j] == $urls[0][$i]) {
                                                $alreadyAdded = true;
                                        }
                                }
                                if(!$alreadyAdded) {
                                        array_push($urlsToReplace, $urls[0][$i]);
                                }
                        }
                        $numOfUrlsToReplace = count($urlsToReplace);
                        for($i=0; $i<$numOfUrlsToReplace; $i++) {
                                $str = str_replace($urlsToReplace[$i], "<a target=\"_blank\" href=\"".$urlsToReplace[$i]."\">".$urlsToReplace[$i]."</a> ", $str);
                        }
                        return $str;
                } else {
                        return $str;
                }
        }
	/*------------------------------------------------------------*/
	public static function sendPixel() {
		header("Content-type: image/gif");
		$onePixelPath = "../images/onePixel.gif";
		readfile($onePixelPath);
	}
	/*------------------------------------------------------------*/
}
