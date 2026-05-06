<?php

$vector = Array();


if (isset($_REQUEST['id'])) {
    require_once 'preinclude.php';

    if ($mysqli->connect_errno) {

        echo "Error: Fallo al conectarse a MySQL debido a: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        die("Error: " . $mysqli->connect_error . "\n");
    }


    $sql = "select * from sp_fotos_premios  where anulado='No' AND id_abm_premios=" . $_REQUEST['id'] . " limit 1";

    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    } else {
        if ($mysqli->affected_rows < 1) {
            $sql = "select 0 as puntos_premios";
            $resultado = $mysqli->query($sql);
        }



        while ($Registro = mysqli_fetch_object($resultado)) {
            $url =$Registro->url_foto;
            $fotito="";
            if(isset($_REQUEST["size"])){
                $f= explode('.', $url);
                $ult= count($f)-2;
                $f[$ult]=$f[$ult].$_REQUEST["size"];
                $fotito = implode('.', $f);
            }
            
            $vector[] = array('url' => $fotito);
        }

        //$puntos = $Registro->url_fotos;
    }
}
echo json_encode($vector);

