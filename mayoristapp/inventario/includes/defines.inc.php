<?php
# ESTADO DEL PROYECTO
define('PROYECTO','test');

# mostrar errores segun proyecto
if(PROYECTO=='test'){
	define('DEBUG',true);
} else {
	define('DEBUG',false);
}

# tiempo de sesion
define( 'MAX_SESSION_TIEMPO', 3600 * 1 );

# debug activado
if(DEBUG === true){
	ini_set('error_reporting', E_ALL);
	//ini_set('error_reporting', E_ERROR);	
	ini_set('display_errors', 1);
	//opcache_invalidate(__FILE__, true);
} else {
	ini_set('error_reporting', E_ERROR);	
}

# BASES DE DATOS 
# ==================================================================================

# base de datos TEST
if(PROYECTO=='test'){

	# base de datos LOCAL / servidor sincronizacion
	define("servidor_db","190.15.214.142");
	define("servidor_db_local","192.168.0.1");
	define("usuario_db","administranet");
	define("pass_db",'a7v8xx0805');
	define("puerto_db",3306);
	define("base_de_datos","administranet74");

	/*
	* conexion para carrito 
	* -----------------------------------------------------------------------------
	*/
	
	// test administranet
	define("carrito_servidor_db","190.15.214.142");
	define("carrito_usuario_db","administranet");
	define("carrito_pass_db","a7v8xx0805");
	define("carrito_puerto_db",3306);
	define("carrito_base_de_datos","administranet74");
}

# base de datos REAL PRODUCCION.
if(PROYECTO=='produccion'){
	# base de datos LOCAL / servidor sincronizacion
	define("servidor_db","administranet.com.ar");
	define("usuario_db","jbins");
	define("pass_db",'7tH77xd^');
	define("puerto_db",3306);
	define("base_de_datos","jbins");
	
	/*
	* conexion para carrito 
	* -----------------------------------------------------------------------------
	*/
	
	define("carrito_servidor_db","190.220.229.74");
	define("carrito_usuario_db","administranet");
	define("carrito_pass_db","a7v8xx0805");
	define("carrito_puerto_db",3306);
	define("carrito_base_de_datos","administranet6");

	define('DNS','jbinsumos.com.ar');
	define('urlVueltaMercadoPago','https://'.DNS);
	define('URL','https://'.DNS);
}

define('VALIDOECOMMEXTERNO','No');// para validadcion de productos entre dos tiendas.

# orden de categorias
define('CAMPOORDENCATEGORIAS','nombre_categoria');
define('DIRECCIONORDENCATEGORIAS','ASC');
define('relay_articulos','ajax/relay-art.php');
define('JUNTAFILTROS','No'); // pisa los filros no los acumula.
define('FORZARBULTOPROMEDIO','No'); // pisa los filros no los acumula.
