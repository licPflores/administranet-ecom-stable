<?php
require_once 'preinclude.php';
$cantidad = 0;



if(!isset($_SESSION))
{
	session_start();
//$vector = Array();
}
//print_r($_SESSION['cantidad']);



if(isset($_SESSION['cantidad'])){
	
	$cantidad = sizeof($_SESSION['cantidad']); 
	
}


print json_encode($cantidad);

