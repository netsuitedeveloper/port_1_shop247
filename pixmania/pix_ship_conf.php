<?php

//          Create Date      2014/05/16
//          FileName		 pix_ship_conf.php
//          Developer		 Hakuna Moni
//          E-Mail			 hakunamoni@gmail.com

require dirname(__FILE__)."/function.php";

$fileTemp	= dirname(__FILE__) . "/temp/temp.dat";

// Get Shipped Status Orders With Tracking Number

$shipped_data = $_GET['SHIPPED_DATA'];

$status = $_GET['status'];

if ( $status === '1' ){
	$f	   = fopen( $fileTemp, 'w');
	fwrite($f, $shipped_data);
	fclose($f);
}else if( $status === '2' ){
	$f	   = fopen( $fileTemp, 'a');
	fwrite($f, $shipped_data);
	fclose($f);
}else if( $status === '3' ){
	$fileDes  = dirname(__FILE__) . "/temp/pix.dat";
	$content = file_get_contents($fileTemp);
	file_put_contents($fileDes, $content);

	$shipped_array = json_decode($content);

	$header			= array('Order number', 'Order line number', 'Shipping company ', 'Tracking number', 'Tracking number link', 'Telephone number', 'Shipping date');

	$connector = new pixmaniaConnector();

	$content = array();
	//$content[] = $header;

	for ( $i = 0; $i < count($shipped_array); $i++ ){
		$line = array();
		$line[] = $shipped_array[$i]->orderNum;
		$line[] = (int)$shipped_array[$i]->orderLineNum;
		$line[] = $shipped_array[$i]->shippingCompany;
		$line[] = $shipped_array[$i]->trackingNumber;
		//$line[] = $shipped_array[$i]->trackingNumLink;
		//$line[] = $shipped_array[$i]->telephoneNum;
		//$line[] = $shipped_array[$i]->shippingDate;
		$line[] = '';
		$line[] = '';
		$line[] = '';
		$content[] = $line;
	}

	echo "count: " . count($content);

	// write sync order file	

	if ( count($content) > 0 ){

		$resultFile = dirname(__FILE__) . '/import/confirm.csv';		
		$file = fopen( $resultFile, 'w' );
		//fwrite($file, "\xEF\xBB\xBF");
		for( $i = 0; $i < count($content); $i++) {
			if ( $i == (count($content) - 1 )){
				fwrite($file, implode(';', $content[$i]) );
			}else{
				fwrite($file, implode(';', $content[$i]) . PHP_EOL );
			}			
		}
		fclose( $file );

	}

	$result = $connector->postSubmitFile();

	echo $result;
}

?>