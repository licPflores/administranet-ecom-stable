<?php 


require_once 'sesion.inc.php';
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom = 0;
$usuario = $_SESSION['apenom'];


?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="theme-color" content="#395aa2">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Ventas</title>

    <!-- en este punto se incluye el main_styles.css -->
    <?php require_once 'cabecera.php'; ?>

    <link rel="stylesheet" href="_css/dashboard-estadisticas.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js" async></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="_scripts/principal.js"></script> -->
    <script src="_scripts/dashboard-estadisticas.js"></script>

    <!-- Bootstrap CSS desde CDN -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- jQuery desde CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap JS desde CDN -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script> -->

    
    

</head>
<body>
    <div id="wrapper">

        <?php require_once $barra; ?>

        <div id="dashboard-container">
            <div id="informacion">
                <h1 id="saludo"></h1>
                
            </div>

            <div id="spinner" class="spinnerAdm" style="display:none;">
                <div class="spinner-border" role="status">
                    <img src="_img/logo-administranet-ecommerce.png">
                    <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
                </div>
            </div>

            <!--<div id="spinner" class="d-none text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <span class="ml-2">Cargando...</span>
            </div>-->

            <div id="modal-ncancelados-cliente" style="display:none;">
                    <div class="tituloVentana ">
                            <button id="cierroNcanc" class="botonNuevo black grande" style="float: right;">X</button>
                            <h1>Comprobantes no cancelados</h1>
                            
                    </div>
                    <table id="tablaCancelados" name="tablaCancelados" cellspacing="1" style="width:98%">
                    </table>
            </div>

            <div class="estadisticas-section">
                <div><h1><i class="fa-solid fa-chart-line"></i> Estadísticas</h1></div>
                <div><h2>Pedidos del mes de <span class="destacado" id="fecha"></span></h2></div>
                <div class="div-contenedor">
                    <div class="div-contenedor-interno">
                        <i class="fa-regular fa-circle-check"></i>
                        <div class="valor" id="pedidosRealizados"></div>
                        <div class="titulo">Realizados</div>
                    </div>
                    <div class="div-contenedor-interno">
                        <i class="fa-regular fa-circle-xmark"></i>
                        <div class="valor"  id="pedidosCancelados"></div>
                        <div class="titulo">Cancelados</div>
                    </div>
                    <div class="div-contenedor-interno">
                        <i class="fa-solid fa-receipt"></i>
                        <div class="valor"  id="pedidosFacturados"></div>
                        <div class="titulo">Facturados</div>
                    </div>
                    <div class="div-contenedor-interno">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <div class="valor"  id="dineroTotal"></div>
                        <div class="titulo" id="dineroTotalLetra"></div> 
                    </div>
                </div>
            </div>

            <div class="clientes-section">
                <h2>Top 5 de Clientes que más compraron</h2>
                <div class="panel">
                    <div class="info">
                        <ol id="clientesquemascompraron" class="clientes-list"></ol>
                    </div>
                    <div class="graphic">
                        <canvas class="graficoClientesMas" id="graficoClientesMas" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="clientes-section">
                <h2>Top 5 de Clientes que menos compraron</h2>
                <div class="panel">
                    <div class="info">
                        <ol id="clientesquemenoscompraron" class="clientes-list"></ol>
                    </div>
                    <div class="graphic">
                        <canvas class="graficoClientesMas" id="graficoClientesMenos" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="clientes-section">
                <h2>Top 5 Articulos mas vendidos</h2>
                <div class="panel">
                    <div class="info">
                        <ol id="articulos" class="clientes-list"></ol>
                    </div>
                    <!--<div class="graphic"> //se puede agregar un grafico de ser necesario
                        <canvas class="graficoClientesMas"></canvas>
                    </div>-->
                </div>
            </div>
 
        </div>

        
        
    </div>
<?php require_once 'footer.php';?>  
    <script>

        $(document).ready(function() {
            // Esta función se ejecutará cuando el DOM esté listo
            
                        
            var laSesion = obtenerUsuarioLogueado();
            console.log('datos de sesion de usuario:',laSesion);
            var usuario = <?php echo json_encode($usuario); ?>;
            // vamos a recuparar datos de la sesion para javascript con un ajax.propio. y hacer local storage.

            // Ahora puedes usar la variable en JavaScript.
            $('#saludo').html('Bienvenido/a ' + usuario);
            //mostrarFecha()
            mostrarMes()
            // Cargar información en el div con id "informacion"
            $('#spinner').removeClass('d-none');

           
              
            $("#cierroNcanc").on("click", function (e) {
              
              e.preventDefault();
              $("#modal-ncancelados-cliente").hide();
              var contienes2 = $("#tablaCancelados");

              $.modal.close();
              contienes2.DataTable().destroy()
              
            });


            cargarEstadisticasAsync()
                .then(function() {
                    console.log('Estadísticas cargadas exitosamente.');
                    // Aquí puedes agregar cualquier lógica adicional que quieras ejecutar después de cargar las estadísticas
                })
                .catch(function(error) {
                    console.error('Error al cargar las estadísticas:', error);
                    // Aquí puedes manejar el error de alguna manera, como mostrar un mensaje al usuario
                });


            
        });
        function mostrarMes() {
            let meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']
            const fechaActual = new Date();
            const options = { month: 'long' };
            const mesActual = fechaActual.toLocaleDateString('es-ES', options);
            let añoActual = fechaActual.getFullYear();


           
            let mes2 = ''
            let mes3 = ''


            indice = meses.indexOf(mesActual);
            if(indice==0){
                mes2 = `diciembre del ${añoActual-1}`
                mes3 = `noviembre y `;
            }
            else if(indice==1){
                mes2 = `enero `;
                mes3 = `diciembre del ${añoActual-1}, `;
            }
            else{
                mes2 =`${meses[indice-1]} `
                mes3 = `${meses[indice-2]}, `
            }


            let mes1 = ` y ${mesActual} del ${añoActual} `;

           let mesYAnio = mes3+mes2+mes1

            $('#fecha').append(mesYAnio);
        }


        function cargarEstadisticasAsync() {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    type: 'GET',
                    url: 'ajax/json-estadisticas-vendedor.php',
                    data: {
                        "estadisticas": "true",
                    },
                    success: function(response) {
                        $('#spinner').addClass('d-none');
                      
                    
                    var estadisticas = JSON.parse(response);
                    console.log(estadisticas)


                    animarPedidosAsync(estadisticas.cantidadPedidos, '#pedidosRealizados')
                            .then(function() {
                                console.log('Pedidos Realizados animados exitosamente de forma asíncrona.');
                                // Aquí puedes agregar cualquier lógica adicional que quieras ejecutar después de animar los pedidos
                            })
                            .catch(function(error) {
                                console.error('Error al animar los pedidos Realizados de forma asíncrona:', error);
                                // Aquí puedes manejar el error de alguna manera, como mostrar un mensaje al usuario
                            });
                    animarPedidosAsync(estadisticas.pedidosCancelados,'#pedidosCancelados')
                            .then(function() {
                                console.log('Pedidos Cancelados animados exitosamente de forma asíncrona.');
                                // Aquí puedes agregar cualquier lógica adicional que quieras ejecutar después de animar los pedidos
                            })
                            .catch(function(error) {
                                console.error('Error al animar los pedidos cancelados de forma asíncrona:', error);
                                // Aquí puedes manejar el error de alguna manera, como mostrar un mensaje al usuario
                            });
                    animarPedidosAsync(estadisticas.pedidosFacturados,'#pedidosFacturados')
                            .then(function() {
                                console.log('Pedidos facturados animados exitosamente de forma asíncrona.');
                                // Aquí puedes agregar cualquier lógica adicional que quieras ejecutar después de animar los pedidos
                            })
                            .catch(function(error) {
                                console.error('Error al animar los pedidos facturados de forma asíncrona:', error);
                                // Aquí puedes manejar el error de alguna manera, como mostrar un mensaje al usuario
                            });

                    animarDineroAsync(estadisticas.cantidadEfectivo)
                            .then(function() {
                                console.log('dinero animados exitosamente de forma asíncrona.');
                                // Aquí puedes agregar cualquier lógica adicional que quieras ejecutar después de animar los pedidos
                            })
                            .catch(function(error) {
                                console.error('Error al animar Dinero de forma asíncrona:', error);
                                // Aquí puedes manejar el error de alguna manera, como mostrar un mensaje al usuario
                            });


                   




                    var listaClientesMas = $('#clientesquemascompraron');

                    listaClientesMas.empty();

                    estadisticas.cincoPrimerosClientes.forEach(function(cliente) {
                        var listItem = $('<li></li>').addClass('cliente-item');
                        var clienteInfo = $('<span></span>').addClass('cliente-codigo').text(cliente.nombre+' (Cod: ' + cliente.codigo + ')');
                        var saldoInfo = $('<span></span>').addClass('cliente-saldo').text(dinero.format(cliente.saldo));

                        listItem.append(clienteInfo, saldoInfo);
                        listaClientesMas.append(listItem);
                    });
                    var listaClientesMenos = $('#clientesquemenoscompraron');

                    listaClientesMenos.empty();

                    estadisticas.cincoUltimosClientes.forEach(function(cliente) {
                        var listItem = $('<li></li>').addClass('cliente-item');
                        var clienteInfo = $('<span></span>').addClass('cliente-codigo').text(cliente.nombre+' (Cod: ' + cliente.codigo + ')');
                        var saldoInfo = $('<span></span>').addClass('cliente-saldo').text(dinero.format(cliente.saldo));

                        listItem.append(clienteInfo, saldoInfo);
                        listaClientesMenos.append(listItem);
                    });

                    


                    // INFORMACION NECESARIA PARA CREAR EL GRAFICOO

                    // CINCO PRIMEROS CLIENTES
                    var datosPrimeros = {
                        nombresClientes: [],
                        saldosClientes: [],
                        id:'graficoClientesMas',
                        //backgroundColor: 'rgba(27, 196, 48, 0.2)', // Color de fondo de las barras
                        borderColor: 'rgba(16, 161, 33, 1)', // Color del borde de las barras
                        label: 'Saldos de Clientes que mas compraron',
                    };
                    
                        estadisticas.cincoPrimerosClientes.forEach(function(cliente) {
                        var nombreCompleto = cliente.nombre;
                        var nombreCompletoArray = nombreCompleto.split(' ');
                        var nombre = nombreCompletoArray[0];

                        datosPrimeros.nombresClientes.push(nombre);
                        datosPrimeros.saldosClientes.push(cliente.saldo.toFixed(2));
                    });
                    
                    

                    //CINCO ULTIMOS CLIENTES

                    var datosUltimos = {
                        nombresClientes: [],
                        saldosClientes: [],
                        id:'graficoClientesMenos',
                        label: 'Saldos de Clientes que menos compraron',
                        //backgroundColor: 'rgba(220, 24, 11  , 0.2)', // Color de fondo de las barras
                        borderColor: 'rgba(198, 20, 8 , 1)', // Color del borde de las barras
                    };

                    estadisticas.cincoUltimosClientes.forEach(function(cliente) {
                        var nombreCompleto = cliente.nombre;
                        var nombreCompletoArray = nombreCompleto.split(' ');
                        var nombre = nombreCompletoArray[0];

                        datosUltimos.nombresClientes.push(nombre);
                        datosUltimos.saldosClientes.push(cliente.saldo.toFixed(2));
                    });


                    //CREACION DE AMBOS GRAFICOS    
                    crearGrafico(datosPrimeros)
                    crearGrafico(datosUltimos)



                    //CARGAR 5 ARTICULOS MAS VENDIDOS

                    var listaArticulos = $('#articulos');

                    listaArticulos.empty();

                    estadisticas.articulos.forEach(function(art) {
                        var listItem = $('<li></li>').addClass('cliente-item');
                        var artDesc = $('<span></span>').addClass('cliente-codigo').text(art.Descripcion+' (Cod:'+art.IDArt+')');
                        var artCant = $('<span></span>').addClass('cliente-saldo').text(parseInt(art.Cantidad));

                        listItem.append(artDesc, artCant);
                        listaArticulos.append(listItem);
                    });
                        // Aquí puedes hacer lo que quieras con las estadísticas, como mostrarlas en la página
                        resolve(); // Resolvemos la promesa una vez que se hayan cargado las estadísticas
                    },
                    error: function(x, e) {
                        
                    $('#spinner').addClass('d-none');

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
                        reject(e); // Rechazamos la promesa en caso de error
                        
                        
                    }
                });
            });
        }
                
    </script>
</body>
</html>