<?php

/*	
* -----------------------------------------------------------------
*	File			fetch_orders.php
*	Version			1.0
*	Create Date		2014/09/30
*	Skype			hakunamoni
* -----------------------------------------------------------------

*/

require dirname(__FILE__)."/function_magento.php";

$header			= array('orderid', 'firstname', 'lastname', 'email', 'street', 'city', 'region', 'postalcode', 'countrycode', 'phone', 'shipping_class', 'item_sku', 'quantity', 'unit_price', 'total', 'tax', 'priority');

$connector		= new magentoconnector;

$orders_list	= $connector->getSalesOrderList();

//print_r($orders_list);

$content = array();
$content[] = $header;

for ( $i = 0; $i < count($orders_list); $i++ ){
	$order_info	= $connector->getSalesOrder($orders_list[$i]->increment_id);
	print_r($order_info);
	for ( $j = 0; $j < count($order_info->items); $j++ ){
		$line = array();
		$line[] = $order_info->increment_id;
		$line[] = $order_info->customer_firstname;
		$line[] = $order_info->customer_lastname;
		$line[] = $order_info->customer_email;
		$line[] = $order_info->shipping_address->street;
		$line[] = $order_info->shipping_address->city;
		//$state_abbreviation = $connector->fetch($connector->query("select `State_Abbreviation` from `tbl_us_states` where `Entered_Value` = '" . (string)$order_info->shipping_address->region . "'"));
		//$line[] = $state_abbreviation['State_Abbreviation'];
		$line[] = $order_info->shipping_address->region;
		$line[] = $order_info->shipping_address->postcode;
		$line[] = $order_info->shipping_address->country_id;
		$line[] = $order_info->shipping_address->telephone;
		$line[] = $order_info->shipping_method;
		$line[] = $order_info->items[$j]->sku;
		$line[] = (int)$order_info->items[$j]->qty_ordered;
		$line[] = $order_info->items[$j]->price;
		$line[] = $order_info->grand_total;
		$line[] = $order_info->shipping_amount;
		$line[] = floor((strtotime("now") - strtotime($order_info->created_at)) / 86400);
		$content[] = $line;		
	}	
}

if ( count($content) > 1 ){

	$resultFile = dirname(__FILE__) . '/sync/magento_' . date('Y_m_d_H_i_s') . '.csv';		
	$file = fopen( $resultFile, 'w' );
	fwrite($file, "\xEF\xBB\xBF");
	foreach ($content as $fields) {
		fputcsv($file, $fields);
	}
	fclose( $file );

}

?>