<?php 
// forzar https;
if(!isset($_SERVER['HTTPS'])|| (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!='on')){
			//no soy https debo ir hacia https:
		$serverActual=$_SERVER['SERVER_NAME'];
		//echo var_dump($serverActual);
		// x multples servicios de internet
		$url="";
		switch($serverActual){
				case '190.15.209.173':
					$url="https://chapinidorrego.dyndns.org/administraweb/sistema";
					break;
				case '209.13.155.34':
					$url="https://chapinidorrego1.dyndns.org/administraweb/sistema";
					break;
					
		}
		//echo var_dump($url);
		header('Location: '.$url);
		die;
}