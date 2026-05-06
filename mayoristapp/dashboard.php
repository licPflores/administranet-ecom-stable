<?php

require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];

/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$usaZoom       =0;
?>


<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>estadisticas paretto | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?> 
    <style media="print">


        .noPrint{ display: none; }
        .yesPrint{ display: block !important; }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="_scripts/estadisticas-anuales.js"></script>
    <link href="_css/main_styles.css" rel="stylesheet" type="text/css">
    <link href="_css/estadisticas-anuales.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content" class="noPrint">


            <div class="paneles filtroInformes">   

                <h1 for="estadistica">Elige periodo de tiempo para realizar el informe:</h1>

                <div class="panelesBloqueInforme">
                <select name="tipoEstadistica" id="tipoEstadistica">
                    <option value="clientes">Clientes</option>
                    <option value="rubros">Rubros</option>
                </select>
                </div>

                <div class="panelesBloqueInforme">
                <select name="estdistica" id="estadistica">
                    <option value="anual">Anual</option>
                    <option value="semestral">Semestral</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="mensual">Mensual</option>
                </select>
                </div>

                <div class="panelesBloqueInforme" id="opcionesDinamicas"> </div>
                
                <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                  
                        <button id="buscar" class="botonNuevo">Buscar</button>
                    </span>
                </div>       
                
            </div>
            <canvas id="graficoBarra" width="200" height="200">></canvas>
            <canvas id="graficoTorta" width="200" height="200">></canvas>

    </div>
</div>
    
        
</body>

<script>

    $(document).ready(function(){

        var opcionSeleccionada = 'anual';
        var opcionesSeleccionadas = [];
        // Llamar a la función para crear el select anual al cargar la página
        crearSelectAnual();

        $('#estadistica').change(function() {
            opcionSeleccionada = $(this).val();
            crearSelect(opcionSeleccionada);
        });


        $('#buscar').click(function() {


            let buscar =$(this);
            buscar.prop('disabled', true);
            buscar.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere');
            $('#lista-datos').empty();

            tipoEstadisticas = $('#tipoEstadistica').val()

            opcionesSeleccionadas = []; 

            $('#opcionesDinamicas select').each(function(index) {

                var opcion = $(this).val();
                opcionesSeleccionadas.push(opcion); 
                
            });

            //Aca va ajax

              $.ajax({

                    type: 'GET',
                    url: 'json-dashboard.php',
                    data:{

                        "estadisticas" : '1',
                        "tipoEstadisticas": tipoEstadisticas,
                        "opcionTiempo" : opcionSeleccionada,
                        "periodoTiempo" : opcionesSeleccionadas
                        
                    },
                    success: function(response) {

                        var datos = JSON.parse(response);

                        


                        if(tipoEstadisticas == 'rubros'){
                            GraficoRubros(datos)
                        }
                        if(tipoEstadisticas == 'clientes'){
                            GraficoClintes(datos)
                        }
                    

                    },
                        error: function(x, e) {

                        
                                var s = x.status, 
                                        m = 'Ajax error: ' ; 
                                if (s === 0) {
                                        m += 'Check your network connection.' + x.status + e;
                                }
                                if (s === 404 || s === 500) {
                                        m += s;
                                }
                                if (e === 'parsererror' || e === 'timeout') {
                                        m += e;
                                }
                                alert(m);
                        }

                })

            buscar.prop('disabled', false);
            buscar.html('Buscar'); 

        })


      
    })

</script>
</html>


