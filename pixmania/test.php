<?php
//          Create Date      2014/05/11
//          FileName		 integration_pixmania.php
//          Developer		 Hakuna Moni
//          E-Mail			 hakunamoni@gmail.com

require dirname(__FILE__)."/function.php";

$header			= array('orderid', 'buyername', 'email', 'addr1', 'addr2', 'city', 'region', 'postalcode', 'countrycode', 'phone1', 'phone2', 'item_sku', 'quantity', 'unit_price', 'shipping', 'priority');

$tempFile	= dirname(__FILE__) . '/active_orders/imported.dat';

$connector = new pixmaniaConnector();

$cookie  = tempnam (dirname(__FILE__)."/cookie/", "CURLCOOKIE");

$result = $connector->test($cookie);

$importedOrders = array();

$file = fopen( $tempFile, "r" );

while( $line = fgets ($file) )
	$importedOrders[] = rtrim($line);
fclose( $file );

$orderArray = array();

$content = array();
$content[] = $header;

for ( $i = 0; $i < count($result); $i++ ){
	if ( $result[$i][6] != 'Livraison' && !in_array($result[$i][0], $importedOrders) && count($result[$i]) === 35 ){		
		$line = array();
		$line[] = $result[$i][0];
		$line[] = $result[$i][18];
		$line[] = $result[$i][16];
		$line[] = $result[$i][20];
		$line[] = $result[$i][22];
		$line[] = $result[$i][26];
		$line[] = $result[$i][28];
		$line[] = $result[$i][24];
		$line[] = $connector->getCountryCode($result[$i][30]);
		
		$phone = explode('/', $result[$i][32]);
		$line[] = trim($phone[0]);
		$line[] = $phone[1] ? trim($phone[1]) : '';

		$sku = explode( '||', $result[$i][34]);

		for ( $j = 0; $j < count($sku); $j++ ){
			if ( substr($sku[$j], 0, 4) === 'SKU=' ){
				$line[] = substr($sku[$j], 4);
				break;
			}
		}

		$line[] = $result[$i][8];
		$line[] = $result[$i][10];
		$line[] = $result[$i][12];
		$line[] = floor((strtotime("now") - strtotime($result[$i][4])) / 86400);
		$content[] = $line;
	}
}

echo "count: " . count($content);

// write sync order file	

if ( count($content) > 1 ){

	$resultFile = dirname(__FILE__) . '/sync/pixmania_' . date('Y_m_d_H_i_s') . '.csv';		
	$file = fopen( $resultFile, 'w' );
	fwrite($file, "\xEF\xBB\xBF");
	foreach ($content as $fields) {
		fputcsv($file, $fields);
	}
	fclose( $file );

}

echo "end";

?>