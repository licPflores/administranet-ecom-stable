<?php
// $timeout = 30;  /* thirty seconds for timeout */
// $conexionT = mysqli_init( );
// $conexionT->options( MYSQLI_OPT_CONNECT_TIMEOUT, $timeout ) ||
//      die( 'mysqli_options croaked: ' . $conexionT->error );
// //$conexionT->real_connect($server,  $usr, $passwd, $dbname) ||
//  //    die( 'mysqli_real_connect croaked: ' . $link->error );
	 
// // conexion de afuera.
$conexionT = @mysqli_connect(servidor_db,usuario_db,pass_db,base_de_datos,puerto_db);

if(!$conexionT){
     //or die("no me puedo conectar includes/conex.inc.php \n". mysqli_connect_error().
     //"\nArchivo ".__FILE__."\nLinea".__LINE__);
     $conexionT=mysqli_connect(servidor_db_local,usuario_db,pass_db,base_de_datos,puerto_db);
}


mysqli_set_charset($conexionT,'utf8');
//echo "dentro";
