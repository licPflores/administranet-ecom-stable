<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php

    
    
   
    $titulo;
    $tipoComp;
    $nroComp;
    $link;
    $usuario;
    $destinatario;
    $nombreEmpresa;
    $direccionEmpresa;
    $telefonoEmpresa;
    $urlEmpresa;
    
            
?>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="p-comp.css"  />
        <title></title>
    </head>
    <body>
        <div id="cabecera">$titulo</div>
        
        <div id="cuerpo">
            <p>$destinatario</p>
            <p>$tipoComp $nrocomp</p>
            <p>Descargar comprobante aqui[$link]</p>
        </div>
        <div id="firma">
            <div id="logoFirma">
                <img src="logototal.png" class="asBlock" />';
            </div>
            <div id="textoFirma">
                <label>$vendedor</label>
                <label>$direccion</label>
                <label>$telefono</label>
                <label>$url</label>
                    
            </div>
        </div>
        
        <div id="pie">
            Mail generado por  <a>administraNET gestión e-commerce</a> LOGO
        </div>
    </body>
</html>
