<?php
/*------------------------------------------------------------*/
class Tracking extends Mcontroller {
	/*------------------------------------------------------------*/
	protected $loginName;
	protected $loginId;
	protected $loginType;
	/*------------------------------*/
	protected $trackingUtils;
	protected $Mmemcache;
	/*------------------------------*/
	private $startTime;
	/*------------------------------------------------------------*/
	public function __construct() {
		parent::__construct();

		// permit is called before before()
		// and if fails, before is not called.
		$this->loginId = TrackingLogin::loginId();
		$this->loginName = TrackingLogin::loginName();
		$this->loginType = TrackingLogin::loginType();

		$this->Mmemcache = new Mmemcache;
		Mutils::setenv("debugLevel", 1);
		$topDir = dirname(__DIR__);
		$logsDir = "$topDir/logs/tracking";
		$today = date("Y-m-d");
		$logFileName = "tracking.$today.log";
		$logFile = "$logsDir/$logFileName";
		$this->logger = new Logger($logFile);
		$this->trackingUtils = new TrackingUtils;
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	protected function permit() {
		if ( $this->loginId )
			return(true);
		// api is free of login credentials except the uid, which is sparse
		if ( in_array($this->controller, array('tracking', 'pixel', 'api',) ) )
			return(true);
		return(false);
	}
	/*------------------------------------------------------------*/
	protected function before() {
		ini_set('max_execution_time', 10);
		ini_set("memory_limit", "5M");

		$controller = $this->controller;
		$action = $this->action;
		if ( $controller == 'pixel' )
			return;
		$this->trackingUtils->prior($this->controller, $this->action, $this->loginName, $this->loginType, $this->loginId);
		$this->startTime = microtime(true);
		$this->Mview->assign(array(
			'controller' => $this->controller,
			'action' => $this->action,
		));
		if ( $this->showMargins()) {
			$this->Mview->showTpl("head.tpl");
			$this->Mview->showTpl("header.tpl");
			if ( $this->loginName ) {
				$menu = new Menu;
				$menu->index();
			}
			$this->Mview->showMsgs();
		}
		$method = @$_SERVER['REQUEST_METHOD'];
		if ( $method == "GET" ) {
			$url = @$_SERVER['REQUEST_URI'];
			if ( $this->redirectable($url) ) {
				$this->Mview->setCookie("lastVisit", $url);
			}
		}
	}
	/*------------------------------*/
	protected function after() {
		if ( ! $this->showMargins())
			return;
		$this->Mview->runningTime($this->startTime);
		$this->Mview->showTpl("footer.tpl");
		$this->Mview->showTpl("foot.tpl");
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	public function index() {
		$loginId = $this->loginId ;
		if ( $loginId ) {
			$sql = "select landHere from users where id = $loginId";
			$landHere = $this->Mmodel->getString($sql);
			if ( $this->redirectable($landHere) ) {
				$this->redirect($landHere);
				return;
			}
			$lastVisit = @$_COOKIE['lastVisit'];
			if ( $this->redirectable($lastVisit) ) {
				$this->redirect($lastVisit);
				return;
			}
			$this->redirect("/dashboard");
		}  else {
			$this->Mview->showTpl("login.tpl");
		}
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	public function landHere() {
		$referer = $_SERVER['HTTP_REFERER'];
		$parts = explode("/", $referer, 4);
		$landHere = "/".$parts[3];
		$affected = $this->dbUpdate("users", $this->loginId, array(
			'landHere' => $landHere,
		));
		$this->Mview->tell("landHere page set to $landHere", array(
			'rememberForNextPage' => true,
		));
		$this->redirect($landHere);
	}
	/*------------------------------------------------------------*/
	public function unland() {
		$affected = $this->dbUpdate("users", $this->loginId, array(
			'landHere' => null,
		));
		$this->Mview->tell("landHere page set to auto", array(
			'rememberForNextPage' => true,
		));
		$this->redirect("/");
	}
	/*------------------------------------------------------------*/
	public function register() {
		$email = $_REQUEST['email'];
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->Mview->msg("register: '$email': Not an email");
			return;
		}
		require_once(M_DIR."/MmailJet.class.php");
		$m = new MmailJet;
		$httpCode = null;

		$key = sha1(rand(1000, 1000000));
		$cr = sha1($email);
		$mkey = "RegisterEmail-$key";
		$ttl = 900;
		$this->Mmemcache->set($mkey, $email, $ttl);
		$message = $this->Mview->render("registerEmail.tpl", array(
			'key' => $key,
			'cr' => $cr,
		));
		$m->mail($email, "Create Account @ tracking.theora.com", $message, $httpCode);
		if ( $httpCode == 200 )
			$this->Mview->msg("Please click the link in the email to complete the registration");
		else
			$this->Mview->error("Email error");
	}
	/*------------------------------*/
	public function registration() {
		$key = @$_REQUEST['key'];
		$cr = @$_REQUEST['cr'];
		if ( ! $key || ! $cr ) {
			$this->Mview->error("No key&cr");
			return;
		}
		$mkey = "RegisterEmail-$key";
		$email = $this->Mmemcache->get($mkey);
		if ( ! $email ) {
			$this->Mview->error("Expired");
			return;
		}
		$crcr = sha1($email);
		if ( $cr != $crcr ) {
			$this->Mview->error("Wrong email");
			return;
		}
		$str = $this->Mmodel->str($email);
		$sql = "select loginName from users where loginName = '$str'";
		$dbEmail = $this->Mmodel->getString($sql);
		if ( $dbEmail ) {
			$this->Mview->error("Email $email exists");
			return;
		}
		$sql = "select max(id) from users";
		$maxUid = $this->Mmodel->getInt($sql);
		if ( ! $maxUid )
			$maxUid = 0;
		$uid = $maxUid + rand(100, 1000);
		$rnd = rand(100, 1000);
		$sha1 = sha1($rnd);
		$passwd = substr($sha1, 17, 6);
		$dbPasswd = sha1($passwd);
		$data = array(
			'id' => $uid,
			'loginName' => $email,
			'passwd' => $dbPasswd,
		);
		$id = $this->Mmodel->dbInsert("users", $data, true);
		if ( $id != $uid ) {
			$this->Mview->error("insert failed");
			return;
		}
		$this->Mview->urlMsg("registration successful", "http://tracking.theora.com");
		$this->Mview->msg("password is $passwd");
	}
	/*------------------------------------------------------------*/
	public function forgotPass() {
		$email = $_REQUEST['email'];
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->Mview->msg("forgotPass: '$email': Not an email");
			return;
		}
		$str = $this->Mmodel->str($email);
		$sql = "select * from users where loginName = '$str'";
		$loginRec =  $this->Mmodel->getRow($sql);
		if ( ! $loginRec ){
			$this->Mview->error("forgotPass: Email Not Found");
			return;
		}

		$rnd = rand(100, 1000);
		$sha1 = sha1($rnd);
		$passwd = substr($sha1, 17, 6);
		$dbPasswd = sha1($passwd);

		$affected = $this->Mmodel->dbUpdate("users", $loginRec['id'], array(
			'passwd' => $dbPasswd,
		));
		if ( ! $affected ) {
			$this->Mview->error("forgotPass: update failed");
			return;
		}
		require_once(M_DIR."/MmailJet.class.php");
		$m = new MmailJet;
		$httpCode = null;

		$m->mail($email, "Forgot Password @ tracking.theora.com", $passwd, $httpCode);
		if ( $httpCode == 200 )
			$this->Mview->msg("New Password updated & sent. Please check your email.");
		else
			$this->Mview->error("Email error");
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	private function redirectable($url) {
		if ( ! $url )
			return(false);
		if ( $url == "/" )
			return(false);

		$parts = explode("?", $url);
		$parts = explode("/", $parts[0]);
		$pathParts = array();
		foreach ( $parts as $part )
			if ( $part != "" )
				$pathParts[] = $part;
		if ( ! $pathParts )
			$pathParts = array("tracking", "x");

		$className = $pathParts[0];
		$action = @$pathParts[1];
		$action = $action ? $action : "index";
		$nots = array(
			'pixel' => array(
				'any',
			),
			'api' => array(
				'any',
			),
			'tracking' => array(
				'unland',
				'changePasswd',
				'updatePasswd',
				'forgotPass',
				'register',
				'registration',
			),
		);
		foreach( $nots as $notClassName => $notClass )
			foreach( $notClass as $notAction )
				if ( strcasecmp($notClassName, $className) == 0
						&& 
						( strcasecmp($notAction, $action) == 0 || $notAction == 'any' )
					) {
						return(false);
					}

		$files = Mutils::listDir(".", "php");
		$baseName = null;
		foreach ( $files as $file ) {
			$fileParts = explode(".", $file);
			$baseName = reset($fileParts);
			if(strtolower($className) != strtolower($baseName) )
				continue;
			require_once($file);
			if ( ! class_exists($baseName) ) {
				return(false);
			}
			break;
		}
		if ( ! method_exists($baseName, $action) ) {
			return(false);
		}
		return(true);
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	private function isAjax() {
		$http_x_requested_with = @$_SERVER['HTTP_X_REQUESTED_WITH'];
		$isAjax =
			$http_x_requested_with &&
			strtolower($http_x_requested_with) == "xmlhttprequest" ;
		return($isAjax);
	}
	/*------------------------------*/
	private function showMargins() {
		if ( $this->isAjax() ) {
			return(false);
		}
		$nots = array(
			'tracking' => array(
				'unland',
				/*	'register',	*/
				/*	'registration',	*/
			),
			'pixel' => array(
				'any',
			),
			'api' => array(
				'any',
			),
		);
		$controller = $this->controller;
		$action = $this->action;
		foreach( $nots as $notClassName => $notClass )
			foreach( $notClass as $notAction )
				if ( strcasecmp($notClassName, $controller) == 0
						&& 
						( strcasecmp($notAction, $action) == 0 || $notAction == 'any' )
					) {
						return(false);
					}
		return(true);
	}
	/*------------------------------------------------------------*/
	public function changePasswd() {
		$this->Mview->showTpl("admin/changePasswd.tpl");
	}
	/*------------------------------*/
	public function updatePasswd() {
		$loginName = $this->loginName;
		$oldPasswd = @$_REQUEST['oldPasswd'];
		$newPasswd = @$_REQUEST['newPasswd'];
		$newPasswd2 = @$_REQUEST['newPasswd2'];
		if ( ! $oldPasswd || ! $newPasswd || ! $newPasswd2 ) {
			$this->Mview->error("updatePasswd: please fill in all 3 fields");
			return;
		}
		if ( $newPasswd != $newPasswd2 ) {
			$this->Mview->error("updatePasswd: new passwords are not the same");
			return;
		}
		$sql = "select * from users where loginName = '$loginName'";
		$loginRow = $this->Mmodel->getRow($sql);
		if ( ! $loginRow ) {
			$this->Mview->error("updatePasswd: no login row");
			return;
		}
		$dbPasswd = $loginRow['passwd'];
		if ( $dbPasswd != $oldPasswd && $dbPasswd != sha1($oldPasswd) ) {
			$this->Mview->error("updatePasswd: old password incorrect");
			return;
		}
		$newDbPasswd = sha1($newPasswd);
		$this->dbUpdate("users", $loginRow['id'], array(
			'passwd' => $newDbPasswd,
		));
		$this->Mview->msg("Password changed");
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	protected function error($msg, $r = 100) {
		$this->log("ERROR: $msg", $r);
	}
	/*------------------------------------------------------------*/
	protected function log($msg, $r = 100) {
		if ( rand(1, 100 * 1000) > $r * 1000 )
				return;
		if ( $r == 100 )
				$str = $msg;
		else
				$str = "$r/100: $msg";
		$this->logger->log($str);
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
}
