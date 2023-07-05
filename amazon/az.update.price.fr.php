<?php

/*	
* -----------------------------------------------------------------
*	File			az.update.price.fr.php
*	Version			1.0
*	Update Date		2014/08/10
*	Created By		Hakuna Moni (hakunamoni@gmail.com)
* -----------------------------------------------------------------
*/
require dirname(__FILE__)."/function.php";


$connector = new amazonconnector;

$account = 3;

$currency = 'EUR';

$fileDes  = dirname(__FILE__) . "/temp/fr.dat";

$content = file_get_contents($fileDes);

$inv_array = json_decode($content);

print_r($inv_array);

$inventoryArray = array_chunk($inv_array, 1000);

for ( $i = 0; $i < count($inventoryArray); $i++ ){
	$xml = $connector->makeAzXMLFeed($inventoryArray[$i], $account, $currency);
	echo $xml;
	$result = $connector->submitPriceFeed($account, $xml);
    echo $result;
}