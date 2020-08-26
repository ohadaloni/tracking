<?php
/*------------------------------------------------------------*/
class Menu extends Mcontroller {
	/*------------------------------------------------------------*/
	public function index() {
			$this->Mview->showTpl("menuDriver.tpl", array(
				'menu' => $this->dd(),
			));
	}
	/*------------------------------------------------------------*/
	/*------------------------------------------------------------*/
	private function dd() {
		$menu = array(
			'Tracks' => array(
				array(
					'name' => 'dashboard',
					'title' => 'Dashboard',
					'url' => "/Dashboard",
				),
			),
			'admin' => array(
				array(
					'name' => 'showSource',
					'title' => 'Show Source Code',
					'url' => "/showSource",
				),
				array(
					'name' => 'clone',
					'title' => 'Clone',
					'url' => "https://github.com/ohadaloni/tracking",
					'target' => "clone",
				),
			),
		);
		$loginName = TrackingLogin::loginName();
		if ( $loginName )
			$menu[$loginName] = array(
				array(
					'name' => 'landHere',
					'title' => 'Land Here',
					'url' => "/tracking/landHere",
				),
				array(
					'name' => 'UnLand',
					'title' => 'unland (land latest)',
					'url' => "/tracking/unland",
				),
				array(
					'name' => 'chpass',
					'title' => 'Change Password',
					'url' => "/tracking/changePasswd",
				),
				array(
					'name' => 'logout',
					'title' => 'Log Off',
					'url' => "/?logOut=logOut",
				),
			);
		return($menu);
	}
	/*------------------------------------------------------------*/
}

