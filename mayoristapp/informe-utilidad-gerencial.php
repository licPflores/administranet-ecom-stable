<?php require_once 'sesion.inc.php'; ?>
<?php
/**
 * variables de configuracion para colocar los encabezados
 * UTILIDAD
 */
$uTablas        = 1;
$uModal         = 1;
$uSlider        = 0;
$uGui           = 1;
$iconoDisabled  = 1;
$usaZoom        = 0;
?>
<!DOCTYPE HTML>
<html>

<head>

	<title>Rentabilidad gerencial | administraNET</title>
	<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<?php require_once 'cabecera.php'; ?>
	<script src="_scripts/acciones-informes-gerenciales.js"></script>


	<script>
		function controlarFechas(fechaDesde, fechaHasta) {

			var fecha1 = new Date(fechaDesde);
			var fecha2 = new Date(fechaHasta);

			if (esPrimeraMenorQueSegunda(fecha1, fecha2) && tieneUnMesDeDiferencia(fecha1, fecha2)) {
				return true;
			} else {
				return false;
			}

			function tieneUnMesDeDiferencia(fecha1, fecha2) {

				var diferenciaMeses = (fecha2.getFullYear() - fecha1.getFullYear()) * 12 + fecha2.getMonth() - fecha1.getMonth();

				return diferenciaMeses == 1;
			}

			function esPrimeraMenorQueSegunda(fecha1, fecha2) {
				return fecha1.getTime() < fecha2.getTime();
			}

		}
	</script>



</head>

<body>
	<div id="wrapper">
		<?php
		require_once $barra;
		?>
		<div class="paneles filtroInformes">

			<h1>Parámetros <span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>

			<form id="formBusca" name="formBusca" method="POST" action="">
				<div class='panelesBloqueInforme'>
					<div class="control">
						<label for="verInforme">Ver:</label>
						<select name="verInforme" id="verInforme" required="required">
							<option value="ut"> Utilidad</option>
						</select>
						
					</div>
					<div class="control">
						<label for="agrupoPor" class="parametros">Listar: </label>
						<select name="agrupoPor" id="agrupoPor" required="required">
							<option value=""> - seleccionar -</option>
							<option value="cliente">Cliente</option>
							<option value="tipocliente">Tipo Cliente</option>
							<option value="vendedor">Vendedor</option>
							<option value="articulo">Articulo</option>
							<option value="proveedor">Proveedor</option>
							<option value="zona">Zona</option>
							<option value="categoria">Categoría</option>
							<option value="rubro">Rubro</option>
							<option value="subrubro">Sub Rubro</option>
							<option value="marca">Marca</option>
						</select>
					</div>

					<div class="control">
						<label for="campoPeriodo" class="parametros">Periodo: </label>
						<select name="campoPeriodo" id="campoPeriodo" required="required">
							<!--<option value="dia">Diario</option>
                            <option value="semana">Semanal</option>-->
							<option value="mes" selected="selected">Mensual</option>

						</select>

					</div>
				</div>
				<div class='panelesBloqueInforme panel-fechas'>
					<div class="titulo">Fechas</div>

					<div class="tituloFecha">

						<div id="buscaFecha" class="control w100p">
							<label for="fechaDesde" class="parametros">
								<i class="fa fa-calendar fa-lg fa-fw"></i>
							</label>
							<input type="date" name="fechaDesde" id="fechaDesde" required="required"> al
							<label for="fechaHasta" class="parametros">
								<i class="fa fa-calendar fa-lg fa-fw"></i>
							</label>
							<input type="date" name="fechaHasta" id="fechaHasta" required="required">
						</div>

					</div>

					<!--                        <div class="tituloFecha">
                                                                        <div id="buscaFecha"  class="control">
                                                                            <label>Rango secundario</label><br>
                                                                            <label for="fechaDesdeDos">Desde: <input type="date" name="fechaDesdeDos" id="fechaDesdeDos"  ></label>
                                                                            <label for="fechaHastaDos">Hasta: <input type="date" name="fechaHastaDos" id="fechaHastaDos" ></label>
                                                                        </div>
                                                                        <div id="tipoComparacion" class="control">
                                                                            <label>Operación de Rango:
                                                                            <select id="tipoOperacion" name="tipoOperacion">
                                                                                <option value="suma">Suma</option>
                                                                                <option value="resta">Diferencia</option>
                                                                            </select>
                                                                                </label>
                                                                        </div>    
                                                                    </div>-->
				</div>




				<div class='panelesBloqueInforme'>
					<div class="titulo">
						Punto de Venta
					</div>
					<div class="panel-filtro-simple">
						<div class="control">
							<label class="parametros">Punto:</label>
							<select name="puntoVenta" id="puntoVenta">
								<option value="|Todos" selected="selected"> - todos - </option>
								<?php echo $_SESSION["lista_pv_opc"]; ?>
							</select>

							<button name="addPv" id="addPv" type="button" class="botonNuevo"><i class="fas fa-plus fa-lg fa-fw"></i></button>
						</div>
					</div>

					<div class="subtitulo">Filtros aplicados:</div>

					<div class="panelesBloqueInforme-interno contiene-lista-filtros en-bloque">

						<ul name="listaPv" id="listaPv" class="listaSeleccionado">
							<li id="1" data-valor="Todos"><i class="fas fa-check-square fa-lg fa-fw"></i><span class="tipo">Punto venta: <strong>Todos</strong></span><a class="borrarLi" rel="listaPv|1" href="#" title="Eliminar de la lista"><i class="fa fa-trash fa-lg"></i></a></li>
						</ul>

						<input type="hidden" name="pvSelec" id="pvSelec" value="|Todos|1||" required="required">

					</div>
				</div>

				<div class="panelesBloqueInforme">

					<div class="titulo"> Filtros </div>

					<div class="panel-filtro-compuesto">

						<div class="control">
							<label for="filtrarPor" class="parametros">Tipo:</label>
							<select name="filtrarPor" id="filtrarPor">
								<option value="Todos"> - seleccionar -</option>
								<option value="cliente">Cliente</option>
								<option value="tipocliente">Tipo Cliente</option>
								<option value="vendedor">Vendedor</option>
								<option value="articulo">Articulo</option>
								<option value="proveedor">Proveedor</option>
								<option value="zona">Zona</option>
								<option value="categoria">Categorias</option>
								<option value="rubro">Rubro</option>
								<option value="subrubro">Sub Rubro</option>
								<option value="marca">Marca</option>
								<option value="usuario">Usuario</option>
								<option value="sucursal">Sucursal</option>
							</select>
						</div>

						<div class="control-con-boton">
							<input id="seleccionFiltro" alt="" autocomplete="off" type="search" placeholder="nombre o codigo...">
							<button name="addFiltro" id="addFiltro" class="botonNuevo" type="button"> <i class="fas fa-plus fa-lg fa-fw"></i> </button>
						</div>

					</div>

					<div class="subtitulo">Filtros aplicados:</div>

                    <div class="panelesBloqueInforme-interno contiene-lista-filtros en-bloque">
						<ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
						<input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">
					</div>
				</div>
				<!--                    <div class="separador10px"></div>
                                                    <div class="control">
                                                        
                                                        <input type="checkbox" name="aceptaGrafico" id="aceptaGrafico" value="si">
                                                        <label for="aceptaGrafico">  Ver gráficos <i class="fa fa-bar-chart fa-1x" ></i> </label>
                                                    </div>-->


				<div class="panelesBloqueInformeAccion">
					<span class="centro w100p">
						<button title="Buscar" alt="Buscar" type="button" id="botonBuscarUtilidad" name="botonBuscarUtilidad" class="botonNuevo">
							<i class="fas fa-check fa-lg fa-fw"></i> Generar
						</button>
					</span>
				</div>




			</form>
		</div>




		<div class="paneles" id="contiene-tabla" style="min-width:fit-content">
			<h1>Estadísticas de Rentabilidad <i id="expandir" class="fa fa-expand fa-lg fa-fw" title="expandir"></i> </h1>

			<!-- <h2 class="alignLeft">Utilidades</h2> -->
			<table class="display" id="myTableVentasRubro" style="width:99%">
				<thead></thead>
				<tbody></tbody>
				<tfoot></tfoot>
			</table>

			<!-- <h3 class="alignLeft">Gráfico</h3>
                 <div id="graficoVentasRubro"></div>
                <div id="graficoVentasRubroT"></div> -->

		</div>





		<?php require_once 'footer.php'; ?>

	</div>
	<div id="basic-modal-content"> </div>
	<!--spinner admNET-->
	<div id="spinner" class="spinnerAdm" style="display:none;">
		<div class="centro">
			<img src="_img/logo-administranet-ecommerce.png">
			<div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
		</div>
	</div>
	<!--fin spinner-->
</body>

</html>