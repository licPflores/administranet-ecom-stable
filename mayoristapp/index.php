<?php
// test de url para https forzado.
$https="no";

if($https=="si"){
	include_once 'seguro.inc.php';
}

#TODO: pasar a CONSTANTES TODO LAS OPCIONES
require_once 'conexion-general.inc.php';
$cartelito ="";
if(isset($_GET['cartel'])){
    $cartelito = $_GET['cartel'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="_css/main_styles.css" rel="stylesheet" type="text/css" />
    <link href="_css/font.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <!--<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">-->
   <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.css" integrity="sha256-46qynGAkLSFpVbEBog43gvNhfrOj+BmwXdxFgVK/Kvc=" crossorigin="anonymous" /> -->
   <!-- <script src="https://kit.fontawesome.com/75ecccb04e.js" crossorigin="anonymous"></script> -->
   <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

   
   <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <script src="_scripts/jquery-1.11.1.min.js"></script>
    <script src="_scripts/jquery-migrate-1.2.1.min.js"></script>

<title>administraNET e-com | Ingreso Vendedores</title>
</head>

<script>
var LOGIN = {
    onReady: function(){
        $('#loginForm').on("submit",LOGIN.mandar);
        $('#cartLogin').hide();
        var opcion = "<?php echo $cartelito;?>";
        if(opcion!==""){
            var texto = "";
            if(opcion=="1"){
                texto = "<i class='fa fa-exclamation-triangle'></i> Su sesión expiró";
            }
            if(opcion=="2"){
                texto ="<i class='fa fa-exclamation-triangle'></i> Usuario y Contraseña incorrectos";
            }
            if(opcion=="3"){
                texto ="<i class='fa fa-exclamation-triangle'></i> El sitio está en MANTENIMIENTO";
            }
            
            $('#cartLogin').html(texto);
            $('#cartLogin').show();
            
        }
        $("input[type=radio]").on("click",function(){
            $("label").removeClass("DispoSeleccionado");
            $("label[for="+ $(this).attr("id") +"]").addClass('DispoSeleccionado');
        });
        $('#txtusuario').focus();
    },
    mandar : function(event){
            
//            event.preventDefault();
            var $usu            = $('#txtusuario'),
                $clave          = $("#txtclave"),
                $emp            = $("#txtempresa"),
                $dispositivo    = $("input:checked"),
                $cartel         = $('#cartLogin');
                let boton = $('#aceptoOp');
                
            /*
             * 
             * Evaluo el dispositivo antes
             */   
//            if($dispositivo.length===0){
//                $cartel.html("<i class='fa fa-exclamation-triangle fa-lg'></i> Debe seleccionar un dispositivo");
//                $cartel.show('slow');
//                event.preventDefault();
//                
//                return false;
//            };
            var $element    = [$emp,$usu,$clave];
            boton.html('<i class="fas fa-circle-notch fa-spin"></i> Espere...');
            $.each( $element, function( i, elem ) {
                if( elem.val() ==="" ){
                    //alert("Debe completar "+elem.attr("name"));
                    $cartel.html("<i class='fa fa-exclamation-triangle fa-lg'></i> Debe completar " + elem.attr("name"));
                    $cartel.show('slow');
                    event.preventDefault();
                    boton.html('Ingresar <i class="fa fa-check fa-fw"></i>');
                    elem.focus();
                    //$cartel.hide('slow');
                }

            });
        }        
    };
$( document ).ready( LOGIN.onReady);
</script>

<body>

	<div id="wrapperLogin">
            
	  <div id="login">
      <div class="centered logo">
                    <img src="tmobile/_img/logo-administranet-ecommerce.png">
                   
            </div> 
            <h4 style="padding: 0 20px 10px 20px; text-align: center;">Mayorista y preventista</h4>                 
            <form name="loginForm" id="loginForm" action="control.php" method="post">
                <div id="loginControls" class="w90p">
                    <div class="formRow">
                        <label for="txtempresa"><i class="fa fa-building fa-lg"></i>  Empresa </label>
                            <?php
                            $sqlEmpresa="SELECT id_empresa,nombre_empresa,web_base_defecto FROM empresas";
                            $hacerLogin = mysqli_query($conexionT,$sqlEmpresa)or die(mysqli_error($conexionT));											
                            if($hacerLogin):?>
                                    <select id="txtempresa" name="empresa" class="selectBox">
                                        <option value="">-empresa-</option>
                                        <?php while($emp =  mysqli_fetch_object($hacerLogin)):?>
                                            <option <?php if($emp->web_base_defecto=="Si"){ echo "selected";}?> 
                                                value="<?php echo $emp->id_empresa;?>"><?php echo $emp->nombre_empresa;?></option>
                                        <?php endwhile;?>
                                    </select>
                            <?php endif;?>      
					</div>
 

					<div class="formRow">
                        <label for="txtusuario"><i class="fa fa-user fa-lg"></i> Usuario</label>
						<input name="usuario" id="txtusuario" type="text" class="textField" required autocomplete="username"/>
					</div>

					
					<div class="formRow">
                        <label for="txtclave"><i class="fa fa-unlock-alt fa-lg"></i> Contraseña</label>
						<input name="clave" id="txtclave" type="password" class="textField" enterkeyhint="enter" required  autocomplete="current-password"/>
					</div>
                    <div class="formRow">
<!--                        <label for="DispDesktop"><input type="radio" id="DispDesktop" name="dispositivo" value="pc"> <i class="fa fa-desktop fa-lg"></i> Pc</label>
                        <label for="DispTablet"><input type="radio" id="DispTablet" name="dispositivo" value="tablet"> <i class="fa fa-tablet fa-lg"></i> Tablet/Movil</label>
                        -->
                         <div id="cartLogin">Debo completar algo más.</div>
                    </div>                    				
                </div><!-- #loginControls -->
                <div class="buttons w100p centered">
<!--                        <input type="submit" value="Ingresar" class="boton azul grande" />-->
                    <button name="confOperacion" id="aceptoOp" type="submit" value="ok" class='botonNuevo grande azul'>Ingresar <i class='fa fa-check fa-lg'></i></button>
                </div>
            </form>
                        
		</div><!-- #login -->
       
	</div><!-- #wrapper -->

</body>
</html>

