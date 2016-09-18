<?php
require_once('../configs/config.php');
$jj = new JJClient();
$success = $jj->login(JJ_EMAIL, JJ_PASSWORD);
if ( $success === true ) {
	$jj->init();
	$jj->innerJungle();
} else {
	echo 'Invalid password or username';
}
