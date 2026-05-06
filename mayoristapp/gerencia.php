<!DOCTYPE html>
<?php


?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="_css/main_styles.css" rel="stylesheet" type="text/css" />
    <!--<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">-->
    

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.css" integrity="sha256-46qynGAkLSFpVbEBog43gvNhfrOj+BmwXdxFgVK/Kvc=" crossorigin="anonymous" />
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <script src="_scripts/jquery-1.11.1.min.js"></script>
    <script src="_scripts/jquery-migrate-1.2.1.min.js"></script>
     <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<title>administraNET e-com | Ingreso Negocio</title>
</head>

<script>
$(document).ready(function(){

  // jQuery methods go here...
	$('#aceptoOp').on("click",function(){
		event.preventDefault();
		
		var queNegocio=$('#txtNegocio');
		console.log("Texto Seleccionado");
		console.log(queNegocio.text());
		console.log("Valor Seleccionado");
		console.log(queNegocio.val());
		
		if (queNegocio.val()==""){
			 swal("Ooops!","Debe seleccionar un Negocio","warning");
			return false;
		}else{
			console.log("submitting");
			$('#formNegocio').submit();
		}
	});
	$('#txtNegocio').on("change",function(){
		console.log("quien soy?"+$("#txtNegocio option:selected").text());
		var  negocio=$("#txtNegocio option:selected").text();
		$('#nombreNegocio').val(negocio);
		
	});
});
</script>

<body>

	<div id="wrapperLogin">
            
	  <div id="login">
        	<div class="centered logo">
                    <img src="_img/administanet-ecom-grande.png">
            </div>                
            <form name="formNegocio" id="formNegocio" action="index.php" method="post">
                <div id="loginControls" class="w90p">
                    <div class="formRow">
                        <label for="txtNegocio"><i class="fas fa-store fa-lg fa-fw"></i>  Seleccione un negocio </label>
                            
                                    <select id="txtNegocio" name="txtNegocio" class="selectBox">
                                        <option value="">-Negocio-</option>
										<option value="localhost:30804">Distribuidora JA&NA</option>
										<option value="190.15.212.52:30804"> Fenix </option>	
										<option value="warp.ddns.net:30804"> Mayorista Nina </option>	
                                        <option value="190.15.208.194:30804"> Feria Godoy cruz </option>    
                                        
                                    </select>
									<input type="hidden" name="nombreNegocio" id="nombreNegocio" >
                            
					</div>
 

					
                <div class="buttons w100p centered">
<!--                        <input type="submit" value="Ingresar" class="boton azul grande" />-->
                    <button name="confOperacion" id="aceptoOp" type="submit" value="ok" class='botonNuevo grande azul'>Siguiente <i class='fa fa-chevron-right fa-lg fa-fw'></i></button>
                </div>
           </form>
                        
		</div><!-- #login -->
       
	</div><!-- #wrapper -->

</body>
</html>

