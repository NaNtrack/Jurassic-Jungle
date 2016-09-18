<?php

$TIMER_starttime = microtime(true);
global $TIMER_starttime;
session_start();
header('Content-type: text/html; charset=utf8');

define('JJ', true);


define('THHHTP', $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://');

/**
 * The Document Root directory (with trailing slash)
 */
define('WEBROOT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/**
 * Server URL
 */
define('SERVER_URL', THHHTP . $_SERVER['SERVER_NAME'] . '/');

/**
 * The classes directory
 */
define('CLASSES_DIR', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);

/**
 * The configuration directory
 */
define('CONFIG_DIR', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR);

/**
 * The configuration directory
 */
define('LOGS_DIR', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR);

spl_autoload_register('app_autoload');

//load configuration
$_Conf = parse_ini_file(CONFIG_DIR . 'application.ini', true);

//[general]
define('LOG_FILE', LOGS_DIR . $_Conf['general']['log_file']);

//[jurassicjungle]
define('JJ_EMAIL'	, $_Conf['jurassicjungle']['email']);
define('JJ_PASSWORD'	, $_Conf['jurassicjungle']['pass']);

unset($_Conf);

function app_autoload($className) {
	$fileName = loadClass($className, CLASSES_DIR);
	if (file_exists($fileName))
		require_once $fileName;
}

function loadClass($className, $baseDir) {
	$dirHandle = opendir($baseDir);
	$theFile = false;
	while (($file = readdir($dirHandle)) != false) {
		if ($file != '.' && $file != '..') {
			if (is_dir($baseDir . $file)) {
				$theFile = loadClass($className, $baseDir . $file . DIRECTORY_SEPARATOR);
				if ($theFile) {
					return $theFile;
				}
			} else {
				$base = basename($file);
				if ($base === $className . '.php') {
					closedir($dirHandle);
					return $baseDir . DIRECTORY_SEPARATOR . $file;
				}
			}
		}
	}
	closedir($dirHandle);
	return $theFile;
}

function getTimerTime($fromTime = null) {
	global $TIMER_starttime;
	$end = microtime(true);
	$totaltime = $end - ($fromTime !== null ? $fromTime : $TIMER_starttime);
	$totaltime = round($totaltime, 5);
	return $totaltime;
}