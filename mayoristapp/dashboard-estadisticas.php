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

    <?php require_once 'cabecera.php'; ?>

    <link rel="stylesheet" href="_css/dashboard-estadisticas.css">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js" async></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="_scripts/dashboard-estadisticas.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<style>
    /* ENCABEZADO CON BADGE DE HORARIO */
.info-header {
display: flex;
align-items: center;
justify-content: space-between;
margin-bottom: 15px;
}

.badge-time {
font-size: 12px;
font-weight: 600;
color: #64748b;
background-color: #f1f5f9;
padding: 6px 12px;
border-radius: 20px;
border: 1px solid #e2e8f0;
}
    /* ESTILOS DE ESTRUCTURA DEL PANEL RENDIMIENTO Y DOUGHNUT */
.rendimiento-panel {
background: #ffffff;
border: 1px solid #e2e8f0;
border-radius: 12px;
padding: 20px;
margin-top: 15px;
box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.rendimiento-panel-titulo {
font-size: 14px;
font-weight: 700;
color: #475569;
text-transform: uppercase;
letter-spacing: 0.5px;
margin-bottom: 15px;
}

/* CONTENEDOR FLEXIBLE: GRÁFICO + DESGLOSE LATERAL */
.rendimiento-body {
display: flex;
flex-direction: row;
align-items: center;
justify-content: space-between;
gap: 20px;
flex-wrap: wrap;
}

/* TAMAÑO DEL CANVAS DEL DOUGHNUT (CONTROL DE ALTO/ANCHO) */
.rendimiento-graphic {
flex: 1;
min-width: 200px;
max-width: 260px;
height: 220px;
position: relative;
margin: 0 auto;
}

.rendimiento-graphic canvas {
width: 100% !important;
height: 100% !important;
}

/* DESGLOSE LATERAL EN TARJETAS */
.rendimiento-desglose {
flex: 1;
min-width: 250px;
display: flex;
flex-direction: column;
gap: 10px;
}

.desglose-card {
background-color: #f8fafc;
border: 1px solid #e2e8f0;
border-radius: 8px;
padding: 12px 16px;
display: flex;
align-items: center;
justify-content: space-between;
}

.desglose-card.total-card {
background-color: #f1f5f9;
border-color: #cbd5e1;
}

.desglose-info {
display: flex;
align-items: center;
gap: 10px;
}

.desglose-label {
font-size: 11px;
font-weight: 600;
color: #64748b;
text-transform: uppercase;
}

.desglose-valor {
font-size: 15px;
font-weight: 700;
color: #1e293b;
}

.desglose-valor-total {
font-size: 17px;
font-weight: 800;
color: #0f172a;
}

/* PUNTOS Y INSIGNIAS (BADGES) DE PORCENTAJE */
.dot {
width: 12px;
height: 12px;
border-radius: 50%;
display: inline-block;
}
.dot-blue { background-color: #2563eb; }
.dot-red { background-color: #dc2626; }

.badge {
font-size: 11px;
font-weight: 700;
padding: 4px 10px;
border-radius: 20px;
}
.badge-blue { background-color: #eff6ff; color: #1d4ed8; }
.badge-red { background-color: #fef2f2; color: #b91c1c; }
.badge-gray { background-color: #e2e8f0; color: #475569; }

/* PUNTOS E INSIGNIAS (BADGES) DE PORCENTAJE */
.dot {
width: 12px;
height: 12px;
border-radius: 50%;
display: inline-block;
}
.dot-blue { background-color: #2563eb; }
.dot-red { background-color: #dc2626; }

/* STREAMING_CHUNK:Añadiendo estilos específicos para productos más vendidos... */
.producto-cantidad {
color: #059669 !important;
font-weight: 700;
}

.productos-section .cliente-codigo {
font-size: 12px;
font-weight: 600;
color: #334155;
}

.badge {
font-size: 11px;
font-weight: 700;
padding: 4px 10px;
border-radius: 20px;
}
/* RESPONSIVO: EN PANTALLAS PEQUEÑAS SE ACOMODA VERTICALMENTE */
@media (max-width: 768px) {
.rendimiento-body {
flex-direction: column;
}
.rendimiento-graphic {
max-width: 100%;
height: 200px;
}
.rendimiento-desglose {
width: 100%;
}
}
</style>
<body>
    <div id="wrapper">

        <?php require_once $barra; ?>

        <div id="dashboard-container">
                    
					<div id="informacion" class="info-header">
						<h1 id="saludo"></h1>
						<span id="ultimaActualizacion" class="badge-time">Cargando datos...</span>
					</div>

                    <div id="spinner" class="spinnerAdm" style="display:none;">
                        <div class="spinner-border" role="status">
                            <img src="_img/logo-administranet-ecommerce.png">
                            <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
                            
                        </div>
                    </div>

                    <div id="modal-ncancelados-cliente" style="display:none;">
                        <div class="tituloVentana ">
                            <button id="cierroNcanc" class="botonNuevo black grande" style="float: right;">X</button>
                            <h1>Comprobantes no cancelados</h1>
                        </div>
                        <table id="tablaCancelados" name="tablaCancelados" cellspacing="1" style="width:98%"></table>
                    </div>

                    <div class="estadisticas-section pedidos-section">
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
                                <div class="valor" id="pedidosCancelados"></div>
                                <div class="titulo">Cancelados</div>
                            </div>
                            <div class="div-contenedor-interno">
                                <i class="fa-solid fa-receipt"></i>
                                <div class="valor" id="pedidosFacturados"></div>
                                <div class="titulo">Facturados</div>
                            </div>
                            <div class="div-contenedor-interno">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                <div class="valor" id="dineroTotal"></div>
                                <div class="titulo" id="dineroTotalLetra"></div>
                            </div>
                        </div>
                    </div>
             <!-- SECCIÓN RENDIMIENTO COMERCIAL -->
            <div class="estadisticas-section rendimiento-section">
                <div><h1><i class="fa-solid fa-chart-pie"></i> Rendimiento comercial</h1></div>
                <div><h2>Margen y descuentos de <span class="destacado" id="fechaRendimiento"></span></h2></div>
                    <!-- PANEL COMPOSICIÓN (DOUGHNUT + RESUMEN LATERAL) -->
                <div class="panel rendimiento-panel">
                    <div class="rendimiento-panel-titulo">Composición del rendimiento</div>
                    
                    <div class="rendimiento-body">
                        <!-- CANAL DOUGHNUT (CENTRADITA) -->
                        <div class="graphic rendimiento-graphic">
                            <canvas id="graficoRendimiento"></canvas>
                        </div>

                        <!-- DESGLOSE DE VALORES EN TEXTO -->
                        <div class="rendimiento-desglose">
                            <div class="desglose-card">
                                <div class="desglose-info">
                                    <span class="dot dot-blue"></span>
                                    <div>
                                        <div class="desglose-label">Ventas Netas</div>
                                        <div class="desglose-valor" id="leyendaVentasNetas">$0,00</div>
                                    </div>
                                </div>
                                <span class="badge badge-blue" id="porcVentasNetas">0%</span>
                            </div>

                            <div class="desglose-card">
                                <div class="desglose-info">
                                    <span class="dot dot-red"></span>
                                    <div>
                                        <div class="desglose-label">Descuentos Otorgados</div>
                                        <div class="desglose-valor" id="leyendaDescuentos">$0,00</div>
                                    </div>
                                </div>
                                <span class="badge badge-red" id="porcDescuentos">0%</span>
                            </div>

                            <div class="desglose-card total-card">
                                <div>
                                    <div class="desglose-label">Total</div>
                                    <div class="desglose-valor-total" id="leyendaTotalBruto">$0,00</div>
                                </div>
                                <span class="badge badge-gray">100% Total</span>
                            </div>
                        </div>
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
                    <!-- TOP 5 PRODUCTOS MÁS VENDIDOS -->
                    <div class="clientes-section productos-section">
                        <h2>Top Productos más vendidos del mes</h2>
                        <div class="panel">
                            <div class="info">
                                <ol id="productosmasvendidos" class="clientes-list"></ol>
                            </div>
                            <div class="graphic">
                                <canvas id="graficoProductosMas" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php require_once 'footer.php'; ?>
            <script>

                $(document).ready(function() {
                    var laSesion = obtenerUsuarioLogueado();
                    console.log('datos de sesion de usuario:', laSesion);
                    var usuario = <?php echo json_encode($usuario); ?>;

                    $('#saludo').html('Bienvenido/a ' + usuario);
                    mostrarMes();
                    $('#spinner').removeClass('d-none');

                    $("#cierroNcanc").on("click", function (e) {
                      e.preventDefault();
                      $("#modal-ncancelados-cliente").hide();
                      var contienes2 = $("#tablaCancelados");

                      $.modal.close();
                      contienes2.DataTable().destroy();
                    });

                    // cargarEstadisticasAsync()
                    //     .then(function() {
                    //         console.log('Estadísticas cargadas exitosamente.');
                    //     })
                    //     .catch(function(error) {
                    //         console.error('Error al cargar las estadísticas:', error);
                    //     });

                    cargarEstadisticasAsync()
                        .then(function() {
                            console.log('[Dashboard] Datos iniciales cargados exitosamente.');
                            iniciarAutoRefresco(); // Iniciar temporizador periódico (cada 2 min)
                        })
                        .catch(function(error) {
                            console.error('[Dashboard] Error al cargar las estadísticas:', error);
                        });
                });

                function mostrarMes() {
                    let meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                    const fechaActual = new Date();
                    const options = { month: 'long' };
                    const mesActual = fechaActual.toLocaleDateString('es-ES', options);
                    let añoActual = fechaActual.getFullYear();
                    let mesYAnio = `${mesActual} del ${añoActual}`;
                    $('#fecha').append(mesYAnio);
                }

                // function cargarEstadisticasAsync() {
                //     return new Promise(function(resolve, reject) {
                //         $.ajax({
                //             type: 'GET',
                //             url: 'ajax/json-estadisticas-vendedor.php',
                //             data: {
                //                 estadisticas: 'true'
                //             },
                //             success: function(response) {
                //                 $('#spinner').addClass('d-none');

                //                 var estadisticas = JSON.parse(response);
                //                 console.log(estadisticas);

                //                 renderizarRendimiento(estadisticas);

                //                 animarPedidosAsync(estadisticas.cantidadPedidos, '#pedidosRealizados');
                //                 animarPedidosAsync(estadisticas.pedidosCancelados, '#pedidosCancelados');
                //                 animarPedidosAsync(estadisticas.pedidosFacturados, '#pedidosFacturados');
                //                 animarDineroAsync(estadisticas.cantidadEfectivo);

                //                 var listaClientesMas = $('#clientesquemascompraron');
                //                 listaClientesMas.empty();

                //                 estadisticas.cincoPrimerosClientes.forEach(function(cliente) {
                //                     var listItem = $('<li></li>').addClass('cliente-item');
                //                     var clienteInfo = $('<span></span>').addClass('cliente-codigo').text(cliente.nombre + ' (Cod: ' + cliente.codigo + ')');
                //                     var saldoInfo = $('<span></span>').addClass('cliente-saldo').text(dinero.format(cliente.saldo));

                //                     listItem.append(clienteInfo, saldoInfo);
                //                     listaClientesMas.append(listItem);
                //                 });

                //                 var listaClientesMenos = $('#clientesquemenoscompraron');
                //                 listaClientesMenos.empty();

                //                 estadisticas.cincoUltimosClientes.forEach(function(cliente) {
                //                     var listItem = $('<li></li>').addClass('cliente-item');
                //                     var clienteInfo = $('<span></span>').addClass('cliente-codigo').text(cliente.nombre + ' (Cod: ' + cliente.codigo + ')');
                //                     var saldoInfo = $('<span></span>').addClass('cliente-saldo').text(dinero.format(cliente.saldo));

                //                     listItem.append(clienteInfo, saldoInfo);
                //                     listaClientesMenos.append(listItem);
                //                 });

                //                 var datosPrimeros = {
                //                     nombresClientes: [],
                //                     saldosClientes: [],
                //                     id: 'graficoClientesMas',
                //                     label: 'Saldos de Clientes que mas compraron',
                //                     tipo: 'bar'
                //                 };

                //                 estadisticas.cincoPrimerosClientes.forEach(function(cliente) {
                //                     var nombreCompletoArray = cliente.nombre.split(' ');
                //                     var nombre = nombreCompletoArray[0];
                //                     datosPrimeros.nombresClientes.push(nombre);
                //                     datosPrimeros.saldosClientes.push(cliente.saldo.toFixed(2));
                //                 });

                //                 var datosUltimos = {
                //                     nombresClientes: [],
                //                     saldosClientes: [],
                //                     id: 'graficoClientesMenos',
                //                     label: 'Saldos de Clientes que menos compraron',
                //                     tipo: 'bar'
                //                 };

                //                 estadisticas.cincoUltimosClientes.forEach(function(cliente) {
                //                     var nombreCompletoArray = cliente.nombre.split(' ');
                //                     var nombre = nombreCompletoArray[0];
                //                     datosUltimos.nombresClientes.push(nombre);
                //                     datosUltimos.saldosClientes.push(cliente.saldo.toFixed(2));
                //                 });

                //                 construirGraficoClientes(datosPrimeros);
                //                 construirGraficoClientes(datosUltimos);

                //                 resolve();
                //             },
                //             error: function(x, e) {
                //                 $('#spinner').addClass('d-none');
                //                 var s = x.status,
                //                     m = 'Ajax error: ';
                //                 if (s === 0) {
                //                     m += 'Check your network connection.' + x.status + e;
                //                 }
                //                 if (s === 404 || s === 500) {
                //                     m += s;
                //                 }
                //                 if (e === 'parsererror' || e === 'timeout') {
                //                     m += e;
                //                 }
                //                 alert(m);
                //                 reject(e);
                //             }
                //         });
                //     });
                // }
                
            </script>
        </body>
        </html>