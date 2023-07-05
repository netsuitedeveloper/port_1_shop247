<?php

/*	
* -----------------------------------------------------------------
*	File			magento.ship.conf.php
*	Version			1.0
*	Create Date		2014/10/10
*	Skype			hakunamoni
* -----------------------------------------------------------------

*/

$fileTemp	= dirname(__FILE__) . "/temp/temp.dat";

// Get Shipped Status Orders With Tracking Number

$shipped_data	= $_GET['SHIPPED_DATA'];
$status			= $_GET['status'];

if ( $status === '1' ){
	$f	   = fopen( $fileTemp, 'w');
	fwrite($f, $shipped_data);
	fclose($f);
}else if( $status === '2' ){
	$f	   = fopen( $fileTemp, 'a');
	fwrite($f, $shipped_data);
	fclose($f);
}else if( $status === '3' ){
	$fileDes  = dirname(__FILE__) . "/temp/conf_magento.dat";
	$content = file_get_contents($fileTemp);
	file_put_contents($fileDes, $content);	
}

?>