<?php

/*	
* -----------------------------------------------------------------
*	File			temando.php
*	Version			1.0
*	Create Date		2014/10/15
*	Created By		Hakuna Moni (hakunamoni@gmail.com)
* -----------------------------------------------------------------

*/

require dirname(__FILE__)."/function.php";

$connector		= new temandoconnector;

$quote	= $connector->cancelBRequest($_GET['param_id']);

?>