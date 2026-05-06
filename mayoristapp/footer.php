<?php
//    session_start();
//    $caminoDispo = $_SESSION['caminoDisp'];
  if($caminoDispo==""){  
  echo '<div id="footer" class="noPrint">';
  echo '
            <div class="nombreEmpresa">
                '.$_SESSION['nombre_empresa'].'
            </div>
            <div>
            <span class="datoEmpresa">
                <i class="fa fa-home fa-lg"></i>'.$_SESSION['domicilio_empresa'] .'</span>
            <span class="datoEmpresa">
                <i class="fa fa-phone fa-lg"></i>'.$_SESSION['telefono_empresa'].'
            </span>
            <span class="datoEmpresa">
                <i class="fa fa-info fa-lg"></i>'.$_SESSION['cuit_empresa'].'</span>
                     
            </div>
            <div>
            <a href="https://www.administranet.com.ar" title="administraNET gestión software de facturación gratis" target="_blank">
            <img src="_img/logo-administranet-ecommerce.png" alt="desarrollado por administraNET gestión" title="administraNET gestión" />
            </a>
</div>
        ';
  echo '   ';
//  echo 'E-commerce';
  echo '</div> ';
  }
