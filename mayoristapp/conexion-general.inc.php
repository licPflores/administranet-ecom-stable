<?php
include_once('includes/includes.inc.php');


// admnet

$conexionT = @mysqli_connect(servidor_db, usuario_db, password_db,null,puerto_db);
//echo mysql_client_encoding ($conexionT)."<br><br>";
if(!$conexionT){
    $conexionT =@mysqli_connect(administranetLOCAL, usuario_db, password_db,null,puerto_db) or die("error de conexion principal local <pre>". mysqli_connect_error()."</pre>");
}

mysqli_set_charset($conexionT,'utf8');
//echo mysql_client_encoding ($conexionT);

mysqli_select_db($conexionT,"empresas");
//@mysql_query("SET NAMES 'latin1'");


