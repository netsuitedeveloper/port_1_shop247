<?php

/*	
* -----------------------------------------------------------------
*	File			scheduled_inventory_sync.php
*	Version			1.0
*	Create Date		2014/10/13
*	Skype			hakunamoni
* -----------------------------------------------------------------

*/

require dirname(__FILE__)."/function_magento.php";

$file = dirname(__FILE__) . "/temp/inventory_magento.dat";

$connector = new magentoconnector;

$content = file_get_contents($file);

$inventory_array = json_decode($content);

$qty_arrs = array();

for( $i = 0; $i < count($inventory_array); $i++ ){	

	if ( $i % 2 == 0 ){
		
		$sku = trim(str_replace('"', '', $inventory_array[$i]));
		$sku_array[] = $sku;
		
	}else{
	
		$qty_arrs[$sku] = (int)str_replace('"', '', $inventory_array[$i]);

	}
}

$result = $connector->getInventoryStock($sku_array);

for ( $i = 0; $i < count($result); $i++ ){
	$update_result = $connector->updateInventoryStock($result[$i]->product_id, $qty_arrs[$result[$i]->sku]);
}

?>