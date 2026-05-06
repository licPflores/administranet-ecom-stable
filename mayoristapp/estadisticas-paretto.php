<?php 


require_once 'sesion.inc.php';
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom = 0;
$usuario = $_SESSION['apenom'];

// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="theme-color" content="#395aa2">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadisticas Paretto | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

 
    <?php require_once 'cabecera.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="_scripts/estadisticas-paretto.js"></script>
    <script src="_scripts/acciones-informes-gerenciales.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>

 <link href="_css/main_styles.css" rel="stylesheet" type="text/css">
    <link href="_css/estadisticas-paretto.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    
    

</head>
<body>
    <div id="wrapper">

        <?php require_once $barra; ?>

  
        <div class="paneles filtroInformes">
            <h1>Estadisticas</h1>

            <div class='panelesBloqueInforme'>

                 <div class="control">
                        <label for="tipoListado" class="parametros">Tipo Listado: </label>
                        <select name="tipoListado" id="tipoListado" required="required">
                            <option value="categoria" selected>Cobranzas vs Gastos</option>
                            <option value="rubros" >Rubros (informe Paretto)</option>
                            <option value="clientes">Clientes (informe Paretto)</option>
                           
                        </select>
                    </div>


                    <div class="titulo"> Filtros </div>
                    <div class="control w100p" >
                        <h3 class="mensajeFiltro"  id="mensajeFiltro">No es necesario usar filtros</h3> 


                        <div class="control">
                        <select name="elegirFiltro" id="elegirFiltro" required="required" style="display: none;">
                            <option value="todos" selected>Todos</option>
                            <option value="elegir">Elegir</option>
                           
                        </select>
                    </div>

                        <div class="control w100p" id="seleccionFiltroControl" style="display: none;">
                           
                            <input id="seleccionFiltro" alt="" autocomplete="off" type="search" placeholder="nombre o codigo..." >
                            <label class="parametros">
                                <button name="agregarFiltro" id="agregarFiltro" class="botonNuevo" type="button">
                                    <i class="fas fa-plus fa-lg fa-fw" aria-hidden="true"></i>
                                </button>
                            </label>
                        </div>

                        
                        <div class="control w100p">

                            <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"  style="display: none;"> 
                                <label for="listaFiltro" class="parametros">Aplicados: </label>
                            </ul>
                            <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">


                        </div>


                    </div>




                    <div class="titulo"> Periodo de tiempo </div>
                    <div class="control w100p">

                            <div class="panelesBloqueInforme">
                                <select name="periodo" id="periodo">
                                    <option value="anual">Anual</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="trimestral">Trimestral</option>
                                    <option value="mensual">Mensual</option>
                                </select>
                                </div>

                                <div class="panelesBloqueInforme" id="opcionesDinamicas"> </div>


                    </div>


            </div>

            <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                        <button title="Generar" alt="Generar" type="button" id="botonGenerar" name="botonGenerar" class="botonNuevo">
                            <i class="fas fa-check fa-lg fa-fw"></i> Generar
                        </button>
                    </span>
                </div>



                <div class='panelesBloqueInforme' id="divGraficos">

                    <h1>Grafico <span id='nombreGrafico'></span></h1>

                    <canvas id="graficoBarraCategoria" width="50" height="50"></canvas>
                    <canvas id="graficoBarra" width="400" height="300"></canvas>
                    <canvas id="graficoTorta" width="300" height="300"></canvas>

                </div>

                <div id="contiene-tabla" style="display:none;">   

                    <div class="panelesBloqueInforme" id="graficoDiv">

                    <div id="tablaDiv">


                    <h1>Tabla de Datos</h1>
                    <table   cellspacing="1" id="myTable">
                    <tr>
                        <th>Categoría</th>
                        <th>Total</th>
                    </tr>
                    
                    </table>
            </div>

        </div>

        <?php require_once 'footer.php';?>  
        
    </div>

    <script>

        $(document).ready(function() {

            
            let tipoListado = 'categoria'
            let elegirFiltro = 'todos'

            $("#tipoListado").change(function() {

                    tipoListado = $(this).val(); 
                    $('#listaFiltro').empty();

                    if (tipoListado === "rubros"  || tipoListado === "clientes") {
                        // Mostrar los elementos cuando se selecciona "elegir"
                        $("#mensajeFiltro").hide();
                        $("#elegirFiltro").show();

                        let filtro = tipoListado.slice(0, -1);
                        listado = $('#seleccionFiltro');

                        $.ajax({
                              type: 'POST',
                              url: 'relay-ventas-netas-gerencia.php',
                              data: {
                                  "ajax": "true",
                                  "tabla": filtro,
                                  "queInforme": "seleccion",
                                  "mostrarClientes":"todos"
                              },
                              success: function(response) {
                                try {
                                  
                                  var listaVuelta = jQuery.parseJSON(response);
                                  listado.val("");
  
                                  $("#seleccionFiltro").autocomplete({
                                      source: listaVuelta,
                                      
                                      select: function(event, ui) {
                                          event.preventDefault();
                                                          
                                          $(this).attr("alt", ui.item.value);
                                          $(this).val(ui.item.label);
                                                       
                                      }
                                      
                                  });
                                  listado.focus();  
                                 
                              } catch (error) {
                                  console.error("Error al parsear la respuesta JSON:", error);
                              }
                              },
                              error: function(x, e) {
                                  var s = x.status,
                                      m = 'Ajax error: ';
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
                          });


                             $("#elegirFiltro").change(function() {

                                elegirFiltro = $(this).val();
                                if(elegirFiltro === "elegir"){
                                    $("#seleccionFiltroControl").show();
                                    $("#listaFiltro").show();

                                    
                                }else{
                                    $("#seleccionFiltroControl").hide();
                                    $("#listaFiltro").hide();
                                }
                            });

                    } else {
                        // Ocultar el segundo select en cualquier otra selección
                        $("#elegirFiltro").hide();
                        $("#mensajeFiltro").show(); // Mostrar el mensajeFiltro
                        $("#seleccionFiltroControl").hide();
                        $("#listaFiltro").hide();
                    }
            });




                    var opcionSeleccionada = 'anual';
                    var opcionesSeleccionadas = [];
                    // Llamar a la función para crear el select anual al cargar la página
                    crearSelectAnual();

                    $('#periodo').change(function() {
                        opcionSeleccionada = $(this).val();
                        crearSelect(opcionSeleccionada);
                    });

 

            $('#agregarFiltro').on('click', function() {

                        var listaFiltro = $('#listaFiltro'),
                            textFiltro = $('#filtroSelec'),
                            
                            
                            seleccion = $('#seleccionFiltro').attr("alt").split("|");
                        var tFiltro = textFiltro.val();


                        elegirFiltro = $(this).val();                                   
                        let filtro = tipoListado.slice(0, -1);


                        //agregar item a la lista
                        var indiceLi = listaFiltro.children().length + 1;

                        // sin item para agregar
                        if(seleccion=="" || seleccion[1] == undefined){
                            Toast.fire({
                                icon: "warning",
                                text: "Debe seleccionar un Item de la lista",
                                });

                            return false;
                        }

                        // al menos un item de filtro seleccionado
                        if (seleccion !== "" && seleccion[1] !== undefined) {
                            var valorLi = seleccion[0]; // Valor a agregar al <li>
                            listaFiltro.append('<li id="' + indiceLi + '" data-value="' + valorLi + '"> <i class="fas fa-check-square fa-lg fa-fw"></i>   <strong>' + seleccion[1] + '</strong> <a class="borrarLi" rel="listaFiltro|' + indiceLi + '" href="#" title="Eliminar de la lista"><i class="fas fa-trash fa-lg fa-fw"></i></a></li>');
                            tFiltro = seleccion[0];
                            textFiltro.val(tFiltro);
                            $('.borrarLi').on('click', function() {
                                var valorLi = $(this).attr("rel").split("|"),
                                    textFiltro = $('#filtroSelec'),
                                    arrFiltro = $('#filtroSelec').val().split("||");
                                var ul = valorLi[0],
                                    li = valorLi[1] - 1,
                                    liObj = valorLi[1],
                                    textoFiltro = "";

                                for (var po in arrFiltro) {
                                    if (arrFiltro[po] != "") {
                                        var arrLinea = arrFiltro[po].split('|');
                                        var iLi = arrLinea[3] - 1;
                                        if (iLi != li) {

                                            textoFiltro = textoFiltro + arrFiltro[po] + "||";
                                        }
                                    }
                                }
                                textFiltro.val(textoFiltro);
                                $('#' + ul + ' #' + liObj).remove();
                            });
                        }

                        $('#seleccionFiltro').attr("alt", "");
                        $('#seleccionFiltro').val("");
                        // agregar al input una lista
                        
            });

                
                




                $("#botonGenerar").click(function() {

                    let buscar =$(this);
                    //buscar.prop('disabled', true);
                    buscar.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere');
                    const opcionSeleccionada = $("#tipoListado").val();

                    


                    let valores = [] // valores para filtrado -cod rubro y cod cliente
                    $('#listaFiltro li').each(function() {
                        // Obtenemos el valor del atributo de datos personalizado 'data-value'
                        var valorLi = $(this).data('value');
                        
                        valores.push(valorLi); 
                    });
                    //$('#listaFiltro').empty(); //vacio ul

                    tipoEstadisticas = $('#tipoEstadistica').val()

                    opcionesPeriodo = []; 

                    periodo = $('#periodo').val()
                    $('#nombreGrafico').text(capitalizeFirstLetter(periodo))
                    opcionesPeriodo.push(periodo);

                    $('#opcionesDinamicas select').each(function(index) {

                        var opcion = $(this).val();
                        opcionesPeriodo.push(opcion); 
                        
                    });
                    
                    


                            
                    $.ajax({

                            type: 'GET',
                            url: 'ajax/json-estadisticas-paretto.php',
                            data:obtenerDataAjax(tipoListado,elegirFiltro,opcionesPeriodo,valores),
                            success: function(response) {


                                if(tipoListado == 'rubros'){
                                    
                                    GraficoRubros(response)
                                }
                                if(tipoListado == 'clientes'){
                                    GraficoClintes(response)
                                }
                                if(tipoListado == 'categoria'){
                                    
                                    GraficoCategoria(response)
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
                    buscar.html('<i class="fas fa-check fa-lg fa-fw"></i> Generar'); 
                });
            });


        function mostrarMes() {
            const fechaActual = new Date();
            const options = { month: 'long' };
            const mesFormateado = fechaActual.toLocaleDateString('es-ES', options);
            const añoActual = fechaActual.getFullYear();

            const mesYAnio = `${mesFormateado} del ${añoActual}`;

            $('#fecha').append(mesYAnio);
        }


        function obtenerDataAjax(tipoListado,elegirFiltro,opcionesPeriodo, valores){

            let tListado = ''
            let eFiltro = 'todos'
            let valoresFiltro = []

            if(tipoListado === 'categoria'){
                tListado = 'categoria'
            }
            if(tipoListado === 'rubros' || tipoListado === 'clientes'){
                tListado = tipoListado

                elegirFiltro = $("#elegirFiltro").val(); 
                eFiltro = elegirFiltro


            } 

            let data = {
                'estadisticasParetto':1,
                'tipoListado' : tListado,
                'elegirFiltro' : eFiltro,
                'opcionesPeriodo' : opcionesPeriodo,
                'valoresFiltro': valores
            }

            return data

        }





        


        
                
    </script>
</body>
</html>