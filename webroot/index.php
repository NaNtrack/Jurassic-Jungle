<?php
require_once('../configs/config.php');
header("Content-type: text/plain");
$jj = new JJClient();
$success = $jj->login(JJ_EMAIL, JJ_PASSWORD);
if ( $success === true ) {
	$stats = $jj->init();
	$msj = "Level: " . $stats['personal']['level'] . ". ". $stats['battle']['ap'] . " AP to use\n";
	$msj .= "Exp: " . $stats['personal']['exp'] . "/" . $stats['personal']['exp_max']. "\n";
	$msj .= "Energy: " . $stats['personal']['energy'] . "/" . $stats['personal']['energy_max']. "\n";
	$msj .= "HP: " . $stats['personal']['hp'] . "/" . $stats['personal']['hp_max']. "\n\n";

	$msj .= "Rubidium: " . $stats['currency']['rubidium']."\n";
	$msj .= "Bank: " .$stats['currency']['bank'] ."\n";
	$msj .= "Spiked Lemon: " . $stats['food']['spiked_lemon'] . "\n";
	$msj .= "Ox Blood: " . $stats['food']['ox_blood']."\n";
	$msj .= "Turns: {$stats['food']['turns']}\n\n";

	$msj .= "AG: {$stats['battle']['agility']} ; DEF: {$stats['battle']['defense']} ; STR: {$stats['battle']['strength']} ; WIS: {$stats['battle']['wisdom']}\n";

	$headers = 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";

	// Additional headers
	$headers .= 'To: Julio Araya Cerda <julioarayacerda@gmail.com>' . "\r\n";
	$headers .= 'From: Jurassic Jungle <www-data@maletindepoker.cl>' . "\r\n";
	
	mail('julioarayacerda@gmail.com', 'Jurassic Jungle Status @ ' .gmdate("Y-m-d H:i:s"), $msj, $headers);
} else {
	echo 'Invalid password or username';
}
