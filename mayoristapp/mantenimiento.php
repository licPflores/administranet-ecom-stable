<?php  
require_once 'conexion-general.inc.php';
?>
<!DOCTYPE html>
<?php
$cartelito ="";
if(isset($_POST["accion"])){
    $puntoVenta = null;
   
    // ejecuto el script.
    // pv
    // me traigo la base sobre la que hacer el mantenimiento. o bien la que elimino.

    $puntoVenta1 = 5;
	$puntoVenta2 = 6;
//    $dataBase = $_POST["empresa"];
    $dataBase = "administranet_delete";
    
    //conexion.
    
        $baseConecto = $dataBase;
        mysql_select_db($baseConecto,$conexionT);
        mysql_set_charset('utf8',$conexionT);
    if($puntoVenta1){
        
        $errores = 0;
        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysql_query($sqlTotal) or die('No puedo iniciar autocommit '.mysql_error());
        $sqlTotal = "BEGIN;";
        $resultado = mysql_query($sqlTotal) or die('No puedo hacer Begin '.mysql_error());
    
        $sqlTotal ="DELETE punto_venta.*, 
                    cuentacliente.*, 
                    stock.*, 
                    recibo_factura.*, 
                    recibo_factura_par.*, 
                    caja.*, 
                    cont_asiento.* 
                FROM punto_venta             
                    LEFT JOIN cuentacliente ON (cuentacliente.id_pv = punto_venta.id_punto_venta)
                    LEFT JOIN stock ON (stock.CodigoMovimiento = cuentacliente.CodigoMovimiento OR stock.codigo_movimiento_anul = cuentacliente.CodigoMovimiento)
                    LEFT JOIN recibo_factura ON (recibo_factura.CodigoMovimiento = cuentacliente.CodigoMovimiento)
                    LEFT JOIN recibo_factura_par ON (recibo_factura_par.CodigoMovimiento = cuentacliente.CodigoMovimiento)
                    LEFT JOIN caja ON (caja.codigo_movimiento = cuentacliente.CodigoMovimiento OR caja.codigo_movimiento_anul = cuentacliente.CodigoMovimiento)
                    LEFT JOIN cont_asiento ON (cont_asiento.codigo_movimiento = cuentacliente.CodigoMovimiento OR cont_asiento.codigo_movimiento_anul = cuentacliente.CodigoMovimiento)
                WHERE punto_venta.id_punto_venta = {$puntoVenta1} OR punto_venta.id_punto_venta = {$puntoVenta2}";
        $resultado = mysql_query($sqlTotal);
        
        if(!$resultado){
            $textoError = mysql_errno() .":". mysql_error();
            $errores++;
        }
        if($errores == 0){
            $sqlTotal= "COMMIT;";
            $resultado = mysql_query($sqlTotal);
            $cartelito = 3;
            //echo "todo bien";
        }else{
            $sqlTotal = "ROLLBACK;";
            $resultado = mysql_query($sqlTotal);
            $cartelito = 1;
            //echo "todo mal";
        }
    }else{
        
            $sql = "DROP DATABASE IF EXISTS {$dataBase};";
            $resultado = mysql_query($sql);
            if(mysql_error()){
                 $textoError = mysql_errno() .":". mysql_error();
                 $cartelito = 1;
            }else{
                $cartelito = 3;
            }
                
            
        
        
    }
}
?>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="_css/main_styles.css" rel="stylesheet" type="text/css" />
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>
var LOGIN = {
    onReady: function(){
        $('#loginForm').on("submit",LOGIN.mandar);
        $('#cartLogin').hide();
        var opcion = "<?php echo $cartelito;?>";
        if(opcion!==""){
            var texto = "";
            if(opcion=="1"){
                texto = "<i class='fa fa-exclamation-triangle fa-lg'></i> ocurrio un error comunicarse con el administrador <br> <?php echo $textoError;?>";
            }
            if(opcion=="2"){
                texto ="<i class='fa fa-exclamation-triangle fa-lg'></i> Usuario y Contraseña incorrectos";
            }
            if(opcion=="3"){
                texto ="<i class='fa fa-check fa-lg'></i> El mantenimiento se ejecutó correctamente.";
            }
            $('#cartLogin').html(texto);
            $('#cartLogin').show();
            
        }
        $("input[type=radio]").on("click",function(){
            $("label").removeClass("DispoSeleccionado");
            $("label[for="+ $(this).attr("id") +"]").addClass('DispoSeleccionado');
        });
    },
    mandar : function(event){
            //alert("hola");
            //event.preventDefault();
            
             var $dispositivo    = $("input:checked"),
                 $cartel         = $('#cartLogin');
                
            /*
             * 
             * Evaluo el dispositivo antes
             */   
            if($dispositivo.length===0){
                $cartel.html("<i class='fa fa-exclamation-triangle fa-lg'></i> Debe seleccionar una opcion");
                $cartel.show('slow');
                event.preventDefault();
                
                return false;
            };
            console.log($dispositivo);
            if($dispositivo.val()==='no'){
                return false;
            }
            
            
        }        
    };
$( document ).ready( LOGIN.onReady);
</script>


<title>administraNET e-com | mantenimiento</title>
</head>

<body>

	<div id="wrapperLogin">
            
	  <div id="login">
        	<div class="centered logo">
                <!--<img src="sistema/_img/logo-administranet-ecommerce.png">-->
                <img src="foto-logo.php" />
               
            </div>                
            <form name="loginForm" id="loginForm" action="" method="post">
                <div id="loginControls" class="w90p">
                    <div class="formRow">
                    <label>
                           Está seguro que desea realizar el mantenimiento?</label>  
                    </div>
                    
                    <div class="formRow">
                        
                        <label for="mantenimientoSi">
                            <input type="radio" id="mantenimientoSi" name="accion" value="si"> 
                            <i class="fa fa-check-circle fa-lg"></i> Si</label>
                        <label for="mantenimientoNo">
                            <input type="radio" id="mantenimientoNo" name="accion" value="no"> 
                            <i class="fa fa-times-circle fa-lg"></i> No</label>
                    </div>                    				
                </div><!-- #loginControls -->
                <div class="buttons w100p centered">
<!--                        <input type="submit" value="Ingresar" class="boton azul grande" />-->
                    <button name="confOperacion" id="aceptoOp" type="submit" value="ok" class='botonNuevo grande azul'>Aceptar <i class='fa fa-check fa-lg'></i></button>
                </div>
            </form>
                      
		</div><!-- #login -->
        <div id="cartLogin">Debo completar algo más.</div>
        <div style="margin:auto; padding: 5%;text-align: center; color: black;">
            <a href="http://www.administranet.com.ar" style="color:black; text-decoration: none;" title="administraNET sistema de gestión contable e-commerce Mendoza Argentina">
                <img src="_img/logo-administranet-ecommerce.png" style="width:150px;">
                <br>powered by administraNET 2014. 
                </a>
        </div>
	</div><!-- #wrapper -->

</body>
</html>

