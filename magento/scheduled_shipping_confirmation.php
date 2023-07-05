<?php

/*	
* -----------------------------------------------------------------
*	File			scheduled_shipping_confirmation.php
*	Version			1.0
*	Create Date		2014/10/11
*	Skype			hakunamoni
* -----------------------------------------------------------------

*/

require dirname(__FILE__)."/function_magento.php";

$file = dirname(__FILE__) . "/temp/conf_magento.dat";

$connector = new magentoconnector;

$connector->updateNSFields('6456899');

exit;

$shipment = $connector->createShipment('100000005');
$shipment = $connector->addTrackShipment($shipment, 'fedex', 'test-tracking');

$shipment = $connector->updateSalesOrderStatus('100000005', 'complete');

exit;

$shipment = $connector->getShipmentInfo('100000002');

print_r($shipment);



$content = file_get_contents($file);

//$shipment = $connector->createShipment('100000005');
$shipment = $connector->addTrackShipment('100000002', 'fedex', 'test-tracking');
print_r($shipment);
//$shipment = $connector->unholdSalesOrder('100000002');

exit;

$shipped_array = json_decode($content);

for( $p = 0; $p < count($shipped_array); $p++ ){
	
	$ns_id			= $shipped_array[$p]->id;
	$order_num		= $shipped_array[$p]->orderNum;
	$ship_date		= $shipped_array[$p]->shipDate;
	$tracking_num	= $shipped_array[$p]->trackingNum;
	$carrier_name	= $shipped_array[$p]->carrierName;

	$order_info = $connector->getSalesOrder($order_num);

	//$items_qty = array();

	//foreach( $order_info->items as $item ){
	//	$item_qty = array();
	//	$item_qty[] = $item->item_id;
	//	$item_qty[] = $item->qty_ordered;
		
	//	$items_qty[] = $item_qty;
	//}

	$shipment = $connector->createShipment($order_num);
	print_r($shipment);
	$shipment = $connector->addTrackShipment($shipment, $carrier_name, $tracking_num);
	print_r($shipment);

	$update_order = $connector->updateSalesOrderStatus($order_num, 'shipped');
	print_r($update_order);
}

?>