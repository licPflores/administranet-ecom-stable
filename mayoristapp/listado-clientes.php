<?php
//error_reporting('E_ALL');
require_once 'sesion.inc.php';
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom = 0;
/**
 * elimino el carrito 
 **/
if (isset($_SESSION['jcart'])) {
    unset($_SESSION['jcart']);
}
/**
 * obtener el formulario destino y crear una variable de sesion.
 * @$_SESSION['formualario']:= determino que tipo de formulario destino una vez que seleccione el cliente, a saber
 * pedido, remito x sistem, remito x talonario.
 * @$_SESSION['uFormulario']:= url del formulario.
 **/

 if(isset($_SESSION['cliente'])){

    $codCliente = $_SESSION['cliente'][0]->Codigo;
    $tipoCliente = $_SESSION['cliente'][0]->TipoCliente;

}else{
    $codCliente ='';
    $tipoCliente ='';
}

$tipoNegocio = $_SESSION['tipoCliente'];

$tipoBusca = 1;
$tipoUsuario = "cliente";

if (isset($_SESSION["tipo_busqueda"])) {
    $tipoBusca = $_SESSION["tipo_busqueda"];
}
if (isset($_SESSION['tipousuario'])) {
    $tipoUsuario = $_SESSION['tipousuario'];
}

$arrCli = $_SESSION["clienteRapido"];
$cartel = "";
$formulario = "";
$cliente = "";
$uFormulario = "";
if (isset($_SESSION['cliente'])) {
    $cliente = $_SESSION['cliente'];
}
$buscaOferta = "";
if (isset($_GET['buscaOferta'])) {
    $buscaOferta = $_GET['buscaOferta'];
}

if (isset($_REQUEST['frm'])) {
    /**
     * vengo de algun formulario.
     */
    $frm = $_REQUEST['frm'];

    switch ($frm) {
        case 0:
            /*
                 * Pedido
                 */
            $_SESSION['formulario'] = 'pedido';
            $_SESSION['uFormulario'] = 'alta_pedido.php';
            break;
        case 1:
            /*
                 * Remito por sistema
                 */
            $_SESSION['formulario'] = 'remitoSistema';
            $_SESSION['uFormulario'] = 'lista-facturas-sin-stock.php';
            break;
        case 2:
            /*
                 * Remito por talonario
                 */
            $_SESSION['formulario'] = 'remitoTalonario';

            ///$_SESSION['uFormulario'] = 'alta-remito-talonario.php';
            $_SESSION['uFormulario'] = 'lista-facturas-sin-stock.php';
            break;
        case 3:
            /*
                 * Presupuesto
                 */
            $_SESSION['formulario'] = 'presupuesto';
            $_SESSION['uFormulario'] = 'alta_presupuesto.php';
            break;
        case 4:
            /*
                 * Recibos
                 */
            $_SESSION['formulario'] = 'recibo';
            //                $_SESSION['uFormulario'] = 'alta_recibo_seleccion_factura.php';
            $_SESSION['uFormulario'] = 'recibo/alta_recibo.php';
            break;
        case 5:
            /*
                 * Devolucion
                 */
            $_SESSION['formulario'] = 'devolucion';
            $_SESSION['uFormulario'] = 'alta-devolucion.php';
            break;
    }
    // si no hay un cliente, pongo el cartel, no lo pido.
    if ($cliente == "") {
        //$cartel=1;
    }

    $formulario = $_SESSION['formulario'];
    $uFormulario = $_SESSION['uFormulario'];
} else {
    //unset($_SESSION['cliente']);
    // vengo dede las ofertas asi que tengo que ir al pedido si no
    // voy a donde me elijan despues.
    if ($buscaOferta == "si") {
        $_SESSION['formulario'] = 'pedido';
        $_SESSION['uFormulario'] = 'alta_pedido.php';
        $formulario = $_SESSION['formulario'];
        $uFormulario = $_SESSION['uFormulario'];
    } else {
        unset($_SESSION['formulario']);
        unset($_SESSION['uFormulario']);
    }
}
// si el cliente ya fue seleccionado debo ir a los comprobantes directamente
// salvo en los casos en los que quiero cambiar de cliente que voy a seleccion
// de cliente anulando su seleccion.

if ($formulario != "" && $cliente != "") {
    // $uFormulario='listado-clientes.php';
    unset($_GET);
    header('Location:' . $uFormulario);
}

// echo "<pre>";

//
//print_r($objCliente);
//echo "<br>";
//print_r($_SESSION["domicilios_cliente"]);
//echo "</pre>";
//echo "<pre>";
//echo $formulario;
//echo $uFormulario;
//echo "</pre>";
//oferton

$oferta = "";
$cantidad = "";
$articulo = "";
if (isset($_GET['buscaOferta'])) {
    $oferta = $_GET["buscaOferta"];
    $cantidad = $_GET["cant"];
    $articulo = $_GET["IDArt"];
}
//tipo de busqueda por defecto del usuario

?>
<!DOCTYPE HTML>
<html>

<head>
    <meta name="theme-color" content="#395aa2">
    <title>seleccionar Cliente | administraNET e-com </title>
    <?php require_once 'cabecera.php'; ?>
    <link rel='stylesheet' type='text/css' media='screen' href='_css/basic.css' />
    
    <link rel="stylesheet" href="_css/estadisticas-cliente.css">
    <link rel="stylesheet" href="_css/dashboard-estadisticas.css">
    <!-- <link rel='stylesheet' type='text/css' media='screen' href='_css/busqueda-rapida.css' /> -->

    <!-- estilo de autocompletar busqueda de articulos -->
    <script>
        
        // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
        // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
        
        $(document).ready(function() {
            
            //activar y desactivar el boton de busqueda rapida.
            inicioAutoCompletar("cliente");    

            $('#nombreBuscaRapido').focus();
            
            $('#spinner-estadistica').css('display', 'block');
            mostrarMes()
            
            $("#cierroNcanc").on("click", function (e) {
              
              e.preventDefault();
              $("#modal-ncancelados-cliente").hide();
              var contienes2 = $("#tablaCancelados");

              $.modal.close();
              contienes2.DataTable().destroy()
              
            });

            $('#altaClienteR').on("click", function() {
                // console.log("hice click");
                location.href = "alta-mod-cliente-rapida.php?accion=alta";
            });
            var accionCliente= findGetParameter('accion');
            console.log('accion cliente: ',accionCliente);
            if(accionCliente=='cambiar'){
                cambiarCliente();
            }
            <?php if (isset($objCliente) && is_object($objCliente)) : ?>
                $('.buscador').hide();
            <?php endif; ?>
            var opcionCartel = 0;

            if (opcionCartel = findGetParameter('cartel')) {
                console.table('Cartel_GET', opcionCartel);

            }

            if(opcionCartel!=0 && opcionCartel==5 || opcionCartel=='6'){
                let tipo= findGetParameter('tipo');
                let comprobante=findGetParameter('comp');

                if(opcionCartel=='6'){
                    Swal.fire("", tipo+": "+comprobante + "e-mail enviado correctamente", "success");

                }

                if(opcionCartel=='5'){
                    Swal.fire("", tipo+": "+comprobante +"No se envio e-mail", "warning");

                }
            }
            let codCliente = '<?php echo addslashes($codCliente); ?>'

            if(codCliente){


                cargarEstadisticasAsync(codCliente)
                .then(function() {
                    console.log('Estadísticas cargadas exitosamente.');
                    // Aquí puedes agregar cualquier lógica adicional que quieras ejecutar después de cargar las estadísticas
                })
                .catch(function(error) {
                    console.error('Error al cargar las estadísticas:', error);
                    // Aquí puedes manejar el error de alguna manera, como mostrar un mensaje al usuario
                });


       
                

            }

        });


         
        function cargarEstadisticasAsync(codCliente) {
            return new Promise(function(resolve, reject) {

                    $.ajax({
                        type: 'GET',
                        url: 'json-estadisticas-cliente.php',
                        data:{
                            "estadisticas-cliente" : "true",
                            "cliente" : codCliente,
                            "tipoCliente" : '<?php echo addslashes($tipoCliente); ?>'
                            
                        },
                        success: function(response) {

                            $('#spinner-estadistica').css('display', 'none');
                            // Rellenar el número de pedidos realizados
                            $('#pedidosRealizados').text(response.totalPedidosRealizados);

                            // Rellenar el número de pedidos facturados
                            $('#pedidosFacturados').text(response.totalPedidosFacturados);                 

                            // Rellenar los top 5 artículos más vendidos
                            var articulosMasVendidos = response.articulosMasVendidos;
                            var listaArticulosMasVendidos = $('#articulosMasVendidos');
                            listaArticulosMasVendidos.empty();
                            $.each(articulosMasVendidos, function(index, articulo) {
                                var listItem = $('<li></li>').addClass('cliente-item');
                                var artDesc = $('<span></span>').addClass('cliente-codigo').text(articulo.Descripcion+' (Cod:'+articulo.IDArt+')');
                                var artCant = $('<span></span>').addClass('cliente-saldo').text(parseInt(articulo.cantidad));

                                listItem.append(artDesc, artCant);
                                listaArticulosMasVendidos.append(listItem);


                            
                            });

                            // Rellenar los top 5 artículos menos vendidos
                            var articulosMenosVendidos = response.articulosMenosVendidos;
                            var listaArticulosMenosVendidos = $('#articulosMenosVendidos');
                            listaArticulosMenosVendidos.empty();
                            $.each(articulosMenosVendidos, function(index, articulo) {
                                var listItem = $('<li></li>').addClass('cliente-item');
                                var artDesc = $('<span></span>').addClass('cliente-codigo').text(articulo.Descripcion+' (Cod:'+articulo.IDArt+')');
                                var artCant = $('<span></span>').addClass('cliente-saldo').text(parseInt(articulo.cantidad));

                                listItem.append(artDesc, artCant);
                                listaArticulosMenosVendidos.append(listItem);
                            });

                            // Rellenar los top 5 artículos más vendidos por tipo de cliente
                            var articulosTipoCliente = response.articulosTipoCliente;
                            var listaArticulosTipoCliente = $('#articulosTipoCliente');
                            listaArticulosTipoCliente.empty();
                            $.each(articulosTipoCliente, function(index, articulo) {
                                var listItem = $('<li></li>').addClass('cliente-item');
                                var artDesc = $('<span></span>').addClass('cliente-codigo').text(articulo.Descripcion+' (Cod:'+articulo.IDArt+')');
                                var artCant = $('<span></span>').addClass('cliente-saldo').text(parseInt(articulo.cantidad));

                                listItem.append(artDesc, artCant);
                                listaArticulosTipoCliente.append(listItem);
                            });

                            resolve();
                            
                        },
                        error: function(x, e) {

                            $('#spinner-estadistica').css('display', 'none');

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
                                reject(e);
                        }
                    });
                });
            }


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


    </script>
</head>

<body>
    <div id="wrapper">
        <?php require_once $barra; ?>

        <?php if ($tipoUsuario == "vendedor") : ?>


           
            <div class="paneles" id="buscador-cliente">
                
                <h3 class="paneles-titulo">
                    <div><i class="fa-solid fa-image-portrait fa-lg"></i> Seleccionar Cliente:</div>

                    <?php if ($_SESSION["permiso_alta_cliente"] == "Si") : ?>
						<div>
							<button type="button" id="altaClienteR" class="botonNuevo floatRight">
								<i class="fas fa-user-plus fa-lg"></i> Nuevo
							</button>
						</div>
                	<?php endif; ?>

                </h3>
                
                <form method="post" action="" id="formBusca">

                    <!-- <div id="divBuscaArticulo"> -->

                    <div class="barra-busqueda">

                        <input type="search" id="nombreBuscaRapido" name="nombreBuscaRapido" placeholder="nombre o id ..." class="input-buscar-rapido" autocomplete="off" />
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscarRapido" name="botonBuscarRapido" class="boton-busca-rapido">
                            <i class="fab fa-sistrix"></i> Buscar
                        </button>

                    </div>

                    <!-- </div> -->
                    <input type="hidden" name="itemId" id="itemId">
                </form>

                <input type="hidden" name="buscaOferta" id="buscaOferta" value="<?php echo $oferta; ?>">
                <input type="hidden" name="IDArt" id="IDArt" value="<?php echo $articulo; ?>">
                <input type="hidden" name="cant" id="cant" value="<?php echo $cantidad; ?>">
            </div>
        <?php endif; ?>

        <div id="spinner" class="spinner" style="display:none;">
            <img src="_img/logo-administranet-ecommerce.png" />
        </div>
        <div class="paneles" id="contiene-tabla" style="display:none;">
            <table class="display compact tabla-712px" cellspacing="1" id="myTable"></table>
        </div>

        <!-- <div class="paneles" >
            <span>estadisticas personales</span>
            <span>Botones</span>
            inventario, informes, premios, mi cuenta

        </div> -->

        <?php if (isset($objCliente) && is_object($objCliente)) : ?> 

			<div class="dashboard-container">
				<div class="dashboard-title">
					<i class="fa fa-th-large"></i> Comprobantes
				</div>

				<div class="cards-grid">

					<?php if ($tipoUsuario == "vendedor") : ?>

						<a href="javascript:void(0);" onclick="miFormulario(3)">
							<div class="card-modulo presupuesto">
								<div class="card-icon"><i class="fas fa-file-invoice fa-fw"></i></div>
								<div class="card-title">Presupuesto</div>
							</div>
						</a>
						
						<a href="javascript:void(0);" onclick="miFormulario(0)">
							<div class="card-modulo pedido">
								<div class="card-icon"><i class="fas fa-shipping-fast fa-fw"></i></div>
								<div class="card-title">Pedido</div>
							</div>
						</a>
						
						<a href="javascript:void(0);" onclick="miFormulario(5)">
							<div class="card-modulo devolucion">
								<div class="card-icon"><i class="fa-solid fa-truck-arrow-right fa-flip-horizontal fa-fw"></i></div>
								<div class="card-title">Devolución</div>
							</div>
						</a>

						<?php if ($_SESSION["usaRemito"] == "Si") : ?>

							<a href="javascript:void(0);" onclick="miFormulario(5)">
								<div class="card-modulo">
									<div class="card-icon"><i class="fa-solid fa-dolly fa-fw"></i></div>
									<div class="card-title">Remito Talonario</div>
								</div>
							</a>

							<a href="javascript:void(0);" onclick="miFormulario(5)">
								<div class="card-modulo">
									<div class="card-icon"><i class="fa-solid fa-dolly"></i></div>
									<div class="card-title">Remito Sistema</div>
								</div>
							</a>

						<?php endif; ?>
						
						<a href="javascript:void(0);" onclick="miFormulario(4)">
							<div class="card-modulo">
								<div class="card-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
								<div class="card-title">Recibo</div>
							</div>
						</a>

					<?php else : ?>

						<a href="javascript:void(0);" onclick="miFormulario(0)">
							<div class="card-modulo">
								<div class="card-icon"><i class="fas fa-file-invoice"></i></div>
								<div class="card-title">Pedido</div>
							</div>
						</a>

					<?php endif; ?>
				</div>
			</div>

			<!-- Estamos usando la verrsion nueva del dashboard para mostrar las estadisticas del cliente, por eso comento el bloque de comprobantes que tenia antes. -->
            <!--<div id="clienteOk" class="paneles">
                <h3 class="paneles-titulo">Comprobantes</h3>


                <ul class="listaBotonesC">
                    <?php if ($tipoUsuario == "vendedor") : ?>
                        <li> <a href="javascript:void(0);" onclick="miFormulario(3)">

                                <span class="icono"><i class="fas fa-file-invoice fa-fw"></i> </span>
                                <span class="icono-texto">Presupuesto</span>

                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" onclick="miFormulario(0)">

                                <span class="icono">
                                    <i class="fas fa-shipping-fast fa-fw"></i>
                                </span>
                                <span class="icono-texto">Pedido</span>

                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" onclick="miFormulario(5)">

                                <span class="icono"><i class="fa-solid fa-truck-arrow-right fa-flip-horizontal fa-fw"></i></span>
                                <span class="icono-texto">Devolución</span>

                            </a>
                        </li>
                        <?php if ($_SESSION["usaRemito"] == "Si") : ?>

                            <li>
                                <a href="javascript:void(0);" onclick="miFormulario(2)">

                                    <span class="icono"><i class="fa-solid fa-dolly fa-fw"></i> </span>
                                    <span class="icono-texto">Remito Talonario</span>

                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0);" onclick="miFormulario(1)">

                                    <span class="icono"> <i class="fa-solid fa-dolly"></i> </span>
                                    <span class="icono-texto">Remito Sistema</span>

                                </a>
                            </li>

                        <?php endif; ?>
                        <li>
                            <a href="javascript:void(0);" onclick="miFormulario(4)">

                                <span class="icono"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                                <span class="icono-texto">Recibo</span>

                            </a>
                        </li>
                    <?php else : ?>
                        <li>
                            <a href="javascript:void(0);" onclick="miFormulario(0)">

                                <span class="icono"><i class="fas fa-file-invoice"></i> </span>
                                <span class="icono-texto">Pedido</span>

                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
               


            </div>-->

            <div class="paneles" id="dashboard-container">




            <div id="spinner-estadistica" class="spinnerAdm" >
                <div class="spinner-border" role="status">
                    <img src="_img/logo-administranet-ecommerce.png">
                    <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
                </div>
            </div>

            
   
            <div class="estadisticas-section">

				<h2><i class="fa-solid fa-chart-line"></i> Estadísticas del mes de <span id="fecha"></span></h2>
				<hr>
				<h3>Pedidos<span ></span></h3>  

                <div class="div-contenedor">
                    
                    <div class="div-contenedor-interno">
                        <i class="fa-regular fa-circle-check"></i>
                        <!-- <p>Realizados: <span id="pedidosRealizados"></span></p> -->
                        <div class="valor" id="pedidosRealizados"></div>
                        <div class="titulo">Realizados</div>
                    </div>
					
                    <div class="div-contenedor-interno">
                        <!-- <p>Facturados: <span id="pedidosFacturados"></span></p> -->
                        <i class="fa-solid fa-receipt"></i>
                        <div class="valor"  id="pedidosFacturados"></div>
                        <div class="titulo">Facturados</div>
                    </div>

                </div>
            </div>


            <div class="clientes-section">
                <h2>Top 20 Artículos más vendidos (cantidades)</h2>
                <div>
                    <ol id="articulosMasVendidos" class="clientes-list"></ol>
                </div>
            </div>

            <div class="clientes-section">
                <h2>Top 5 Artículos menos vendidos (cantidades)</h2>
                <div>
                    <ol id="articulosMenosVendidos" class="clientes-list"></ol>
                </div>
            </div>

            <div class="clientes-section">
                <h2> top 10 artículos más vendidos por mismo tipo de negocio "<?php echo $tipoNegocio; ?>" (cantidades): </h2>
                <div>
                    <ol id="articulosTipoCliente" class="clientes-list"></ol>
                </div>
            </div>
            
    


</div>

        <?php endif; ?>
        <div id="modal-ncancelados-cliente" style="display:none;">
            <div class="tituloVentana ">
                    <button id="cierroNcanc" class="botonNuevo black grande" style="float: right;">X</button>
                    <h1>Comprobantes no cancelados</h1>
                    
             </div>
            <table id="tablaCancelados" name="tablaCancelados" cellspacing="1" style="width:98%"></table>
        </div>

        <?php require_once 'footer.php'; ?>
        
</body>

</html>