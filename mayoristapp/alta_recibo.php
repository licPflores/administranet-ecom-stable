<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
/* 
 * RECIBO WEB CON EASY UI
 * programacion 100% mobil
 * por pasos con guia.
 * 
 * 
 */
require_once 'sesion.inc.php';
//echo "<pre>";
//print_r($_SESSION['vendedor']);
if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}
//
//echo "</pre>";
//print_r($_SESSION["puntos_de_venta_usr"]);
$pvta= array();
if(isset($_SESSION["puntos_de_venta_usr"])){
    $pvta=$_SESSION["puntos_de_venta_usr"];
}
$usuario=$_SESSION["vendedor"];
//print_r($usuario);
?>
<!doctype html>
<html>
<head>
     <meta charset="UTF-8">  
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Nuevo Recibo</title>  
    
    <!--<link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/color.css">-->

    <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/mobile.css"> 
    <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/bootstrap/easyui.css"> 
    <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/icon.css">  
   
<!--     <link rel="stylesheet" type="text/css" href="_css/main_styles.css">-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous"> 
    <script type="text/javascript" src="_lib/easyui/jquery.min.js"></script>  
    <script type="text/javascript" src="_lib/easyui/jquery.easyui.min.js"></script> 
    <script type="text/javascript" src="_lib/easyui/jquery.easyui.mobile.js"></script> 
    <!--meter las swift si no joden..y permiten cuando sea necesario mostrarlas.-->
     <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
     <style>
         .spinner {
            background-color:       #e8e8e8a8; 
/*            top:                    0%;
            left:                   0%;*/
            text-align:             center;
            z-index:                1234;
            overflow:               auto;
            width:                  100%; /* width of the spinner gif */
            height:                 100%; /*hight of the spinner gif +2px to fix IE8 issue */
           
            border:                 1px solid #DDDDDD;
            position:               absolute;
            padding:                10px;
            top:0;
        }
        .spinner div.centro{
            margin-top: 30%;
            background-color: #ffffff;
            height:150px;
            text-align: center;
            padding: 30px;
        }
        .spinner div.texto{
            margin-top: 30px;
            font-size: 20px;
        }
     </style>
</head>
    <body>
    <div class="easyui-navpanel" id="P1">
        <header>
            <div class="m-toolbar">
                <div class="m-title">Recibo </div>
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralEf',menuAlign:'left'"><i class="fas fa-bars"></i></a>

                </div>
            </div>

        </header>
         <div id="mGeneralEf" class="easyui-menu" style="width:200px;" data-options="itemHeight:30,noline:true">
            <div class="menu-sep"></div>
            <div> <i class="fas fa-user-circle fa-lg fa-fw"></i><?php echo $clienteObj->cliente; ?></div>
            <div class="menu-sep"></div>
             <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="salida();"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
        </div>
        <div style="padding:5px;" >           
            <p style="text-align: center" id="nombreCliente"><?php 
//            echo "<pre>";
//            print_r($clienteObj);
//            echo "</pre>";
            echo "<strong>".$clienteObj->cliente." (".$clienteObj->Codigo.")";  ?></strong></p>
            <p style="text-align:right;">Saldo: <strong>$<?php echo  number_format($clienteObj->saldo,2,",","."); ?></strong></p>
            <input type="hidden" name="idCliente" id="idCliente" value="<?php echo  $clienteObj->Codigo; ?>">
            <input type="hidden" name="saldoCliente" id="saldoCliente" value="<?php echo  $clienteObj->saldo; ?>">
            <input type="hidden" name="idPcCliente" id="idPcCliente" value="<?php echo  $clienteObj->id_pc; ?>">
        </div>
        <div style="padding:10px;" >            
            <!--<p>1. Acción del recibo</p>-->
<!--            <span class="m-buttongroup">
                 <a href="javascript:void(0)" class="easyui-linkbutton cRecibo" id="aCuenta" data-options="toggle:true,group:'g2',disabled:true,selected:false"  style="width:50%;height:30px">Dinero a cuenta</a> 
                 <a href="javascript:void(0)" class="easyui-linkbutton cRecibo" id="imputacion" data-options="selected:true"  style="width:100%;height:30px">Imputar facturas</a> 
               
            </span>-->
        <a href="javascript:void(0)" class="easyui-linkbutton cRecibo" id="imputacion" data-options="selected:true"  style="width:100%;height:30px">Imputar facturas</a> 
           
            
        </div>   
        <div style="padding:10px" >
          <div style="padding:10px 10px;">
                    <!--<input class="easyui-numberbox" type="number"  inputmode="numeric" pattern="[0-9]*" id="puntoVenta" min="0" max="99999" required="true" missingMessage="Debe completar el punto de vta recibo"  style="width:100%" data-options="label: 'Punto venta',labelPosition:'top'" >--> 
                      <?php if(!empty($pvta)):?>
                        <select class="easyui-combobox" title="Punto de venta" lines="true" id="puntoVenta" data-options="label:'Punto Vta:'" style="width:99%">
                        <?php foreach($pvta as $pv):?>
                            <?php if($pv["id_punto_venta"]==$usuario->id_punto_venta):?>
                            <option value="<?php echo $pv["id_punto_venta"]."|".$pv["nro_punto_venta"];?>" selected><?php echo str_pad($pv["nro_punto_venta"], 4,"0",STR_PAD_LEFT); ?></option>
                            <?php else:?>
                            <option value="<?php echo $pv["id_punto_venta"]."|".$pv["nro_punto_venta"];?>"><?php echo str_pad($pv["nro_punto_venta"], 4,"0",STR_PAD_LEFT); ?></option>
                            <?php endif;?>
                        <?php endforeach;?>    
                        </select>
                    <?php else:?>
                    <div>Usuario sin punto de venta asigando, no puede continuar.</div>
                    <?php endif;?>
            </div>
            <p> Tipo de recibo </p>
            <span class="m-buttongroup">
                 
                <a href="javascript:void(0)" class="easyui-linkbutton" id="sistema" data-options="toggle:true,group:'g1',selected:true" onclick="hide1('#items')" style="width:80px;height:30px">Sistema</a> 
               <a href="javascript:void(0)" class="easyui-linkbutton" id="talonario" data-options="toggle:true,group:'g1'" onclick="show1('#items')" style="width:80px;height:30px">Talonario</a> 
                
            </span>
            
           <div id="items" style="padding:10px 10px;">
           
                
                <!--<div style="margin-left: 2%;margin-bottom:10px; float:left;width:99%">-->
                    <input class="easyui-numberbox" type="number" inputmode="numeric" pattern="[0-9]*"  id="nroTalonario" min="0" max="99999999" missingMessage="Debe completar numero de recibo" placeholder="nro recibo"  style="width:90%;" data-options="label: 'Número:'">
                <!--</div>-->
           
           
        </div>
            
        </div>
        
       
       
        <?php if(!empty($pvta)):?>
        <div style="text-align:center;padding:10px">
            <p><a href="#" class="easyui-linkbutton primaria" style="width:100%"  onclick="crear_recibo()"> Siguiente <i class="fas fa-chevron-right fa-fw fa-lg"></i></a></p>
        </div>
        <?php endif;?>
        
<!--        <footer>
            <div class="m-toolbar">
                <div class="m-title">Total. $0000</div>
            </div>
        </footer>-->
    </div>
<!--    <div class="easyui-navpanel" id="mCuenta">
        <header>
            <div class="m-toolbar">
                <div class="m-title" id="nroRecibo">
                </div>
            </div>
        </header>
        <div style="padding:20px" >
            <p>Cliente: <?php //echo $clienteObj->cliente;?></p>
            <p>3. Monto a cuenta</p>
            <div id="items" style="padding:10px 20px;">

                <div style="margin-bottom:10px">
                    <input class="easyui-numberbox" id="montoCuenta" min="0" decimalSeparator="," precision="2" required="true" missingMessage="Debe completar el monto a recibir"   prompt="monto efectivo " style="width:100%" label="Monto:"> 

                </div>
                <div style="text-align:center;padding:10px">
                    <p>
                        <a href="#" class="easyui-linkbutton" style="width:100%" onclick="guardar_monto($('#montoCuenta'))"> Guardar</a>
                        <a href="#" class="easyui-linkbutton" style="width:100%" onclick="guardar_monto($('#montoCuenta'))"> Borrar</a>
                    </p>
                </div>
                <div style="text-align:center;padding:10px">
                    <p><a href="#" class="easyui-linkbutton" disabled style="width:100%" onclick="ir('medioCobro')"> Ir medio cobro ></a></p>
                </div>
            </div>



        </div>    

    </div>    -->
   <div id="spinner" class="spinner" style="display:none;">
               <div class="centro">
                    <img src="_img/logo-administranet-ecommerce.png">   
                    <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
               </div>
            </div>    
</body>
 <script>
     $(document).ready(function() { 
         $('#items').hide();
    });
      
     // test de lo swift
        
//        swal({
//            title: "Good job!",
//            content: div,
//            icon: "success",
//            button: "Aww yiss!",
//          });
   
        function show1(id){
            //$('div.m-item').hide();
            $(id).show();
        }
        function hide1(id){
            $(id).hide();
        }
        
        function crear_recibo(){
            $('#spinner').show();
            var tipoRecibo="sistema";
            var nroPventaT="";
            var nroReciboT="";
            var idCliente=$('#idCliente').val();
            var saldoCliente=$('#saldoCliente').val();
            var idPcCliente=$('#idPcCliente').val();
            var error=0;
            // que hacer si es x sistema o talonario
            $('a.easyui-linkbutton').each(function(){
                var opts = $(this).linkbutton('options');
                
                if (opts.selected && opts.group=="g1"){
                        console.log(opts.text + ' selected');
                        if(opts.text=="Talonario"){
                            tipoRecibo="Talonario";
                            // validar que no puede quedar vacio el talionario y numero
                            var pv=$('#puntoVenta'),
                                nro=$('#nroTalonario');
                                if(pv.val()===''){
                                    //pv.numberbox('clear').numberbox('textbox').focus();
                                    error++;
                                    
                    
                                }else{
                                    nroPventaT=pv.combobox('getValue');
                                }
                                
                                if(nro.val()===''){
                                    nro.numberbox('clear').numberbox('textbox').focus();
                                    error++;
                                }else{
                                    nroReciboT=nro.val();
                                }
                                //console.log(pv.val()=='');
                                console.log(nro.val()=='');
                                    
                        }
                        if(opts.text=="Sistema"){
                            var pv=$('#puntoVenta');
                                
                                if(pv.val()===''){
                                    //pv.numberbox('clear').numberbox('textbox').focus();
                                    error++;
                                    
                    
                                }else{
                                    nroPventaT=pv.combobox('getValue');;
                                }
                        }
                }
                
            });
            
            // ver si es recibo a cuenta o imputacion
            var claseRecibo="";
            
            $('a.easyui-linkbutton.cRecibo').each(function(){
                var opts = $(this).linkbutton('options');
                
                if (opts.selected){
                    console.log($(this).prop('id'));
                    claseRecibo=$(this).prop('id');
                    
                }
            });
            // inicio del recibo consultar si esta seguro de seguir.
            
            if(error==0){
                /// preguntar si continua y ahi seguir
                var nomCliente=$('#nombreCliente').text();
                var mensajeFin="<p>Recibo para: <br> <strong>"+nomCliente+"</strong>";
                    mensajeFin +=" con saldo: <strong>$"+saldoCliente+"</strong>.</p>";
                var divMensaje=document.createElement('div');
                    divMensaje.innerHTML = mensajeFin;
                            
                swal({
                title: "¿Está seguro que desea continuar?",
                content: divMensaje,
                icon: "warning",
                 buttons: ["Cancelar", true],
                dangerMode: true
              })
              .then((willDelete) => {
                if (willDelete) {
                    console.log('acepte hacer el recibio---');
                    console.log("todo ok");
                    $.ajax({ 
                        type: 'GET', 
                        url: 'json_recibo.php', 
                        data: { 
                                altaRecibo: 1 ,
                                cliente:idCliente,
                                saldoCliente:saldoCliente,
                                idPcCliente:idPcCliente,
                                tipoNro:tipoRecibo, 
                                nroPv: nroPventaT,
                                nroRec:nroReciboT 
                        }, 
                        dataType: 'json',
                        beforeSend: function(){
                             $('#spinner').show();
                        },
                        success: function (data) { 
                                console.log(data);
                                console.log(claseRecibo);
                                 if(data.msg === "ok"){
                                     if(claseRecibo=="aCuenta"){
                                         $('#nroRecibo').text('Rec: '+data.numero);
                                         $.mobile.go('#mCuenta');
                                     }
                                     if(claseRecibo=="imputacion"){
                                         location.href="alta_recibo_imputacion.php";
                                     }
                                 }else{
                                     swal("Ooops!",data.desc,"warning");
                                 }
                                // si viene ok viene con numero paso a la opcion dos otres
                                // si soy a cuenta muestro el acuenta si soy imputacion
                                // voy a la nueva pagina con facturas. 
                                //VVV();
                                // $.each(data, function(index, element) {
                                //console.log(element.msg)
                                //     });
                                },
                        complete: function(){
                            $('#spinner').hide();
                        }
                    });
                }else{
                    $('#spinner').hide();
                } 
              });
                
                
            }
            if(error>0){
                console.log("todo mal");
                $('#spinner').hide();
            }
        }
        
    // no hya plata a cuenta hay que hacer medios de cobro 
    // directamente
    function salida(){
    $('#mGeneralEf').menu('hide', true);
         swal({
            title: "¿Está seguro que desea salir?",
            text: "Si acepta se eliminará el recibo en curso!",
            icon: "warning",
             buttons: ["Cancelar", true],
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
                console.log('dentro del willdelete');
                $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { salirRecibo:1}, 
                    dataType: 'json',
                    success: function (data) { 
                        console.log(data);

                         if(data.msg === "ok"){
                            console.log({data});
                            
                            location.href='listado-clientes.php';
                             

                         }
                         // error
                         if(data.msg==="error"){
                             // colocar en el titulo que no se pudo cargar el recibo.
                             // poner el mensaje de error y anular el boton de nuevo recibo
                             // pedir solo salir.
                            // alert('hubo un error');
                            location.href='listado-clientes.php';
                             
                             
                         }
                    }
            });
            } 
          });
      }
    
        
    
     
     
    </script>
</html>