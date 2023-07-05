<?php

//          Create Date      2014/10/08
//          FileName		 updateFiles.php
//			Skype			 hakunamoni

$targetFile		= $_GET['file'];
$order_array	= json_decode($_GET['imported']);

require dirname(__FILE__)."/function_magento.php";

$connector		= new magentoconnector;

$result = $connector->updateSalesOrderStatus('100000014', 'processing');
print_r($result);
exit;

for ( $i = 0; $i < count($order_array); $i++ ){
	$result = $connector->updateSalesOrderStatus($order_array[$i]);
	echo 'order ' . $order_array[$i] . ' : ' . $result;
}

$filePath		= dirname(__FILE__) . "/sync/";

if ( $targetFile ){
	
	unlink($filePath . $targetFile);
	echo "deleted";
}

?>