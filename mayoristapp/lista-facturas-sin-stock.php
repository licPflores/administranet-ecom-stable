<?php 
ini_set("display_errors",1);
//session_start();
//
//session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
unset($_SESSION["sel_factura"]);
unset($_SESSION["contacto_cliente"]);
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$usaZoom = 0;

//    echo '<pre>'.print_r($_POST).'</pre>';
/*
 * Consulto la acciones a seguir si no paso al remito directo.
 * =============================================================================
 */
//    $usaFacturaStock=$_SESSION["usa_factura_stock"];
//    $editoCantStock = $_SESSION["edita_cant_stock_fact"];
    $usaFacturaStock="Si";
    $editoCantStock ="Si";
    
    $formulario =$_SESSION["formulario"];
    if($formulario=="remitoSistema"){
        $uFormulario="alta-remito-sistema.php";
    }
    if($formulario=="remitoTalonario"){
        $uFormulario="alta-remito-talonario.php";
    }
    
    $condicion =""; 
    $botonContacto="";
    $codMovEstado="";
    $completo=$_SESSION["contacto_completo"];
/*
 * Debo mostrar facturas o continuar viaje
 * =============================================================================
 */    
    if($usaFacturaStock=="No"){
        // me fui de aca 
        header("Location: {$uFormulario}");
        exit();
    }
    
/*
 * POST analizo la factura que se eligio y el permiso de si se remita obligatorio
 * =============================================================================
 * **/    
    if(isset($_POST["codMovFactura"])&&$_POST["codMovFactura"]!=""){
        //seleccione una factura a asociar.
        // busco el stock 
        // y lo debo transmitir
        $codMov=$_POST["codMovFactura"];
        $nroFact = $_POST["nroFactura"];
        $arrContacto = explode("|",$_POST["contactoCliente"]);
        
        // no hay facturas debo remitar directamente pero seleccione el contacto.
        
        if($codMov=="no"){
            
            $_SESSION["contacto_cliente"]=$arrContacto[1];
            
            header("Location: ".$uFormulario);
            exit();
        }
        
        
        $arrParam=array();
        $sqlStock="SELECT "
                . "st.IDArt,"
                . "st.no_entregado_fact,"
                . "st.cantidad_entregada_pend AS cuanto,"
                . "st.id_stock "
                . "FROM Stock AS st "
                . "WHERE st.CodigoMovimiento={$codMov} "
                . "AND st.no_entregado_fact='Si' "
                . "AND st.entregado_fact_total='No' ";
        $hs= mysqli_query($connV,$sqlStock) or die("No se pudo conseguir el stock -".mysqli_error($connV)."-{<pre>".$sqlStock."</pre>}");
        while($s= mysqli_fetch_assoc($hs)){
            $arrParam["art"][$s["IDArt"]]=$s;
        }
        // guardo la factura para que junto con el stock se envien 
        $arrParam["fact"]["codmov"]=$codMov;
        $arrParam["fact"]["nrofact"]=$nroFact;
        $_SESSION["sel_factura"] = $arrParam;
        $_SESSION["contacto_cliente"]=$arrContacto[1];
        
        //print_r($_SESSION);
        header("Location: {$uFormulario}");
        
    }
    
    
        
        
        if(isset($_SESSION['idcliente'])){
            $condicion .=" AND cc.Codigo=" .$_SESSION['idcliente'];
        }
        
        
        $sqlPedido="SELECT 
                            cc.CodigoMovimiento,
                            cc.id_cuentacliente AS id,
                            DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS FechaB,
                            cc.Fecha,
                            cc.Detalle,
                            cc.TipoComprobante,
                            cc.NroComprobante,
                            CONCAT(cc.TipoComprobante,' ',cc.NroComprobante)AS Factura,
                            cc.CondVenta,
                            cc.SubTotalGral,
                            cliente.nombre_cliente,
                            viajantes.Nombre,
                            cc.estado_fact_remito,
                            cc.Estado,                            
                            (cc.IVA1+
                            cc.IVA2)AS IVA,
                            (cc.SubTotalDesc+
                            cc.IVA1+
                            cc.IVA2) AS Total
                            
                    FROM 
                        CuentaCliente AS cc
                        LEFT JOIN cliente ON cliente.Codigo = cc.Codigo
                        LEFT JOIN viajantes ON viajantes.CodViajante = cc.CodViajante
           
                    WHERE                    
                    cc.remite_factura_art='No'
                    AND
                    (cc.TipoFacturaPR='Factura Comun' OR cc.TipoFacturaPR='Factura Pedido')
                     ".$condicion." 
                    AND cc.TipoComprobante IN ('FA','FB','FC','FM')
                    AND (cc.estado_fact_remito='Pendiente' OR cc.estado_fact_remito='Parcial') 
                     
                    AND cc.anulado='No'
                                        
                     
                    ORDER BY cc.Fecha DESC,cc.CodigoMovimiento DESC LIMIT 10";
        $hacerPed = mysqli_query($connV,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlPedido);
//        echo $sqlPedido.'<br>';
//        $totalFact=mysqli_fd
        
        
        $facturas=array();
        $facturaFinalizo=array();
        $contador=0;
		$noCodFact=null;
        // facturas a remitar
        while($factura = mysqli_fetch_object($hacerPed)){
            if($contador<1){			
                
                $noCodFact=$factura->CodigoMovimiento;
				
				//voy a averiguar si la factura tiene imputacion si hay no la pongo en facturar que ni aparezca no se toque
				
				$sqlImputacion="SELECT imputacion.id_imputacion 
								FROM imputacion 
								WHERE imputacion.codmov_fac_nd=".$factura->CodigoMovimiento." 
								AND imputacion.tipo='Crédito' AND imputacion.Anulado='No';";
				$hacerControl = mysqli_query($connV,$sqlImputacion) or die('No puedo encontrar imputacion'.mysqli_error($connV).'<br>'.$sqlPedido);				
				$hayNC=array();
				 while($nc = mysqli_fetch_object($hacerControl)){
					 $hayNC[]=$nc;
				 }
				/* echo "<PRE>";
				print_r($hayNC);
				
				echo "<PRE>"; */				
				// sin imputacion
				if(empty($hayNC)){
					$facturas[] = $factura;
					$contador++;
				}else{
					$contador=0;
				}
				
				
            }else{
                if($noCodFact!==$factura->CodigoMovimiento){
					$facturaFinalizo[$factura->CodigoMovimiento] = $factura;
				}
				$contador++;
            }
            
        }
        
        // facturas que finalizo
        //echo "dentro de las facturitas que debo dar de baja";
//        while($facturaf = mysqli_fetch_object($hacerPed)){
//            echo "facturaf:<pre>";
//            print_r($facturaf);
//            echo "</pre>";
//             if($noCodFact!==$facturaf->CodigoMovimiento){
//                 $facturaFinalizo[] = $facturaf;
//             }
//        }
        
        
        // no hay facturas creo el boton para pasar de largo
        if(empty($facturas)){
            //header("Location: {$uFormulario}");
            $botonContacto='<button id="remitarContacto" class="botonNuevo grande azul"><i class="fa fa-check fa-lg"></i> remitar</button>';
            $codMovEstado="no";
            //exit();
        }
        
        
        
        //hay facturas anteriores debo editar su estado a En Remito
        if(!empty($facturaFinalizo)){
            $condicion= implode(",", array_keys($facturaFinalizo));
            
            
            $sqlFinaliza="UPDATE CuentaCliente AS cc SET "
                    . "cc.estado_fact_remito='En Remito' "
                    . "WHERE cc.CodigoMovimiento IN(".$condicion.");";
            
//            echo "facturas a finalizar::<pre>";
//            print_r($sqlFinaliza);
//            echo "</pre>";
            
            $hr=mysqli_query($connV,$sqlFinaliza)or die("No puedo Finalizar la factura ".mysqli_error($connV)."<pre>".$sqlFinaliza."</pre>");
            $sqlFfin="SELECT cc.CodigoMovimiento,
                            cc.id_cuentacliente AS id,
                            DATE_FORMAT(cc.Fecha,'%d/%m/%Y') AS FechaB,
                            DATE_FORMAT(cc.Fecha,'%Y%m%d') AS FechaOrden,
                            cc.Fecha,
                            cc.Detalle,
                            cc.TipoComprobante,
                            cc.NroComprobante,
                            CONCAT(cc.TipoComprobante,' ',cc.NroComprobante)AS Factura,
                            cc.CondVenta,
                            cc.SubTotalGral,
                           
                            cc.estado_fact_remito,
                            cc.Estado,                            
                            (cc.IVA1+
                            cc.IVA2)AS IVA,
                            (cc.SubTotalDesc+
                            cc.IVA1+
                            cc.IVA2) AS Total
                        FROM Cuentacliente AS cc 
                        WHERE cc.CodigoMovimiento IN(".$condicion.") "
                    . "ORDER BY cc.CodigoMovimiento DESC;";
            $hrr=mysqli_query($connV,$sqlFfin)or die("No puedo recuperar la factura ".mysqli_error($connV)."<pre>".$sqlFfin."</pre>");
            $facturaLista=array();
            while($factLista = mysqli_fetch_object($hrr)){
                $facturaLista[]=$factLista;
            }
        }
        
        // busco los contactos.
        
        $sqlC="SELECT "
                . "contacto.nombre_cliente_contacto AS nombre,"
                . "contacto.tipo_doc,"
                . "contacto.nro_doc,"
                . "contacto.id_cliente_contacto AS codigo "
                . "FROM cliente_contacto AS contacto "
                . "WHERE contacto.id_cliente=".$_SESSION['idcliente']." "
                . "ORDER BY contacto.nombre_cliente_contacto ASC";
        $hc=mysqli_query($connV,$sqlC) or die("No puedo encontar los contactos ".mysqli_error($connV)."<pre>".$sqlC."</pre>");
        $txtLista='<option value="">-seleccione responsable-</option>';
        $cuantosC = mysqli_num_rows($hc);
        while($c= mysqli_fetch_assoc($hc)){
            if($cuantosC==1){
                $txtLista .='<option value="'.$c["codigo"].'|'.$c["nombre"].' - '.$c["tipo_doc"].' '.$c["nro_doc"].'" selected="selected">'.$c["nombre"].'</option>';
            }else{
                $txtLista .='<option value="'.$c["codigo"].'|'.$c["nombre"].' - '.$c["tipo_doc"].' '.$c["nro_doc"].'">'.$c["nombre"].'</option>';
            }
        }
        
        
    ?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Facturas a remitar | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    <script>
  $(document).ready(function() { 
      $(".form-alta-contacto").hide();
      $('#myTable').DataTable({
          searching:false,
          responsive:false,
           "language": {
               "emptyTable":     "No data available in table",
               "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
               "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
               "infoFiltered":   "(filtrado de _MAX_ resultados)",
               "infoPostFix":    "",
               "thousands":      "",
               "lengthMenu":     "Ver _MENU_ entradas",
               "loadingRecords": "Loading...",
               "processing":     "Processing...",
               "search":         "Buscar:",
               "zeroRecords":    "No matching records found",
               "paginate": {
                   "first":      "Primero",
                   "last":       "Ultimo",
                   "next":       "Siguiente",
                   "previous":   "Anterior"
               },
               "aria": {
                   "sortAscending":  ": activate to sort column ascending",
                   "sortDescending": ": activate to sort column descending"
               }
               
           },
           "order": [[ 1, "desc" ]]
       });  
       
       $('#myTableFin').DataTable({
          searching:false,
          responsive:false,
           "language": {
               "emptyTable":     "No data available in table",
               "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
               "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
               "infoFiltered":   "(filtrado de _MAX_ resultados)",
               "infoPostFix":    "",
               "thousands":      "",
               "lengthMenu":     "Ver _MENU_ entradas",
               "loadingRecords": "Loading...",
               "processing":     "Processing...",
               "search":         "Buscar:",
               "zeroRecords":    "No matching records found",
               "paginate": {
                   "first":      "Primero",
                   "last":       "Ultimo",
                   "next":       "Siguiente",
                   "previous":   "Anterior"
               },
               "aria": {
                   "sortAscending":  ": activate to sort column ascending",
                   "sortDescending": ": activate to sort column descending"
               }
               
           },
           "order": [[ 0, "desc" ]]
       });  
      
     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
        

     $('#myTable tbody').on("click","td a.selFactura", function(){
        //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
            var divModal      = $('.cartelContacto');
            var codigoFactura = $(this).attr('mov');
            var nroFacturaTxt = $(this).attr('comprobante');
            var nroFactura    = $(this).attr('numero');
            var selContacto   = $("#selContacto").val(); 
            
            var ventana       = $("#basic-modal-content");
            console.log("codmovfactura{"+codigoFactura+"}");
            $("#codMovFactura").val(codigoFactura);
            $("#nroFactura").val(nroFactura);
            if(selContacto===""){
                var textoError='<div id="alertas-formulario" class="alerta-error">';
                    textoError+='<strong>';
                    textoError+='<i class="fa fa-warning"></i> Atención! </strong><br>';                  
                    textoError +='<span class="texto-alerta">Debe seleccionar <u>Responsable</u></span><br>';
                    textoError+='</div>';
                 divModal.html(textoError);
                 divModal.show();
                 return false;
                    
            }
            $("#contactoCliente").val(selContacto);
            
            
           
            var contenidoVentana='<div id="renglonlote" >';
            
            
            
            contenidoVentana +='<div class="contiene-renglon">Desea remitar: <strong>'+nroFacturaTxt+'</strong></div>';
            contenidoVentana +='<div id="campoDato" name="boton">';
            contenidoVentana +='<button id="aceptaFact" class="botonNuevo grande azul"><i class="fa fa-check fa-lg"></i> Continuar</button>';
            contenidoVentana +='<button id="cancelaFact" class="botonNuevo grande gris"><i class="fa fa-times fa-lg"></i> Cancelar</button>';
            contenidoVentana +='</div></div>';
            
           
            
            
            ventana.html(contenidoVentana);
            ventana.modal({
                            minWidth:300,
                            minHeight:100,
                            maxHeight:300,

                            close: false,
                            onShow: function(){

                                    $('#cancelaFact').on("click",function(e){
                                        e.preventDefault();
                                       $.modal.close(); 
                                    });
                                    $('#aceptaFact').on("click",function(e){
                                         console.log("envie valores");
                                        e.preventDefault();
                                       $.modal.close(); 
                                        $("#formFactura").submit();
                                    });
                                }
            });
        });
    
//    $("#formFactura").on("submit",function(){
//       //e.preventDefault();
//       //console.log("dentro del submit");
//       $(this)submit();      
//      
//    }); 
    
    /*
     * CONTACTO
     * =========================================================================
     */
    
    $("#remitarContacto").on("click",function(){
        console.log("dentro del boton que remita directo");
            var divModal      = $('.cartelContacto');           
            var selContacto   = $("#selContacto").val();  
            console.log("valContacto:"+divModal);
            if(selContacto===""){
                console.log("dentro del contacto vacio");
                var textoError='<div id="alertas-formulario" class="alerta-error">';
                    textoError+='<strong>';
                    textoError+='<i class="fa fa-warning"></i> Atención! </strong><br>';                  
                    textoError +='<span class="texto-alerta">Debe seleccionar <u>Responsable</u></span><br>';
                    textoError+='</div>';
                 divModal.html(textoError);
                 divModal.show();
                 return false;
                    
            }
            $("#contactoCliente").val(selContacto);
            $("#formFactura").submit();
    }); 
    
    $("#agregarContacto").on("click",function(){
        var formulario = $(".form-alta-contacto");
        formulario.show();
    });
    
    
    function resetFormulario(){
        
        var nombreC=$('#nombreContacto'),          
          telefonoC=$('#telefonoContacto'),
          emailC=$('#emailContacto'),
          tipoDocC=$('#tipoDocContacto'),
          nroDocC=$('#nroDocContacto');
          
          nombreC.val("");
          telefonoC.val("");
          emailC.val("");
          tipoDocC.val("");
          nroDocC.val("0");
          
        
    }
    
    var validarForm= function(){
      //validamos el formulario que no este vacio.
        var   error=0,
            textoError=""; 
        var divModal=$('.cartelCliente');
        var todosCampos=$("#completo").val();
        // validar que no exista el cliente
        var nombreC=$('#nombreContacto'),              
            telefonoC=$('#telefonoContacto'),
            emailC=$('#emailContacto'),
            tipoDocC=$('#tipoDocContacto'),
            nroDocC=$('#nroDocContacto');

            textoError+='<div id="alertas-formulario" class="alerta-error">';
            textoError+='<strong>';
            textoError+='<i class="fa fa-warning"></i> Atención! </strong><br>' ;

        if(todosCampos=="Si"){    
           
                if(nombreC.val()===""){
                    //nombreC.focus();
                    textoError +='<span class="texto-alerta">Debe Completar el <u>nombre de Responsable</u></span><br>';
                    error++;
                }

                if(emailC.val()===""){
                    //tipoC.focus();
                    textoError +='<span class="texto-alerta">Debe Completar el <u>E-mail</u></span><br>';
                    error++;
                }
                if(telefonoC.val()===""){
                    //tipoC.focus();
                    textoError +='<span class="texto-alerta">Debe Completar el <u>telefono o movil</u></span><br>';
                    error++;
                }

                if(tipoDocC.val()===""){
                    textoError +='<span class="texto-alerta">Debe seleccionar el <u>Tipo de documento</u></span><br>';
                    error++;
                }

                if(tipoDocC.val()!=="CUIT" && nroDocC.val()===""){
                    //tipoC.focus();
                    textoError +='<span class="texto-alerta">Debe Completar el <u>número de documento</u>';
                    textoError +='<br>utilice CERO(0) si no lo conoce</span>';
                    error++;
                }
            }else{
                if(nombreC.val()===""){
                    //nombreC.focus();
                    textoError +='<span class="texto-alerta">Debe Completar el <u>nombre de Responsable</u></span><br>';
                    error++;
                }
                

                if(tipoDocC.val()===""){
                    textoError +='<span class="texto-alerta">Debe seleccionar el <u>Tipo de documento</u></span><br>';
                    error++;
                }

                if(tipoDocC.val()!=="CUIT" && nroDocC.val()===""){
                    //tipoC.focus();
                    textoError +='<span class="texto-alerta">Debe Completar el <u>número de documento</u>';
                    textoError +='<br>utilice CERO(0) si no lo conoce</span>';
                    error++;
                }
            }  

            if(error>0){
                 textoError+='</div>';
                 divModal.html(textoError);
                 divModal.show();
                 return false;
            }else{           
                //terminar la validacion agregar la validacion del cuit.
                return true;
            }        
    };
    
    
    //alta de contacto y vuelta a listar
    $("#altaContacto").on("click",function(){
        var divModal=$('.cartelCliente');
        var formulario=$('.form-alta-contacto');
        var listaContacto = $("#selContacto");
        // controlar que este todo ok.
        if(validarForm()===true){
            var nombreC=$('#nombreContacto').val(),              
                telefonoC=$('#telefonoContacto').val(),
                emailC=$('#emailContacto').val(),
                tipoDocC=$('#tipoDocContacto').val(),
                nroDocC=$('#nroDocContacto').val();
                console.log("Nombre:"+nombreC);
            $.ajax({
                type:'POST',
                url:'relay-contacto-cliente.php',
                data:{
                    "ajax":"true",
                    "accion":"alta",
                    "nombreContacto":nombreC,
                    "telefonoContacto":telefonoC,
                    "emailContacto":emailC,
                    "tipoDocContacto":tipoDocC,
                    "nroDocContacto":nroDocC
                    
                },
                success: function(response){
                    console.log("resultado no:?? "+response);
                   // console.log("fallo "+response.cartel);
                    var a=response;
                    var cartelito="";
    //                            console.log("a:=>"+a.cartel);
                    if(a==="0"){
                        //alert("todo MAL para los pibes");
                        cartelito+='<div id="alertas-formulario" class="alerta-error">';
                        cartelito+='<strong>';
                        cartelito+='<i class="fa fa-warning"></i> Atención! </strong><br>' ;
                        cartelito+='<span class="texto-alerta">No se pudo ingresar responsable</span>';
                        cartelito+='</div>';
                        divModal.html(cartelito);
                        divModal.show();
                        divModal.hide("slow");
                       // $('#basic-modal-content').modal();
                    }else{
                        //alert("tudo bien");
                        cartelito+='<div id="alertas-formulario" class="alerta-exito">';
                        cartelito+='<strong>';
                        cartelito+='<i class="fa fa-check-circle fa-lg"></i>' ;
                        cartelito+='<span class="texto-alerta">Responsable ingresado con exito!</span></strong>';
                        cartelito+='</div>';
                        
                        divModal.html(cartelito);
                        //$('#basic-modal-content').modal();
                        listaContacto.html(response);
                        resetFormulario();
                        divModal.show();
                        divModal.hide(3000);
                        formulario.hide(3000);

                    }


                },
                error: function(x, e) {
                                var s = x.status, 
                                m = 'Ajax error: ' ; 
                                if (s === 0) {
                                    m += 'Check your network connection.';
                                }
                                if (s === 404 || s === 500) {
                                    m += s;
                                }
                                if (e === 'parsererror' || e === 'timeout') {
                                    m += e;
                                }
                                alert(m);
                            }
            });
        }
    });
      
     
      
 });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content" class="noPrint">           
            
           	<div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>

            <div id="contiene-tabla">
            	<div class="cartelContacto" id="cartelContacto"></div> 

					<h2>1. Responsable </h2>
					<h4><?php echo $objCliente->cliente ; ?></h4>

					<div class="renglonForm">
						<label for="selContacto"> 
							<button class="botonNuevo grande azul" id="agregarContacto" name="agregarContacto"><i class="fa fa-plus fa-lg"></i> Agregar</button>
							<?php echo $botonContacto;?>
						</label>
					</div>
					<div class="renglonForm">
						<select name="selContacto" id="selContacto">
							<?php echo $txtLista;?>
						</select>
					</div>
				</div>
			</div>	

            <div id="contiene-tabla" class="form-alta-contacto">
                <div class="cartelCliente" id="cartelNuevo"></div> 
                <h3>Alta Responsable</h3>
                
				<div class="paneles panelesBloqueInforme">
					<div class="control">
						<label for="calleCliente">Apellido y Nombre:<em>*</em></label>
						<input type="text" id="nombreContacto" name="nombreContacto"  placeholder="Apellido y Nom..." required="required">
					</div>
					
					<div class="control">
						<label for="tipoDocContacto">Tipo Doc<em>*</em><br></label>
						<select name="tipoDocContacto" id="tipoDocContacto">
							<option value="">- tipo documento -</option>
							<option value="DNI">DNI</option>
							<option value="LE">LE</option>
							<option value="LC">LC</option>
							<option value="CIE">CIE</option>
							<option value="PAS">PAS</option>
						</select>
					</div>    
					<div class="control">    
						<label for="nroDocCliente">Nro<em>*</em><br></label>
						<input type="number" id="nroDocContacto" name="nroDocContacto" placeholder="documento de identidad...">
					</div>
					<?php if($completo=="Si"):?>
						<div class="control">
							<label for="telefonoCliente">Telefono<em>*</em></label>
							<input type="tel" id="telefonoContacto" name="telefonoContacto"  placeholder="Telefono..." required="required">
						</div>
						<div class="control">
							<label for="emailContacto">E-mail:<em>*</em></label>
							<input type="email" id="emailContacto" name="emailContacto"  placeholder="email..." required="required">
						</div>
					<?php endif;?>
					<div class="control control-con-boton">
						<label></label>
						<button id="altaContacto" class="botonNuevo"><i class="fa fa-check fa-lg"></i> Guardar</button>                   
						<input type="hidden" name="completo" id="completo" value="<?php echo $completo;?>">
					</div>
				</div>
            </div>

            <div id="contiene-tabla">    
				<form method="post" name="formFactura" id="formFactura" action="">    
					
					<?php if(!empty($facturas)):?>

						<h2>2. Seleccionar facturas pendientes</h2>

						<table class="display" cellspacing="1" id="myTable">
							<thead>
								<tr>
									<th>&nbsp</th>
									<th>Fecha</th>                               
									<th>N°Comp.</th>
								
								</tr>
							</thead>
							<tbody>
								<?php foreach($facturas as $factura):?>
									<tr>
										<td class="acciones">
											<a href="#" class="selFactura" title="Remitar Factura" alt="Remitar Factura" mov="<?php echo $factura->CodigoMovimiento;?>" comprobante="<?php echo $factura->Factura;?>" numero="<?php echo $factura->NroComprobante;?>">
											<i class='fa fa-check-circle fa-lg fa-3x' ></i> 
											</a>
										</td>

										<td class="dt-nowrap">
											<?php echo $factura->FechaB;?>
										</td>
										
										<td class="dt-nowrap" >
											<span><strong><?php echo $factura->TipoComprobante.'</strong> '.$factura->NroComprobante;?></span><br>
											<span><?php echo $factura->Detalle;?></span><br>
											<span><strong>Total: </strong>$<?php echo number_format($factura->Total,2,",",".");?></span>
										</td>
									</tr>
								<?php endforeach;?>
							</tbody>
						</table>                   

					<?php endif;?>
					<?php if(isset($facturaLista)&&!empty($facturaLista)):?>
						<h2>3. Facturas previas finalizadas</h2>
							<table class="display" cellspacing="1" id="myTableFin">
								<thead>
									<tr>
										<th>Fecha</th>                               
										<th>N°Comp.</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($facturaLista as $factura):?>
										<tr>
											
											<td class="dt-nowrap" data-order="<?php echo $factura->FechaOrden;?>">
												<?php echo $factura->FechaB;?>
											</td>
											
											<td class="dt-nowrap" >
												<?php echo $factura->TipoComprobante.' '.$factura->NroComprobante;?>
												<span><?php echo $factura->Detalle;?></span><br>
												<span>Total: <?php echo $factura->Total;?></span>
											</td>

										</tr>
									<?php endforeach;?>
								</tbody>
							</table>                  
					<?php endif;?>    

					<input type="hidden" name="codMovFactura" id="codMovFactura" value="<?php echo $codMovEstado;?>">
					<input type="hidden" name="nroFactura" id="nroFactura">
					<input type="hidden" name="contactoCliente" id="contactoCliente">

				</div>
				
				<div id="basic-modal-content" ></div>
			</form>
        </div>
 
        <?php require_once 'footer.php';?>   
    
        </div>
        
    </body>
</html>