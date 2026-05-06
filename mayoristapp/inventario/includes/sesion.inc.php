<?php
function is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}
# salida de sesion pero dejo las cookies si hubiera.
function destruir_session() {

    $_SESSION = array();
    if ( ini_get( 'session.use_cookies' ) ) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - MAX_SESSION_TIEMPO,
            $params[ 'path' ],
            $params[ 'domain' ],
            $params[ 'secure' ],
            $params[ 'httponly' ] );
    }

    @session_destroy();
}

// Example
if ( is_session_started() === FALSE ) session_start();

if ( isset( $_SESSION[ 'ULTIMA_ACTIVIDAD' ] ) && defined('MAX_SESSION_TIEMPO') &&
     ( time() - $_SESSION[ 'ULTIMA_ACTIVIDAD' ] > MAX_SESSION_TIEMPO ) ) {

    // Si ha pasado el tiempo sobre el limite destruye la session
    destruir_session();
   // echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";
    header('location: ../index.php');
    exit();
}

$_SESSION[ 'ULTIMA_ACTIVIDAD' ] = time();

// Función para destruir y resetear los parámetros de sesión
