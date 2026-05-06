<?php 
$iconoSuper="fa-regular fa-circle-user fa-fw ";
$tituloPuesto ="Vendedor";
if($objVendedor->id_puesto==1){
    //supervisor
    $iconoSuper="fa-solid fa-circle-user fa-fw ";
    $tituloPuesto ="Supervisor de Ventas";
}else{
    // veo el permiso
    if($objVendedor->permiso_supervisor_venta=='Si'){
        $iconoSuper="fa-solid fa-circle-user fa-fw ";
        $tituloPuesto ="Supervisor de Ventas";
    }
}
$arrListaPrecio = $_SESSION["arr_lista_precio"];
$modInventario =0;
$modPremios = 0;
if(isset($_SESSION['modulo_inventario'])){
    $modInventario = $_SESSION['modulo_inventario'];
}
if(isset($_SESSION['mod_premios'])){
    $modPremios = $_SESSION['modPremios'];
}
?>
<div id="header" class="noPrint">
    <ul id="nav">
        <li >
                    <a href="escritorio.php" title="Escritorio">
                        <img src="foto.php?origen=logo|0" alt="<?php echo $_SESSION['nombre_empresa'];?>" title="<?php echo $_SESSION['nombre_empresa'];?>" class="asBlock" />
                    </a>
                </li>
        
        <li>
            <a href="listado-clientes.php" title="Nuevo cliente" >
                <i class="fa-solid fa-image-portrait fa-fw"></i>
                <span>Clientes</span>

            </a>
    </li>
        
        
        <?php if($modInventario!=0):?>
        <li>
            <a title="Inventario" href="inventario/index.php" > 
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Inventario</span>

            </a>
        </li>   
        <?php endif;?>
        <?php if($modPremios!=0):?>
        <li>
            <a title="Premios" href="menu_lista_premios.php" > 
            <i class="fas fa-gift fa-lg fa-fw"></i>
                <span>Premios</span>

            </a>
        </li>   
        <?php endif;?>
        <li>
            <a title="Listado de comprobantes" href="menu-lista-listados.php">  
                <i class="fa-solid fa-rectangle-list"></i>
                <span>Informes</span>

            </a>
        </li>
        
          <li>
              <a href="menu-lista-informes.php" title="Estadísticas">
                <i class="fa-solid fa-chart-line  fa-fw "></i>     
                <span>Est.</span>
                           
              </a>
              
          </li>
          
         <li style="float:right">
            <a href="javascript:void(0);" title="Salir" onclick="salida();">
                <i class="fa fa-sign-out-alt fa-fw "></i>
                <span>Salir</span>

            </a>
        </li>
        <li style="float:right">
       

            <a href="javascript:void(0);" id="iconoVendedor">
                <i class="<?php echo $iconoSuper;?> "></i>
                <span>Cuenta</span>
            </a>
            
        </li>
    </ul>

    <div id="datosVendedor" class="paneles">
    <div class="cabecera-panel">
        <span ><i class="<?php echo $iconoSuper;?>"></i> Mi cuenta</span>
                
       

        <span class="cerrar-menu-vendedor"><i class="fa-regular fa-rectangle-xmark fa-lg"></i></span>
    </div>

<div class="lista-datos-vendedor">
    <ul class="opciones-vendedor">
        <li>
             Nombre: <strong><?php echo $objVendedor->nombre_usuario . ' ' . $objVendedor->apellido_usuario; ?></strong>
                
        </li>
        <li>Usuario: <strong><?php echo $_SESSION['usuario']; ?></strong> </li>
        <li>Puesto : <strong><?php echo $tituloPuesto;?></strong></li>
        <li>Empresa: <strong><?php echo $_SESSION['nombre_empresa']; ?></strong>     </li>
        <li>Deposito: <strong> <?php echo $objVendedor->deposito; ?></strong>        </li>
        <li>Venta sin stock: <strong <?php if($_SESSION["venta_sin_stock"]=='No'){ echo 'class="exceso"';}?>><?php echo $_SESSION["venta_sin_stock"]; ?></strong>       </li>
        <li>Lim Desc Pie: <strong class="verde"><?php echo $objVendedor->lim_desc_pie; ?>%</strong>        </li>
        <li>Lim Desc Reng: <strong class="verde"><?php echo $objVendedor->lim_desc_renglon; ?>%</strong>        </li>
    </ul>
</div>
</div>
    <?php if (isset($_SESSION['cliente'])) : ?>

<div class="paneles" id="tarjeta-cliente">
    <div id="botoneraCliente">

        <div class="accionCliente" id="editarClienteH" rel="<?php echo $objCliente->Codigo; ?>">
            <span class="accion-icono">
                <i class='fa-solid fa-square-pen   fa-fw'></i>
            </span>
            <span class="accion-texto">Editar</span>
        </div>
        <div class="accionCliente" id="domicilioClienteH" rel="<?php echo $objCliente->Codigo; ?>">
            <span class="accion-icono">
                <i class='fa fa-address-card   fa-fw'></i>
            </span>
            <span class="accion-texto">Domicilios</span>
        </div>
        <div class="accionCliente" id="icono-ver-fact">
            <span class="accion-icono">
                <i class="fa fa-chart-line    fa-fw"></i>
            </span>
            <span class="accion-texto">Comprobantes no cancelados</span>
        </div>
        <!-- <div class="accionCliente" id="icono-ver-consumos">
            <span class="accion-icono">
                <i class="fa fa-shopping-basket  fa-fw"></i>
            </span>
            <span class="accion-texto">Consumos</span>
        </div> -->
    </div>
    <div class="nombrecliente">
        <span><i class="fa-solid fa-image-portrait fa-fw"></i> 
            <?php //echo ucwords(strtolower($objCliente->cliente)); ?>
            <?php echo ucwords(mb_strtolower($objCliente->cliente, 'UTF-8'));?>
            <span class="ver-opciones-cliente"><i class="fa-solid fa-angle-down"></i></span>
            <span class="cambiar-cliente"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
        </span>

        <span class="ver-menu-cliente"><i class="fa-solid fa-ellipsis-vertical"></i></span>


    </div>
    <div class="status-cliente">
        <div class="item-status-cliente">
            <span class="leyenda-status-cliente">Saldo: </span>
            <span class="dato-status-cliente">$<?php echo number_format($objCliente->saldo, 2, ',', '.'); ?></span>

        </div>

        <?php if (property_exists($objCliente, 'usaPremio') && ($objCliente->usaPremio === "Si")) : ?>
            <div class="item-status-cliente">
                <span class="leyenda-status-cliente">Puntos: </span>
                <span class="dato-status-cliente"><?php echo number_format($objCliente->puntos, 0, ',', '.'); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($arrCliente['exceso']) && $arrCliente["exceso"] == 1) : ?>
            <div class="item-status-cliente">
                <span class="leyenda-status-cliente exceso"><i class="fa  fa-exclamation-triangle "></i> Exceso </span>
                <?php echo '<span class="dato-status-cliente exceso">' . $arrCliente['dias_exceso_limite'] . ' d</span>'; ?>

            </div>
        <?php endif; ?>

    </div>




    <div class="datos-cliente">
        <!-- <i class="fa-regular fa-rectangle-xmark"></i> -->
        <ul>
            <li> Id: <strong><?php echo $objCliente->Codigo; ?></strong></li>
            <li> Saldo: <strong>$<?php echo number_format($objCliente->saldo, 2, ',', '.'); ?></strong></li>
            <?php if (property_exists($objCliente, 'usaPremio') && ($objCliente->usaPremio === "Si")) : ?>
                <li>Puntos: <strong><?php echo number_format($objCliente->puntos, 0, ',', '.'); ?></strong></li>

            <?php endif; ?>

            <li> Cond.Vta: <strong><?php echo $objCliente->condVenta; ?></strong></li>
            <?php foreach($arrListaPrecio as $lista):?>
                <?php if($lista['texto']==$objCliente->listaPrecio):?>
                    <li> Lista de Precio: <strong><?php echo $lista['texto'].' - '.$lista['nombre']; ?></strong></li>

                <?php endif;?>
            <?php endforeach;?>    
            <li> Credito: <strong>$<?php echo number_format($objCliente->Credito, 2, ",", "."); ?></strong></li>
            <?php if ($objCliente->descPie > 0) : ?>
                <li> Desc. Pie: <strong class="verde"><?php echo number_format($objCliente->descPie, 0); ?>% </strong></li>
            <?php endif; ?>
            <?php if ($objCliente->descRenglon > 0) : ?>
                <li> Desc. Reng: <strong class="verde"><?php echo number_format($objCliente->descRenglon, 0); ?>%</strong></li>
            <?php endif; ?>
            <?php if (isset($arrCliente['exceso']) && $arrCliente["exceso"] == 1) : ?>

                <li>Exceso de vto: <strong class="exceso"><?php echo '' . $arrCliente['dias_exceso_limite'] . ' días'; ?></strong></li>


            <?php endif; ?>

        </ul>
    </div>




</div>



<!-- <div id="modal-ncancelados-cliente">
    <div class="tituloVentana">Comprobantes no cancelados<button id="cierroNcanc" class="botonNuevo black grande">X</button></div>
    <table id="tablaCancelados" name="tablaCancelados" cellspacing="1" style="width: 99%">
    </table>
</div> -->
<div id="modal-consumos-cliente">
    <div class="tituloVentana">TOP 20 Productos consumidos <button id="cierroConsumos" class="botonNuevo black grande">X</button></div>
    <table id="tablaConsumos" name="tablaConsumos" cellspacing="1" style="width:99%">
    </table>
</div>



<?php endif; ?>
    
</div>