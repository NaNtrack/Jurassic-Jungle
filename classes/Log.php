<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 1,2,3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of Log
 *
 * @author delpho
 */
class Log {

	/**
	 * The object instance
	 *
	 * @var Log
	 */
	private static $_instance = null;
	private $_logFile;

	private function __construct() {
		$this->_logFile = LOG_FILE;
	}

	/**
	 * Get the instance (singleton)
	 *
	 * @return Log The instance of this class
	 */
	public static function getInstance() {
		if (!isset(self::$_instance) || self::$_instance == null) {
			$c = __CLASS__;
			self::$_instance = new $c;
		}
		return self::$_instance;
	}

	/**
	 * Log a message into the log file
	 *
	 * @param strign $message The message to log
	 * @param bool $display If true show the message
	 */
	public function log($message, $display = false) {
		$current_tz = date_default_timezone_get();
		if (ini_get('date.timezone'))
			date_default_timezone_set(ini_get('date.timezone'));
		$date = gmdate("Y/m/d H:i:s");

		date_default_timezone_set($current_tz);
		$log = "[$date] - $message\n";
		$fp = @fopen($this->_logFile, 'a');
		if ($display)
			echo "$log\n";
		if (is_resource($fp)) {
			@fwrite($fp, $log);
			@fclose($fp);
		} else {
			trigger_error("Can't open '{$this->_logFile}' log file", E_USER_WARNING);
		}
	}

	public function logException(Exception $ex) {
		$this->log("[exception] " . basename($ex->getFile()) . "@{$ex->getLine()} : {$ex->getMessage()}. Trace: {$ex->getTraceAsString()}");
	}

}
