  <div id="header" class="noPrint">
      <ul id="nav">
                <li >
                    <a href="escritorio.php" title="Escritorio">
                        <img src="foto.php?origen=logo|0" alt="<?php echo $_SESSION['nombre_empresa'];?>" title="<?php echo $_SESSION['nombre_empresa'];?>" class="asBlock" base64="<?php echo traeLogo64($connV); ?>"/>
                    </a>
                </li>
<!--                <li><a href="menu-lista-listados.php" title="listados generales"> Listados </a></li>-->
                 <li><a title="Listado de comprobantes" href="menu-lista-listados.php">  <i class="fa fa-tasks fa-fw fa-lg fa-2x" ></i> </a></i>
<!--                <li ><a href="lista-precio-catalogo.php" title="Catálogo de productos"> Catálogo </i></a></li>-->
                <li ><a href="lista-precio-catalogo.php" title="Catálogo de productos"> <i class="fa fa-th fa-fw fa-lg fa-2x"></i> </a></li>
                <!--<li><a href="alta_pedido.php">Pedido</a></li>-->
                <?php if($iconoDisabled==0 && $caminoDispo!=""):?>
<!--                <li>
                    <a href="#" class="accionPanel iconoActivo" id="iconoListaProductos" title="Activar/Desactivar Buscador y listado de productos">                        
                        <i class="fa fa-bars fa-fw fa-lg fa-2x" ></i>
                    </a>
                </li>-->
                <li>
                    <a href="#" class="accionPanel iconoActivo" id="iconoCarritoCompra" title="Activar/Desactivar Carrito de Compra">                       
                        <i class="fa fa-shopping-cart fa-fw fa-lg fa-2x"></i> (<span id="totalCarrito"><?php echo $_SESSION["totalCarrito"];?></span>)                        
                    </a>
                </li>
                <?php else:?>
                <li>
                    <a href="alta_pedido.php" title="Mi Pedido">
                        <i class="fa fa-shopping-cart fa-fw fa-lg fa-2x"></i> 
                    </a>
                </li>
                <?php endif;?>
                
                
                <li>
                    <a href="#" id="iconoCliente" title="Mis Datos">
                        <i class="far fa-user-circle fa-fw fa-lg fa-2x"></i>
                    </a>
                </li>    
                
          <li ><a href="salida.inc.php?cliente=si" title="Salir del sistema"><i class="fa fa-sign-out-alt fa-2x fa-lg"></i></a></li>
      </ul>
      
     
<!--        <div id="headerLogo">
            <img src="foto.php?origen=logo|0" alt="<?php echo $_SESSION['nombre_empresa'];?>" title="<?php echo $_SESSION['nombre_empresa'];?>" class="asBlock" base64="<?php echo traeLogo64($connV); ?>"/>
        </div>-->

    <?php if(isset($_SESSION['cliente'])):?>
        
        <div class="headerCliente">    
            <div class="nombrecliente"> 
                <i class="far fa-user-circle fa-lg fa-2x"></i> ¡Hola, <?php echo ucwords(strtolower($objCliente->cliente));?>!
            </div>
            <div class="datosCliente">
                <ul >
                <li><i class="far  fa-check-circle fa-fw fa-lg"></i> Codigo: <strong><?php echo $objCliente->Codigo; ?></strong></li>
                <!--<div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Lista precio: <strong><?php// echo $objCliente->listaPrecio;?></strong></div>-->
                <li><i class="far  fa-check-circle fa-fw fa-lg"></i> Cond.Vta: <strong><?php echo $objCliente->condVenta; ?></strong></li>      
                <li><i class="far  fa-check-circle fa-fw fa-lg"></i> Credito: <strong>$<?php echo number_format($objCliente->Credito,2,",","."); ?></strong></li>
                <?php if($objCliente->descPie>0):?>
                <li class="verde"><i class="far  fa-check-circle fa-fw fa-lg"></i> Desc. Pie: <strong><?php echo number_format($objCliente->descPie,0);?>% </strong></li>
                <?php endif;?>
                <?php if($objCliente->descRenglon>0):?>
                <li class="verde"><i class="far  fa-check-circle fa-fw fa-lg"></i> Desc. Reng: <strong><?php echo number_format($objCliente->descRenglon,0);?>%</strong></li>
                <?php endif;?>
                </ul>    
            </div>        
        </div>
        <div id="divDatoCliente">
              <?php //if($_SESSION["ivaIncluido"]=='si'):?>
                   <!--<div class="cartelAdvertencia" style="font-size: 9px;float: left;float:left;"><strong> <i class="fa fa-certificate fa-lg"></i> Los precios publicados incluyen IVA </strong></div>-->
                <?php //else:?>
                    <!--<div class="cartelAdvertencia" style="font-size: 9px;float: left;float:left;"><strong> <i class="fa fa-certificate fa-lg"></i> Los precios publicados NO incluyen IVA </strong></div>-->
                <?php //endif;?>
                
                    <div id="saldoCliente">Saldo: <strong>$<?php echo number_format($objCliente->saldo,2,",","."); ?></strong>
        </div>           
    <?php endif;?>
    
      
</div>