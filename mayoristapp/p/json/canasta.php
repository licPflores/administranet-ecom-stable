<?php

require_once 'preinclude.php';
if (!isset($_SESSION)) {
    session_start();
    $vector = Array();
} else {

    if (isset($_SESSION['carrito'])) {
        $vector = $_SESSION['carrito'];
    } else {
        $vector = Array();
    }
}

// 



// control de cantidades negativas
if (isset($_REQUEST['agregar']))
    if ($_REQUEST['agregar'] < 1)
        die(json_encode(array("Mensaje" => "Cantidad insuficiente")));
    
// control de haber agregado un articulo
if (isset($_REQUEST['agregar'])) {
    if (!isset($_REQUEST['articulo']))
        die(json_encode(array("Mensaje" => "No tengo articulos")));
//if(!isset($_REQUEST['puntos']))die("No tengo puntos");
    $articulo = $_REQUEST['articulo'];
    $puntos = 0;
    $articulo = strval($articulo);



    if ($mysqli->connect_errno) {

        echo "Error: Fallo al conectarse a MySQL debido a: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        die("Error: " . $mysqli->connect_error . "\n");
    }

// recuperando los puntos del cliente para control y demas.
$idCliente = 0;
$puntosCliente=0;
if(isset($_SESSION['cliente'])) {
    if (is_object($_SESSION['cliente'])) {
        $clienteObj = $_SESSION['cliente'];
    } else {
        $clienteObj = $_SESSION['cliente'][0];
    }
    $idCliente = $clienteObj->Codigo;
}

    $sqlPuntosCli="SELECT 
                        pc.id_cliente,
                        ROUND(pc.saldo_premios,0) AS puntosCliente,
                        date_format(pc.vencimiento,'%d/%m/%Y') AS vencimiento
                    FROM sp_saldo_cliente_premios AS pc 
                    WHERE pc.id_cliente = " . $idCliente . " LIMIT 1;";
    
    if (!$hacerPCli = $mysqli->query($sqlPuntosCli)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sqlPuntosCli . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    }
    if ($hacerPCli->num_rows > 0) {
        $pcli = mysqli_fetch_object($hacerPCli);
        $puntosCliente=$pcli->puntosCliente;
    }

    // recupero el total de puntos en la canasta para control
    $puntosCanasta=0;
//    echo "<pre>";
//    print_r($_SESSION["carrito"]);
//    print_r($_SESSION["cantidad"]);
    if(isset($_SESSION["carrito"])){
        foreach($_SESSION["carrito"] as $ppunto){
//            print_r($ppunto);
//            echo "<br>";
            $puntosCanasta +=$ppunto;
        }
    }
    
    
    
//    exit();
    
   // recupero de datos del premio
    $sql = "SELECT id_abm_premios,
            id_categoria_abm_premios,
            nombre_premios,
            descripcion_premios,
            ROUND(puntos_premios,0) AS puntos_premios,
            ROUND(saldo_premios,0) AS saldo_premios,
            vigencia_premios,
            anulado
            FROM sp_abm_premios 
            WHERE id_abm_premios=" . $articulo;

    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    }
    if ($resultado->num_rows > 0) {

        $Registro = mysqli_fetch_object($resultado);
        $puntos = $Registro->puntos_premios;
        $saldo_premios = $Registro->saldo_premios;
        $NombrePremio = $Registro->nombre_premios;
    }

// avisar si es error o exito para mostrar el mensaje.

    if (isset($_REQUEST['articulo'])) {

        // control de saldo
        if ($_REQUEST['agregar'] > $saldo_premios)
            die(json_encode(array("estado"=>'error',"Mensaje" => "No puedo agregar el premio, La cantidad a canjear: [" . $_REQUEST['agregar'] . "] supera al saldo: [" . $saldo_premios . "] unidades ")));

       // control de puntos en carrito menos los puntos que hay.
       
        // no hay puntos en la canasta pero puedo estar canjeando de mas!
        $puntosCanasta += $_REQUEST["agregar"] * $puntos;
        
        
        if($puntosCanasta>$puntosCliente){
            die(json_encode(array("estado"=>'error',"Mensaje" => "No puedo agregar el premio, puntos insuficientes  Canje:[" . $puntosCanasta . "] y sus puntos [" . $puntosCliente . "]")));
        }
        

        if (isset($_SESSION['carrito'][$articulo])) {

            $_SESSION['carrito'][$articulo] += $_REQUEST['agregar'] *$puntos;
            //$_SESSION['puntos'][$articulo] = $puntos;

            if (!isset($_SESSION['cantidad'][$articulo]))
                $_SESSION['cantidad'][$articulo] = $_REQUEST['agregar'];
            if (isset($_SESSION['cantidad'][$articulo])) {
                //var_dump($_SESSION['cantidad'][$articulo]);
                $_SESSION['cantidad'][$articulo] = $_SESSION['cantidad'][$articulo] + $_REQUEST['agregar'];
            }


            //canasta de canje hacer una CANASTA UNICA donde se pongan las propiedades... 
        } else {
            $_SESSION['carrito'][$articulo] = $_REQUEST['agregar'] * $puntos;
            $_SESSION['cantidad'][$articulo] = $_REQUEST['agregar'];
            $_SESSION['puntos'][$articulo] = $puntos;
            $_SESSION['premios'][$articulo] = $NombrePremio;
            
        }

//$msg =  'Puntos Canjeados: '.$puntos.' x '.$_REQUEST['agregar'];
        if ($_REQUEST['agregar'] > 1)
            $msg = 'Se ha agregado '.$NombrePremio.' Cant:[' . $_REQUEST['agregar'] . '] unidades x [' . $puntos . '] ptos,  total: [' . ($_REQUEST['agregar'] * $puntos).'] pts';
        if ($_REQUEST['agregar'] == 1)
            $msg = 'Se ha agregado <i> ' . $NombrePremio . ' </i> de ' . $puntos . ' puntos';
    }
}


/*
 * ELIMINAR ITEM DE CANASTA DE PREMIO
 */
if (isset($_REQUEST['eliminar'])) {

    $vector = $_SESSION['carrito'];
    unset($vector[$_REQUEST['eliminar']]);

    if (isset($_SESSION['cantidad'][$_REQUEST['eliminar']]))
        unset($_SESSION['cantidad'][$_REQUEST['eliminar']]);
    $_SESSION['carrito'] = $vector;

    $msg = "Eliminacion Correcta de " . $_REQUEST['eliminar'];
}

/*
 * VACIAR LA CANASTA DE PREMIOS COMPLETA
 */
if (isset($_REQUEST['vaciar'])) {
    if (isset($_SESSION['carrito'])) {
        unset($_SESSION['carrito']);
        $msg = "Carrito Eliminado";
        //print_r($_SESSION['carrito'] );
    }
}









if (isset($_SESSION['carrito'])) {

    $v = $_SESSION['carrito'];

    if (sizeof($v) < 1) {

        unset($_SESSION['carrito']);
        exit;
    }



    //$tt = "(".implode(",",array_keys( $v)).")";
    $tt = implode(",", array_keys($v));
    $tt = "(" . $tt . ")";

//if(!isset($mysqli))$mysqli = new mysqli('127.0.0.1', 'root', 'luc172008040','administranet');

    if ($mysqli->connect_errno) {

        echo "Error: Fallo al conectarse a MySQL debido a: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        die("Error: " . $mysqli->connect_error . "\n");
    }


    $sql = "SELECT 
                p.id_abm_premios,
                p.nombre_premios AS premio,
                
                p.descripcion_premios AS descripcion,
                c.descripcion_categoria_premios AS categoria,
                vigencia_premios AS vigencia, 
                p.puntos_premios AS puntos
               FROM sp_abm_premios AS p
               LEFT OUTER JOIN  sp_categoria_abm_premios AS c ON (p.id_categoria_abm_premios=c.id_categoria_abm_premios   )
               WHERE
               p.id_abm_premios IN " . $tt;

    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    }



    $rows = Array();
    $columna = Array();
    $cdad = Array();
    /*
      while ($property = mysqli_fetch_field($resultado)) {
      $columna[]=$property->name;
      } */

    $columna[] = 'ID';
    $columna[] = 'Premio';
    $columna[] = 'Cantidad';
//    $columna[] = 'CantidadT';
    $columna[] = 'Puntos';
    //$columna[]= array('field'=>'Cantidad','width'=>'70%','sortable' => 'true');
    $columna[] = 'Total';
//    $columna[] = 'TotalT';
    //field:'itemid',title:'Item ID',rowspan:2,width:80,sortable:tru
    $x = 0;
    $GSaldo = 0;
    while ($Registro = mysqli_fetch_object($resultado)) {
//echo $Registro->cfoto;
        // $cdad['Cantidad'] = $_SESSION['cantidad'][$Registro->id_abm_premios];
//	 $rows[]=$Registro;
        $rows[] = Array(
            'ID' => $Registro->id_abm_premios,
            'Premio' => $Registro->premio,
            'Cantidad' => $_SESSION['cantidad'][$Registro->id_abm_premios],
//            'CantidadT'=>  '<center>' . $_SESSION['cantidad'][$Registro->id_abm_premios] . '</center>',
            'Puntos'=> number_format($Registro->puntos, 0,".",""), 
            'Total' => number_format(($_SESSION['cantidad'][$Registro->id_abm_premios] * $Registro->puntos),0,".","")
            //'Boton'=>'&nbsp;&nbsp;<a style="padding:2px;color:red;" href="javascript:void(0);" onclick="quitarItem('.$Registro->id_abm_premios.',\' '. $Registro->premio .'\' )" ><i class="fas fa-trash-alt fa-lg"></i> </a>'
//            'TotalT' => '<center>' . ($_SESSION['cantidad'][$Registro->id_abm_premios] * $Registro->puntos) . '</center>'
        );
        $GSaldo += $_SESSION['cantidad'][$Registro->id_abm_premios] * $Registro->puntos;
//	  $rows[$x] = array('Cantidad', $_SESSION['cantidad'][$Registro->id_abm_premios]);
//	  $rows[$x] = array('Total', $_SESSION['cantidad'][$Registro->id_abm_premios]*);
        $x++;
    }




    if (!isset($msg)) {
        echo json_encode(
                array('columnas' => $columna, 'datos' => $rows, 'TotalGeneral' => $GSaldo)
        );
    } else {
        // como hay un mensaje debo retornar el estado de todo ok.
        echo json_encode(
                array('estado'=>'ok', 'columnas' => $columna, 'datos' => $rows, 'Mensaje' => $msg, 'TotalGeneral' => $GSaldo)
        );
    }
}













/*

  $mysqli = new mysqli('127.0.0.1', 'root', 'luc172008040','administranet');

  if ($mysqli->connect_errno) {

  echo "Error: Fallo al conectarse a MySQL debido a: \n";
  echo "Errno: " . $mysqli->connect_errno . "\n";
  die( "Error: " . $mysqli->connect_error . "\n");

  }


  $sql = "select * from sp_abm_premios where id_abm_premios=1";
  if (!$resultado = $mysqli->query($sql)) {

  echo "Error: La ejecución de la consulta falló debido a: \n";
  echo "Query: " . $sql . "\n";
  echo "Errno: " . $mysqli->errno . "\n";
  die( "Error: " . $mysqli->error . "\n");

  }
  $Registro = mysqli_fetch_object($resultado);


  $rows = Array();
  $columna = Array();
  while ($property = mysqli_fetch_field($resultado)) {
  $columna[]=$property->name;

  }

  $x=0;
  while ($Registro = mysqli_fetch_object($resultado)) {
  for($y=0;$y<sizeof($columna);$y++){
  $n=$columna[$y];
  $rows[$x][$n]=$Registro->$n;
  }

  $x=$x+1;

  }

  //var_dump($rows);
  echo json_encode(array("total" => sizeof($rows),"rows" => $rows)); */
?>