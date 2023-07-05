<?php
//          Create Date      2014/04/27
//          FileName		 getSyncFileList.php
//			Skype			 hakunamoni

$filePath		= "sync/";

$fileList = array();

$handler = opendir($filePath);

while ($file = readdir($handler)) {
	if ($file != "." && $file != "..") {
		$fileList[] = $file;
	}
}
closedir($handler);
echo json_encode($fileList);

?>