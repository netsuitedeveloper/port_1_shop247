<?php

/*	
* -----------------------------------------------------------------
*	File			confirm_manifest.php
*	Version			1.0
*	Create Date		2014/11/07
*	Created By		Hakuna Moni (hakunamoni@gmail.com)
* -----------------------------------------------------------------

*/

require dirname(__FILE__)."/function.php";

$connector		= new temandoconnector;

//$result = $connector->get_request('SOAU-00033487');

//print_r($result);

//exit;

$result = $connector->get_manifests();

print_r($result);

$result = $connector->confirm_manifests();

print_r($result);

?>