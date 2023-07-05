<?php
//          Create Date      2014/05/08
//          FileName		 updateFiles.php
//          Developer		 Hakuna Moni
//          E-Mail			 hakunamoni@gmail.com

$targetFile		= $_GET['file'];
$order_array	= json_decode($_GET['arr']);

echo $targetFile;
echo $_GET['arr'];

$filePath		= dirname(__FILE__) . "/sync/";

if ( $targetFile && count($order_array) > 0 ){
	
	$file = fopen( $filePath . $targetFile, "r" );

	$arr = array();

	while(! feof($file)){

		$arr[] = fgetcsv($file);
	
	}

	fclose( $file );

	echo "arr len : " . count($arr);
	echo " order_array len : " . count($order_array);
	
	if ( count($arr) - count($order_array) < 3 ){
			
		unlink($filePath . $targetFile);
		echo "deleted";
	
	}else{
	
		$update_arr = array();
		
		$update_arr[] = $arr[0];
		
		for( $i = 1; $i < count($arr); $i++ ){
			if ( !in_array($arr[$i][0], $order_array) ){
				
				$update_arr[] = $arr[$i];
			
			}
		}

		$file = fopen( $filePath . $targetFile, "w" );
		
		fwrite($file, "\xEF\xBB\xBF");
		
		foreach ($update_arr as $fields) {
		
			fputcsv($file, $fields);
		
		}

		fclose( $file );

	}

	$prefix = str_replace('_', '', substr($targetFile, 0, 3));

	$activeAzFile = dirname(__FILE__) . '/az_active_orders/' . $prefix . '.dat';

	$file = fopen( $activeAzFile, 'r' );

	$active_az_arr = array();

	while(! feof($file)){

		$line = fgetcsv($file);

		if ( !in_array($line[0], $active_az_arr) ){
			$active_az_arr[] = $line[0];
		}
	
	}

	fclose( $file );	

	$activeFile = dirname(__FILE__) . '/active_orders/az_' . $prefix . '_imported.dat';

	$file = fopen( $activeFile, 'r' );

	$imported_arr = array();

	while(! feof($file)){

		$line = fgetcsv($file);

		if ( !in_array($line[0], $imported_arr) && in_array($line[0], $active_az_arr) ){
			$imported_arr[] = $line[0];
		}
	
	}

	for ( $i = 0; $i < count($order_array); $i++ ){
		
		if ( !in_array($order_array[$i], $imported_arr) ){
			$imported_arr[] = $order_array[$i];
		}
		
	}

	fclose( $file );

	$file = fopen( $activeFile, 'w' );

	for ( $i = 0; $i < count($imported_arr); $i++ ){
			fwrite($file, $imported_arr[$i] . "\n");
	}
	
	fclose( $file );
	
}

?>