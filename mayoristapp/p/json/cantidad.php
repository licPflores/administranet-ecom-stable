<?php
require_once 'preinclude.php';
$cantidad = 0;


if(isset($_REQUEST['id'])){
if(!isset($_SESSION))
{
	session_start();
$vector = Array();
}
//print_r($_SESSION['cantidad']);



if(isset($_SESSION['cantidad'])){
	
	$cantidad = $_SESSION['cantidad'][$_REQUEST['id']];
	
}

}
echo $cantidad;

