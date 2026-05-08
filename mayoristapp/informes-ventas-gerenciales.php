<?php require_once 'sesion.inc.php'; ?>
<?php
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;

$usaZoom = 0;
$usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
?>
<!DOCTYPE HTML>
<html lang="es-AR">

<head>

    <title>ventas gerenciales | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <?php require_once 'cabecera.php'; ?>
    <script src="_scripts/acciones-informes-gerenciales.js"></script>

</head>

<body>
    <div id="wrapper">
        <?php
        require_once $barra;
        ?>

        <div class="paneles filtroInformes">
            <h1>Parámetros <span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
            <!-- <h4 id="tituloInforme">Ventas netas por cliente</h4> -->
            <form id="formBusca" name="formBusca" method="POST" action="">

                <div class='panelesBloqueInforme'>
                    <div class="control">
                        <label for="agrupoPor" class="parametros">Ventas por:

                        </label>
                        <select name="agrupoPor" id="agrupoPor" required="required">
                            <option value=""> - seleccionar -</option>
                            <option value="cliente" selected="selected">Cliente</option>
                            <option value="tipocliente">Tipo Cliente</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="articulo">Artículo</option>
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

                    <div id="tipoComparacion" class="control">
                        <label for="tipoOperacion" class="parametros">Operación:</label>

                        <select id="tipoOperacion" name="tipoOperacion">
                            <option selected="selected" value="suma">Suma por periodo</option>
                            <option value="sumag">Suma totalizada</option>
                            <option value="resta">Diferencia totalizada</option>
                        </select>

                    </div>

                    <div class="control">
                        <label for="verInforme" class="parametros">Valores en: </label>
                        <select name="verInforme" id="verInforme" required="required">
                            <option value=""> - seleccionar -</option>
                            <option value="un">Unidades (Un)</option>
                            <option value="peso">Peso (Kg)</option>
                            <option value="monto" selected="selected">Monto ($)</option>

                        </select>

                    </div>

                    <div class="control">
                        <label for="verInformeDisplayBulto" class="parametros">Pesentación: </label>

                        <select name="verInformeDisplayBulto" id="verInformeDisplayBulto" required="required">
                            <option value=""> - seleccionar -</option>
                            <option selected value="Unidad">Unidad</option>
                            <?php
                            //bulto cerrado 
                            $usoBultoCerrado = 'No';
                            $usaDisplay = 'No';
                            if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
                                $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
                            }

                            // display 
                            if ($_SESSION['utiliza_display'] == "Si") {
                                $usaDisplay = $_SESSION['utiliza_display'];
                            }

                            if ($usoBultoCerrado == 'Si' && $usaDisplay == 'Si') {
                                echo '<option value="Display">Display</option>';
                                echo '<option value="Bulto">Bulto cerrado</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="control">
                        <label for="campoPeriodo" class="parametros">Período: </label>
                        <select name="campoPeriodo" id="campoPeriodo" required="required">
                            <option value="dia">Diario</option>
                            <option value="semana">Semanal</option>
                            <option value="mes" selected="selected">Mensual</option>

                        </select>

                    </div>
                    <div class="control">
                        <label for="decimales" class="parametros">Decimales: </label>
                        <select name="decimales" id="decimales" required="required">

                            <option value="0">No</option>
                            <option value="1">1</option>
                            <option value="2" selected="selected">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>


                        </select>

                    </div>
                    <div class="control">
                        <label for="artEnsamblados" class="parametros">Ensamb.Vta: </label>
                        <select name="artEnsamblados" id="artEnsamblados" required="required">

                            <option value="detalle">Detalle</option>
                            <option value="simple" selected="selected">Simple</option>



                        </select>

                    </div>
                </div>
                <div class='panelesBloqueInforme panel-fechas'>
                    <div class="titulo">Fechas </div>

                    <div class="control w100p">
                        <label class="parametros">Primario:</label>
                        <input type="date" name="fechaDesde" id="fechaDesde" required="required" value="">
						<label for="fechaDesde" class="parametros">al</label>
                        <input type="date" name="fechaHasta" id="fechaHasta" required="required" value="">
                    </div>

                    <div class="control w100p">
                        <label class="parametros">Secundario:</label>
                        <input type="date" name="fechaDesdeDos" id="fechaDesdeDos">
						<label for="fechaDesdeDos" class="parametros">al</label>
                        <input type="date" name="fechaHastaDos" id="fechaHastaDos">
                    </div>

                </div>





                <div class="panelesBloqueInforme panel-filtro-simple">
                    <div class="titulo">Punto de Venta</div>

                    <div class="control">
                        <label class="parametros">Punto:</label>

                        <select name="puntoVenta" id="puntoVenta">
                            <option value="|Todos" selected="selected"> - todos - </option>
                            <?php echo $_SESSION["lista_pv_opc"]; ?>
                        </select>

						<button name="addPv" id="addPv" type="button" class="botonNuevo">
							<i class="fas fa-plus fa-lg fa-fw"></i>
						</button>

                    </div>

                    <div class="control">
						<label class="parametros">Aplicados: </label>
                        <ul name="listaPv" id="listaPv" class="listaSeleccionado">
                            <li id="1"><i class="fas fa-check-square fa-lg fa-fw"></i>Punto venta: Todos <a class="borrarLi" rel="listaPv|1" href="#" title="Eliminar de la lista"><i class="fa fa-trash fa-lg"></i></a></li>
                        </ul>

                        <input type="hidden" name="pvSelec" id="pvSelec" value="|Todos|1||" required="required">
                    </div>

                </div>

                <div class="panelesBloqueInforme panel-filtro-compuesto">
                    <div class="titulo"> Filtros </div>

                    <div class="control">
                        <label for="filtrarPor" class="parametros">Tipo:</label>
                        <select name="filtrarPor" id="filtrarPor">
                            <option value=""> - seleccionar -</option>
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
                        <!-- <label for="seleccionFiltro" class="parametros">Seleccionar: </label> -->
                        <input id="seleccionFiltro" alt="" autocomplete="off" type="search" placeholder="nombre o codigo..." disabled="disabled">
                        <button name="addFiltro" id="addFiltro" class="botonNuevo" type="button"> <i class="fas fa-plus fa-lg fa-fw"></i> </button>
                    </div>

                    <div class="control w100p">
                        <label class="parametros">Aplicados: </label>
                        <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
                        <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">
                    </div>
                </div>

                <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                            <i class="fas fa-check fa-lg fa-fw"></i> Generar
                        </button>
                    </span>
                </div>

            </form>
        </div>


        <div class="paneles" id="contiene-tabla" style="min-width:fit-content">
        <!-- <div class="paneles" id="contiene-tabla" style="overflow:auto"> -->

            <h4>Estadísticas de ventas netas (sin impuestos) <i id="expandir" class="fa fa-expand fa-lg fa-fw" title="expandir"></i> </h4>
            
            <table class="display" id="myTableVentasRubro">
                <thead></thead>
                <tbody></tbody>
                <tfoot></tfoot>                
            </table>




        </div>


        <?php require_once 'footer.php'; ?>

    </div>
    <div id="basic-modal-content"> </div>
    
</body>


</html>