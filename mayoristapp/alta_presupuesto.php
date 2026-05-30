<?php
ini_set("display_errors", 1);

session_start();
$caminoDispo = $_SESSION['caminoDisp'];
//if (isset($_SESSION["jcart"])){
//    unset($_SESSION["jcart"]);
//   
//}
$_SESSION["totalCarrito"] = 0;

session_write_close();
$soyMovil = "No";
if ($caminoDispo != "") {
	$soyMovil = "Si";
}


$comprobante = "PRE";

require_once 'jcart/jcart.php';
require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';

$iconoDisabled = 0;
$tipoBusca = $_SESSION["tipo_busqueda"];
$usoZona = $_SESSION["activ_logistica"];
$arrProductos = $_SESSION["productoRapido"];

if (!isset($_SESSION['cliente'])) {
	header('Location:listado-clientes.php?frm=0&cartel=1');
}
//
//  echo "<pre>";
//  echo print_r($_SESSION);
//    echo "</pre>";
if (is_object($jcart)) {
	$_SESSION["totalCarrito"] = $jcart->totalCarrito();
}
// bulto cerrado 
if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
	$usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
}

// display 
if ($_SESSION['utiliza_display'] == "Si") {
	$usaDisplay = $_SESSION['utiliza_display'];
}
// echo "<pre>";
// echo $usoBultoCerrado;
// echo "<br>";
// echo $usaDisplay;
// echo "</pre>";


?>
<!DOCTYPE HTML>
<html lang="es">

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<title> Nuevo Presupuesto | administraNET e-com </title>
	<?php require_once 'cabecera-articulo.php'; ?>



</head>

<body>

	<div id="wrapper">
		<input type="hidden" id="usoZona" name="usoZona" value="<?php echo $usoZona; ?>">
		<?php require_once $barra; ?>
		

		<div class="paneles busqueda-productos" id="paneles content">
			<div class="paneles-titulo  tituloComprobante">
				<span class="textoTituloComprobante"><i class="fas fa-file-invoice fa-fw"></i> Presupuesto</span>

				<div class="controles-titulo">
					<button type="button" class="botonAccionPrimario" onclick="mostrarBuscarProductos()">Productos</button>

					<button type="button" class="botonAccionSecundario" onclick="mostrarCarrito()">
						Carrito
						<?php if (isset($_SESSION["totalCarrito"])) : ?>
							<span class="badge">
								<i class="fas fa-circle fa-stack-2x" style="color:Tomato"></i>
								<strong id="totalCarrito" class="total-carrito"><?php echo $_SESSION["totalCarrito"]; ?></strong>
							</span>
						<?php endif; ?>
					</button>
				</div>
			</div>
			<div id="panelBuscaRapido">

				<form method="get" action="" id="formBusca">

					<!-- <div id="divBuscaArticulo" > -->

					<div class="barra-busqueda con-filtros">

						<!-- <div class="control" id="divBarraArticulo"> -->

						<!-- <label for="queArticulo">Producto </label> -->
						<!-- <input type="search" id="queArticulo" name="queArticulo" onsearch="" autocomplete="off" class="input-buscar-rapido"  placeholder="nombre o código..." />
                        
                        <button title="Buscar" alt="Buscar" type="submit" id="buscarArticuloR" name="buscarArticuloR"  class="boton-busca-rapido">
                                <i class="fab fa-sistrix"></i> Buscar
                        </button> -->

						<div class="barra">
							<input type="search" id="nombreBuscaRapido" name="nombreBuscaRapido" placeholder="Producto nombre o id ..." class="input-buscar-rapido" autocomplete="off" />
							<button title="Buscar" alt="Buscar" type="button" id="botonBuscarRapido" name="botonBuscarRapido" class="boton-busca-rapido">
								<i class="fab fa-sistrix"></i> Buscar
							</button>
						</div>

						<button type="button" class="btn btn-filtro fa-angle-down botonAccionSecundario" id="filtroAvanzado">
							Filtros <i class="fas fa-angle-down fa-lg fa-fw"></i><i class="fas fa-angle-up fa-lg fa-fw"></i>
						</button>

						<input type="hidden" name="itemId" id="itemId">
					</div>

					<div class="div-busqueda-avanzada-producto ocultar" id="divBuscaRubro">
						<!-- <h4>Filtros</h4> -->

						<div class="controlContainer-50">
							<div class="check-control div-opciones-busqueda">
								<label for="buscaPromo"></label>
								<input type="checkbox" name="buscaPromo" id="buscaPromo" value="si"> Promociones

								<label for="buscaMisConsumos"></label>
								<input type="checkbox" name="buscaMisConsumos" id="buscaMisConsumos" value="si"> Mis consumos
							</div>
						</div>

						<div class="controlContainer">
							<div class="control div-opciones-busqueda">
								<label for="buscaRubro">Categoría:</label>
								<select id="buscaCategoria" name="buscaCategoria">
									<option value="" selected>- todas -</option>
									<?php $articulos->muestra_categorias(); ?>
								</select>
							</div>

							<div class="control div-opciones-busqueda">
								<label for="buscaRubro">Rubro:</label>
								<select id="buscaRubro" name="buscaRubro">
									<option value=""> - todos -</option>

								</select>
							</div>

							<div class="control div-opciones-busqueda">
								<label for="buscaSubRubro">Sub Rubro:</label>
								<select id="buscaSubRubro" name="buscaSubRubro">
									<option value="">- todos -</option>
								</select>
							</div>

							<div class="control div-opciones-busqueda">
								<label for="buscaMarca">Marca:</label>
								<select id="buscaMarca" name="buscaMarca">
									<option value="">- todas -</option>
									<?php $articulos->muestra_marcas(); ?>
								</select>
							</div>

							<div class="control div-opciones-busqueda">
								<label for="buscaModelo">Modelo:</label>
								<select id="buscaModelo" name="buscaModelo">
									<option value="">- todos -</option>
								</select>
							</div>

							<!-- <div class="control div-opciones-busqueda">
								<label for="buscaModelo">Proveedor:</label>
								<select id="buscaModelo" name="buscaModelo">
									<option value="">- todos -</option>
								</select>
							</div> -->
						</div>

						<div class="controlButton div-opciones-busqueda accion">
							<button title="Filtrar" alt="Filtrar" type="button" id="botonBuscarFiltrar" name="botonBuscarFiltrar" class="botonAccionPrimario">
								<i class="fas fa-sliders-h"></i> Aplicar
							</button>
						</div>

					</div>

				</form>
			</div>
		</div>


		<div class="paneles-tabla pedido">
			<div id="contiene-tabla-comprobante" class="paneles lista">
				<?php if ($soyMovil == "No") : ?>
					<table class="display compact" id="myTable" data-page-length='5'>
					<?php else : ?>
						<table class="display compact" id="myTable" data-page-length='5'>
						<?php endif; ?>
						<thead>
							<tr>
								<th>Listado de Productos</th>
							</tr>
						</thead>
						<tr>
							<td class='vacio'>No se encontaron resultados </td>
						</tr>
						</tbody>
						</table>

			</div>

			<div class="sidebar presupuesto" id="sidebar">
				<div id="jcart">
					<?php

					if (isset($jcart) && (is_object($jcart))) {
						if ($soyMovil == 'Si') {
							$jcart->display_carrito_pedido_mobil();
						}
						if ($soyMovil == 'No') {
							$jcart->display_carrito_pedido_desktop();
						}
					}
					?>
				</div>
			</div>

			<div id="spinner" class="spinner" style="display:none;">
				<img src="_img/logo-administranet-ecommerce.png" />
				<div class="texto">Procesando...</div>
			</div>


			<form method="post" action="" class="jcart">

				<input type="hidden" name="my-item-qty" value="<?php echo $_REQUEST['cant']; ?>" size="3" />
				<input type="hidden" name="jcartToken" value="<?php echo $_SESSION['jcartToken']; ?>" />
				<input type="hidden" name="my-item-id" value="" />
				<input type="hidden" name="my-item-id-manual" value="" />
				<input type="hidden" name="my-item-name" value="" />
				<input type="hidden" name="my-item-price" value="" />
				<input type="hidden" name="my-item-tipoIva" value="" />
				<input type="hidden" name="my-item-alicuota" value="" />
				<input type="hidden" name="my-item-impIva" value="" />
				<input type="hidden" name="my-item-iva" value="" />
				<input type="hidden" name="my-item-neto" value="" />
				<input type="hidden" name="my-item-descPor" value="" />
				<input type="hidden" name="my-item-impInterno" value="" />
				<input type="hidden" name="my-item-url" value="" />
				<input type="hidden" name="my-item-promo" value="" />
				<input type="hidden" name="my-item-promoCant" value="" />
				<input type="hidden" name="my-item-promoPorc" value="" />
				<input type="hidden" name="my-item-promoTipo" value="" />

				<input type="hidden" name="my-item-impInternoTasa" value="" />
				<input type="hidden" name="my-item-impInterno" value="" />


				<input type="hidden" name="my-item-impInternoDescripcion" value="" />
				<input type="hidden" name="my-item-impInternoTipo" value="" />
				<input type="hidden" name="my-item-impInternoPorcentaje" value="" />
				<input type="hidden" name="my-item-impInternoMontoFijo" value="" />
				<input type="hidden" name="my-item-impInternoPesoCalculado" value="" />
				<input type="hidden" name="my-item-impInternoPagoMinimo" value="" />
				<input type="hidden" name="my-item-impInternoIdUnimed" value="" />

				<!-- cantidad unidad display bulto -->
				<input type="hidden" name="my-item-cantidad-unidad-display" value="" />
				<input type="hidden" name="my-item-cantidad-dividir" value="" />
				<input type="hidden" name="my-item-tipo-unidad-contada" value="" />
				<input type="hidden" name="my-item-cantidad-minima-contada" value="" />
				<input type="hidden" name="my-item-costo" value="" />


				<input type="hidden" name="permiso-sin-stock" id="permiso-sin-stock" value="<?php echo $_SESSION['venta_sin_stock']; ?>">
				<input type="hidden" name="usa-bulto-promedio" value="<?php echo $_SESSION["uso_bulto_promedio"];  ?>" />
				<input type="hidden" name="my-item-saldo" value="">
				<input type="hidden" name="my-item-ensamblado-vta" value="">


			</form>







			<script>
				$(document).ready(function() {
					// filtro avanzado.
					var divAvanzado = $("#divBuscaRubro");
					//divAvanzado.fadeOut(); 
					$('#filtroAvanzado').on('click', function() {
						// console.log('hago click en la busqueda avanzada---------');
						// var divAvanzado = $("#divBuscaRubro");
						// mostrar
						if ($(this).hasClass('fa-angle-down') === true) {
							// console.log('tenglo clase angle down----');
							// vamos a hacer un toggle class
							divAvanzado.removeClass('ocultar').addClass('mostrar');
							$(this).removeClass('fa-angle-down').addClass('fa-angle-up');
						} else {
							//ocultar panel
							// console.log('tenglo clase angle UP----');
							$(this).removeClass('fa-angle-up').addClass('fa-angle-down');
							// divAvanzado.hide();
							divAvanzado.removeClass('mostrar').addClass('ocultar');

						}

					});

					$('#queArticulo').focus(function() {
						//$(this).val();
						//alert('a ver si hace focus');
						$(this).val('');
					});

					// * armando el cartel sweet alert
					var paramGets = listaGetUrl();
					let cartel = paramGets.searchParams.get("cartel");
					let comprobante = paramGets.searchParams.get("pre");
					let remito = paramGets.searchParams.get("rem");
					let estado = paramGets.searchParams.get("est");
					let tipoComprobante = 'presupuesto';
					let tipoUsuario = '<?php echo $_SESSION["tipousuario"]; ?>';
					console.log('todos los parametros', paramGets);
					// console.log('remito',remito);
					console.log('pedido', comprobante);
					console.log('cartel', cartel);
					console.log('tipoUsuario', tipoUsuario);

					console.log('estado', estado);
					if (cartel !== null) {
						let htmlCartel = '';
						let tituloCartel = '';
						// todo bien
						if (cartel == 0) {
							tituloCartel += 'Excelente';
							htmlCartel += '<span class="alerta-exito">Se ha generado el ' + tipoComprobante + '<br>';
							htmlCartel += ' <strong>' + comprobante + '</strong> (<strong>' + estado + '</strong>)</span>';
							icono = 'success';

						}
						// todo mal
						if (cartel == 1) {

							tituloCartel += 'Atención!';
							htmlCartel += '<span class="texto-alerta"> No se hizo el Comprobante, intente nuevamente.</span>';
							icono = 'error';
						}

						// * soy cliente
						if (tipoUsuario == 'vendedor') {
							Swal.fire({
								title: tituloCartel,
								html: htmlCartel,
								icon: icono,
								showConfirmButton: true,
								confirmButtonText: 'Cambiar Cliente',
								confirmButtonColor: '#2A3E72',
								showCancelButton: true,
								cancelButtonText: "No",
							}).then((accion) => {
								// console.log('estoy confirmado accion',accion);
								if (accion.isConfirmed) {
									location.href = 'listado-clientes.php?accion=cambiar';
								}


							});


							// listado-clientes.php
						}
						if (tipoUsuario == 'cliente') {
							Swal.fire({
								title: tituloCartel,
								html: htmlCartel,
								icon: icono,
								showConfirmButton: true,
								confirmButtonColor: '#2A3E72',
							});
						}


					}

					// variables globales de inicio del captura de texto.    


				});
			</script>
		</div>
		<?php require_once 'footer.php'; ?>
	</div>
	<div id="basic-modal-content"></div>
</body>

</html>