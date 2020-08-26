<?php
/*------------------------------------------------------------*/
class LogTracker extends Tracking {
        /*------------------------------------------------------------*/
        /*------------------------------------------------------------*/
        public function index() {
                $this->track(@$_REQUEST['name']);
        }
        /*------------------------------------------------------------*/
        private function track($name) {
			$v = "/var/www/vhosts";
			$dir = "$v/tracking.theora.com/logs/$name";
			$logFile = `/bin/ls -tr $dir | tail -1`;
			$logFile = trim($logFile);
			$numLines = 30;
			$contents = `tail -$numLines $dir/$logFile`;
			$contents = trim($contents);
			$lines = explode("\n", $contents);
			$text = implode("<br />\n", $lines);
			$this->Mview->msg($logFile);
			$this->Mview->pushOutput($text);
        }
        /*------------------------------------------------------------*/
        /*------------------------------------------------------------*/
}
