<?php

/*
*
* -----------------------------------------------------------------
	File	:	rakuten-fetch-orders.php
	Date	:	11/05/2014
	Dev		:	Xiaoxin Li ( nsmastership@gmail.com )
* -----------------------------------------------------------------
*
*	This script is to fetch orders from Rakuten using api.*
*
*/

require dirname(__FILE__)."/function-rakuten.php";

$connector		= new rakutenConnector;

$orders_list	= $connector->getOrderLists();

if ( $orders_list ){
	for ( $i = 0; $i < count($orders_list); $i++ ){		
		$check = $connector->fetch($connector->query("select * from `rakuten_orders` where `orderid` = '" . $orders_list[$i]->orderNumber . "'"));
		if ( !$check ){
			$order = $connector->getOrderDetail($orders_list[$i]->orderNumber);

			for ( $i = 0; $i < count($order->order->orderItem); $i++ ){
				$orderid = htmlentities(mysql_real_escape_string($order->order->orderNumber));
				$buyername = htmlentities(mysql_real_escape_string($order->order->buyerName));
				$email = htmlentities(mysql_real_escape_string($order->order->buyerEmail));
				$addresses = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->name));
				$addr1 = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->address1));
				$addr2 = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->address2));
				$city = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->city));
				$region = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->stateCode));
				$postalcode = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->postalCode));
				$countrycode = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->countryCode));
				$phone = htmlentities(mysql_real_escape_string($order->order->shipping->shippingAddress->phoneNumber));
				$shipping_class = htmlentities(mysql_real_escape_string($order->order->shipping->shippingMethod));
				$item_sku = htmlentities(mysql_real_escape_string($order->order->orderItem[$i]->baseSKU));
				$quantity = htmlentities(mysql_real_escape_string($order->order->orderItem[$i]->quantity));
				$unit_price = htmlentities(mysql_real_escape_string($order->order->orderItem[$i]->unitPrice));
				$total = htmlentities(mysql_real_escape_string($order->order->orderTotal));
				$priority = htmlentities(mysql_real_escape_string(floor((strtotime("now") - strtotime($order->order->orderDate)) / 86400)));
				$tax = 0;

				$connector->query("insert into `az_" . $info[6] . "_orders` set `orderid` = '" . $orderid . "', `buyername` = '" . $buyername . "', `email` = '" . $email . "', `addresses` = '" . $addresses . "', `addr1` = '" . $addr1 . "', `addr2` = '" . $addr2 . "', `city` = '" . $city . "', `region` = '" . $region . "', `postalcode` = '" . $postalcode . "', `countrycode` = '" . $countrycode . "', `phone` = '" . $phone . "', `shipping_class` = '" . $shipping_class . "', `item_sku` = '" . $item_sku . "', `quantity` = '" . $quantity . "', `unit_price` = '" . $unit_price . "', `total` = '" . $total . "', `priority` = '" . $priority . "', `tax` = '" . $tax . "', `flag` = '1'");
			}			
		}
	}
}


?>