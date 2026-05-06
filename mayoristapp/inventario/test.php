<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

    $submit = "";
    if (isset($_REQUEST['submit'])) { 
        $submit = isset($_GET["submit"]) ? trim($_GET["submit"]) : trim($_POST["submit"]); 
    };

    $token = '';
    if (isset($_REQUEST['token'])) { 
        $token = isset($_GET['token']) ? trim($_GET['token']) : trim($_POST['token']); 
    };

    $user = '';
    if (isset($_REQUEST['user'])) {
        $user = isset($_GET['user']) ? trim($_GET['user']) : trim($_POST['user']);
    };

    $empresa = '';
    if (isset($_REQUEST['empresa'])) {
        $empresa = isset($_GET['empresa']) ? trim($_GET['empresa']) : trim($_POST['empresa']);
    };

    $pass = '';
    if (isset($_REQUEST['pass'])) {
        $pass = isset($_GET['pass']) ? trim($_GET['pass']) : trim($_POST['pass']);
    };

    $search = '';
    if (isset($_REQUEST['search'])) {
        $search = isset($_GET['search']) ? trim($_GET['search']) : trim($_POST['search']);
    };

    // CONSULTO LOGUEO (primer paso)
    // Primero saver si estoy ya previamente logueado (por ejemplo si alguien refresca la web con f5)
    // Reviso si existe la cookie de token y la envio al back-end
    // 1) si estoy logueado me da onfirmacion (true) y datos del usuario
    // 2) si no estoy logueado me manda confirmacion (false) y el listado de empresas del loguin.

    if ($submit==='getToken') {

        $empresa = [];

        if($token==='testToken' ) {
            $tokenResult = 'true';
            $empresa = ["0" => ""];
        } else {
            $tokenResult = 'false';
            $empresa = [
                "0" => "Chapini",
                "1" => "Maldonado",
                "2" => "One Store",
            ];
        }

        $response = [
            'tokenResult' => $tokenResult,
            'empresa' => $empresa
        ];
        $response = array('result' => $response);
        print json_encode($response);
    }

    // ME LOGUEO EN LA APP

    if ($submit==='getLoguin') {

        if ($user === 'test@test.com') {

            $usuarioMessage = 'usuario correcto';

            if ($pass === 'test') {

                $passwordMessage = 'password correcto';
                $token = 'testToken';

            } else {

                $passwordMessage = 'password incorrecto';
                $token = '';

            };

        } else {

            $usuarioMessage = 'usuario incorrecto';
            $passwordMessage = 'password incorrecto';
            $token = '';

        };

        $response = [ 'user' => $usuarioMessage, 'pass' => $passwordMessage, 'token' => $token ];
        $response = array('result' => $response);
        print json_encode($response);
    };

    // PIDO LOS DATOS DEL USUARIO

    if ($submit==='getDatosDeUsuario') {

        $logo = "https://www.chapini.com/nv/img/chapini-logo-header.jpg";
        $empresa = "Chapini";
        $Nombre = "Jhon";
        $Apellido = "Doe";
        $DatoUno = "dato uno";
        $DatoDos = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec tincidunt tempor ex non mollis. Morbi tincidunt dui et erat auctor vehicula. Donec hendrerit, ipsum vel cursus faucibus, orci quam finibus diam, in elementum sem metus ut sapien. ";

        $response = [
            'logo' => $logo,
            'empresa' => $empresa,
            'nombre' => $Nombre,
            'apellido' => $Apellido,
            'datoUno' => $DatoUno,
            'datoDos' => $DatoDos,
        ];
        $response = array('result' => $response);
        print json_encode($response);
    };

    if ($submit==='getSearch') {

        if ($search == '7793253003326') {

            $estado = 'true';
            
            $id = '543';
            $cod = '7793253003326';
            $fotos = [
                '0' => "https://dev.administranet.com.ar/catalogo/ramas/rodamientos/Articulo_Foto_Multi/14_0.jpeg",
                '1' => "https://dev.administranet.com.ar/catalogo/ramas/rodamientos/Articulo_Foto_Multi/14_0.jpeg",
                '2' => "https://dev.administranet.com.ar/catalogo/ramas/rodamientos/Articulo_Foto_Multi/14_0.jpeg",
            ];
            $titulo = "Notebook lenovo ideapad s145 14api amd ryzen 3 14 4gb ram 128gb ssd w10s";
            $precio = "89.990";
            $marca = "Lenovo";
            $stock = "15";
            $descripcion = "dato dos";

            $response = [
                'estado' => $estado,
                'id' => $id,
                'cod' => $cod,
                'fotos' => $fotos,
                'titulo' => $titulo,
                'precio' => $precio,
                'marca' => $marca,
                'stock' => $stock,
                'descripcion' => $descripcion,
            ];
            
        } else {

            $estado = 'true';
            
            $id = '543';
            $cod = '543';
            $fotos = [
                '0' => "https://dev.administranet.com.ar/catalogo/ramas/rodamientos/Articulo_Foto_Multi/14_0.jpeg",
                '1' => "https://dev.administranet.com.ar/catalogo/ramas/rodamientos/Articulo_Foto_Multi/14_0.jpeg",
                '2' => "https://dev.administranet.com.ar/catalogo/ramas/rodamientos/Articulo_Foto_Multi/14_0.jpeg",
            ];
            $titulo = "Notebook lenovo ideapad s145 14api (busqueda simulada)";
            $precio = "89.990";
            $marca = "Lenovo";
            $stock = "15";
            $descripcion = "dato dos";

            $response = [
                'estado' => $estado,
                'id' => $id,
                'cod' => $cod,
                'fotos' => $fotos,
                'titulo' => $titulo,
                'precio' => $precio,
                'marca' => $marca,
                'stock' => $stock,
                'descripcion' => $descripcion,
            ];

        }

        $response = array('result' => $response);
        print json_encode($response);

    };

    if ($submit==='actualizarInvetario') {

        $estado = 'true';
        $closingMessage = "Se actualizo correctamente";

        $response = [
            'estado' => $estado,
            'closingMessage' => $closingMessage,
        ];

        $response = array('result' => $response);
        print json_encode($response);
    };

?>