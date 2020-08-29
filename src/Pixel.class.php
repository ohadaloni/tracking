<?php
/*------------------------------------------------------------*/
class Pixel extends Tracking {
	/*------------------------------------------------------------*/
	private $uid;
	private $ttl;
	/*------------------------------------------------------------*/
	public function __construct() {
		parent::__construct();
		$this->ttl = 300;
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	protected function permit() {
		$this->uid = @$_REQUEST['uid'];
		if ( ! $this->uid )
			return(false);
		return(true);
	}
	/*------------------------------------------------------------*/
	public function index() {
		$this->send();
		$this->add2countByArray(array(
			'name' => @$_REQUEST['name'],
			'value' => @$_REQUEST['value'],
			'cid' => @$_REQUEST['cid'],
			'pid' => @$_REQUEST['pid'],
			'oid' => @$_REQUEST['oid'],
		));
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	// Sat Jun 27 12:49:16 IDT 2020
	// this is a first prototype of a transliterator
	// todo (vtd) describes doing this without any extra tables
	// a transilitirator can be totally elsewhere,
	// and just call the native pixel index() here
	public function mbUserAgent() {
		$this->send();
		$ua = @$_REQUEST['ua'];
		if ( ! $ua ) {
			$this->add2countByArgs("noMbUserAgent");
			return;
		}
		$str = $this->Mmodel->str($ua);
		$sql = "select * from userAgents where userAgent = '$str'";
		$row = $this->Mmodel->getRow($sql, $this->ttl);
		if ( $row ) {
			$id = $row['id'];
		} else {
			// Sat Jun 27 12:51:32 IDT 2020
			// can Q this in memcache and collect later
			// but as only new agents hit this, its no sweat
			$id = $this->Mmodel->dbInsert("userAgents", array(
				'userAgent' => $ua,
			));
		}
		$this->add2countByArray(array(
			'name' => "mbUserAgent",
			'oid' => $id,
		));
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	// Sat Jun 27 13:01:07 IDT 2020
	// set the relevant counter
	// the key is a cocncat of the args,
	// except the value is the double accumulator,
	// but if null then its a counter to be ++ed
	// Sat Jun 27 13:03:25 IDT 2020
	// need to somehow like in placementQ q uid-name sequences,
	// the collector can find this key
	// Sat Jun 27 13:04:18 IDT 2020
	// its a minute counter
	// time & uid are also part of the key.
	// maybe just Q it all, and then the collector just updates the db from the Q.
	// the Q length is reported on the dashboard,
	// even though it has all uid's
	/*------------------------------*/
	private function add2countByArgs($name, $value = null, $cid = null, $pid = null, $oid = null) {
		$this->add2countByArray(array(
			'name' => $name,
			'value' => $value,
			'cid' => $cid,
			'pid' => $pid,
			'oid' => $oid,
		));
	}
	/*------------------------------------------------------------*/
	private function add2countByArray($a) {
		$more = array(
			'uid' => $this->uid,
			'date' => date("Y-m-d"),
			'hour' => date("G"),
			'minute' => date("i"),
		);
		$qItem = array_merge($more, $a);
		// Sat Aug 29 11:54:44 IDT 2020
		// bot hits
		/*	$this->Mmemcache->msgQadd("trackingPixel", $qItem);	*/
		$json = json_encode($qItem);
		error_log("(not) Queueing: $json");
	}
	/*------------------------------------------------------------*/
	private function send() {
		$this->trackingUtils->sendPixel();
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
}
