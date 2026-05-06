<?php 
    require_once '_scripts/php/funciones.php';
?>
<?php if($caminoDispo!=""):?>
<?php 
    /*
     * MOVIL
     */
    require_once 'header-cliente-mobil.inc.php';
?>
<?php else:?>
    <?php 
        /*
         *  DESKTOP
         */
    ?>
<div id="header" class="noPrint">
    <?php
    if(is_object($_SESSION['cliente'])){
        $clienteObj = $_SESSION['cliente'];
    }else{
            $clienteObj = $_SESSION['cliente'][0];
    }
        if($_SESSION["tipousuario"]=="vendedor"){
            $usuario = $_SESSION["vendedor"];
        }else{
            $usuario = $clienteObj;
        }

    ?>
    <div id="header" class="noPrint">
        <div id="headerLogo">
            <a href="escritorio.php">
            <img id="" src="foto.php?origen=logo|0" alt="<?php echo $_SESSION['nombre_empresa'];?>" title="<?php echo $_SESSION['nombre_empresa'];?>" class="asBlock" base64="<?php echo traeLogo64($connV); ?>"/>
            </a>
        </div>
    <div id="statusBar">
        <span class="vendedor">                
                <!--<i class="fa fa-user fa-fw fa-lg"></i> <?php //echo $_SESSION['apenom'];?>-->
                <!--<strong><i class="fa fa-gear fa-fw fa-lg" id="iconoVendedor" title="Opciones del vendedor"></i></strong>-->
                 
               <?php 
//               echo "<pre>";
//                        print_r($objCliente);
//                        
//                     echo "</pre>";   
               ?>
                <?php //if(is_object($jcart)):?>
                <!--<strong><a href="alta_pedido.php" title="Mi Pedido" ><i class="fa fa-shopping-cart fa-lg"></i> (<?php //echo $jcart->totalCarrito();?>)</a></strong>-->
                <?php //else:?>
                <!--<strong><a href="alta_pedido.php" title="Mi Pedido"> <i class="fa fa-shopping-cart fa-lg"></i> (0)</a></strong>-->
                <?php //endif;?>
                <strong><a href="alta_pedido.php" title="Mi Pedido"><i class="fa fa-shopping-cart fa-lg"></i> (<span id="totalCarrito"><?php echo $_SESSION["totalCarrito"]; ?></span>)</a></strong>
                <br>
                <?php //if($_SESSION["ivaIncluido"]=='si'):?>
                <!--<strong class="cartelAdvertencia" style="font-size: 12px;"> <i class="fa fa-certificate fa-lg fa-1x"></i> Los precios publicados incluyen IVA </strong>-->
                <?php //else:?>
               
                    <!--<strong class="cartelAdvertencia" style="font-size: 12px;"> <i class="fa fa-certificate fa-lg fa-1x"></i> Los precios publicados NO incluyen IVA </strong>-->
               <?php //endif;?>
            </span>       

        
 
    </div>
    <?php if(isset($_SESSION['cliente'])):?>
    
            <div class="headerCliente">
                <div class="izquierda nombrecliente"><?php echo $objCliente->cliente; ?></div>
                <!--<div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Codigo: <strong><?php echo $objCliente->Codigo; ?></strong></div>-->
                <!--<div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Lista precio: <strong><?php //echo $objCliente->listaPrecio;?></strong></div>-->
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Cond.Vta: <strong><?php echo $objCliente->condVenta; ?></strong></div>       
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Saldo: <strong>$<?php echo number_format($objCliente->saldo, 2,",","."); ?></strong></div>
                <br>
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Credito: <strong>$<?php echo number_format($objCliente->Credito,2,",",".");  ?></strong></div>
                <?php if($objCliente->descPie>0):?>
                <div class="izquierda verde"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Desc. Pie: <strong><?php echo $objCliente->descPie;?>% </strong></div>
                <?php endif;?>
                <?php if($objCliente->descRenglon>0):?>
                <div class="izquierda verde"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Desc. Reng: <strong><?php echo number_format($objCliente->descRenglon,0,",",".");?>%</strong></div>
                <?php endif;?>
                
                <?php //if(isset($arrCliente['exceso'])&&$arrCliente['exceso']==1):?>
                <!--<div class="izquierda exceso">-->
                    <?php //echo '<i class="fa  fa-warning fa-lg"></i> Exceso vto en <strong>'. $arrCliente['dias_exceso_limite'] . '</strong> días ';?>
                <!--</div>-->
                <?php //endif;?>
                
            </div>
    <?php endif;?>
    <ul id="nav">
	<li ><a href="escritorio.php"><i class="fa fa-home fa-2x fa-lg"></i> Inicio</a></li>
	<li><a><i class="fa fa-tasks fa-fw fa-lg fa-2x" ></i> Mis Opciones</a>
            <ul>
                <li><a href="lista_precio.php">Mi Lista de Precios</a></li>
                <li><a href="lista-mis-consumos.php">Mis consumos</a></li>   
                <li><a href="lista-promociones.php">Promociones</a></li>
                <li><a href="lista-pedidos-total.php">Mis Pedidos</a></li>
                <li><a href="lista_facturas_electronicas.php">Mis Facturas</a></li>
                 <?php if($_SESSION["usaRemito"]=="Si"):?>  
                <li><a href="lista-remitos.php">Mis Remitos</a></li>
                 <?php endif;?>  
                <li><a href="lista-cuenta-corriente.php">Mi Cuenta Corriente</a></li>
                <li><a href="lista-comprobantes-ncancelados.php"> Mis Comprobantes No Cancelados</a></li>                
            </ul>
	</li>
    <li ><a href="lista-precio-catalogo.php" title="Catalogo de productos"><i class="fa fa-th fa-fw fa-lg fa-2x"></i> Catálogo</a></li>
        <li><a href="alta_pedido.php"><i class="fa fa-shopping-cart fa-lg"></i> Mi Pedido</a></li>
        
<!--        <li><a href="lista-remitos.php">Remitos</a>
        <li><a href="lista-cuenta-corriente.php">Cta Cte</a></li>
        
        <li><a href="lista-comprobantes-ncancelados.php">Fact.a Pagar</a></li>-->
        
        
        <li><a href="salida.inc.php?cliente=si"><i class="fa fa-sign-out-alt fa-2x fa-lg"></i> Salir</a></li>
    </ul>
</div>
<?php endif;?>