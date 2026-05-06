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
//print_r($_SESSION["recibo"]);
if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}
//print_r($clienteObj);
$nroRecibo= $_SESSION["recibo"]["nroRecibo"];
$cuit = $clienteObj->CUIT;
//print_r($_SESSION["recibo"]["cheques"]["listado"]);
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Nuevo Recibo</title>  
    
    <!--<link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/color.css">-->
    <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/bootstrap/easyui.css"> 
    <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/mobile.css"> 
    
    <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/icon.css">  
   
<!--     <link rel="stylesheet" type="text/css" href="_css/main_styles.css">-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous"> 
    <script type="text/javascript" src="_lib/easyui/jquery.min.js"></script>  
    <script type="text/javascript" src="_lib/easyui/jquery.easyui.min.js"></script> 
    <script type="text/javascript" src="_lib/easyui/jquery.easyui.mobile.js"></script> 
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    
    <!--Estilos para el exito y fracaso final despues incorporar al estilo y botones de easyui y hablar con los diseñadores.-->
    <style>
        /*estilos para los mensajes de exito
        =======================================================================
        */
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
     <!--PANEL EFECTIVO-->   
    <div class="easyui-navpanel" id="panelMediosCobro">
        <header>
               <div class="m-toolbar">
                   <div class="m-title">Medios de Cobro</div>
                   <div class="m-left">
                       
                       <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralEf',menuAlign:'left'"><i class="fas fa-bars"></i></a>


                    </div>
               </div>
            <div class="m-toolbar">
                <span class="m-buttongroup">
                 <a href="javascript:void(0)" class="easyui-linkbutton" id="efectivo" data-options="toggle:true,group:'g1',selected:true" onclick="mostrar_panel_efectivo()" style="width:80px;height:30px">Efectivo</a> 
                <a href="javascript:void(0)" class="easyui-linkbutton" id="dolar" data-options="toggle:true,group:'g1'" onclick="mostrar_panel_dolar()" style="width:120px;height:30px">Efectivo U$S</a> 
                <a href="javascript:void(0)" class="easyui-linkbutton" id="cheque" data-options="toggle:true,group:'g1'" onclick="mostrar_panel_cheques();" style="width:80px;height:30px">Cheques</a> 
               
                
            </span>
            <div style="display:none;">   
            <select class="easyui-combobox" name="listaCajaEfectivo" id="listaCajaEfectivo" label="Caja:"  data-options=" valueField: 'id',
                        textField: 'text',prompt:'Caja...',readonly:true" style="width:100%;">                            
            </select>  
             <select class="easyui-combobox" name="listaCajaCheque" id="listaCajaCheque"   data-options=" valueField: 'id',
                        textField: 'text',prompt:'tipo de retencion...',label:'Caja:',readonly:true" style="width:100%;">
            </select>     
                </div>
                       
           </header>
         <div id="mGeneralEf" class="easyui-menu" style="width:200px;" data-options="itemHeight:30,noline:true">
            <div class="menu-sep"></div>
            <div> <i class="fas fa-user-circle fa-lg fa-fw"></i><?php echo $clienteObj->cliente; ?></div>
            <div class="menu-sep"></div>
             <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="salida();"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
        </div>
        
         <!--CUERPO EFECTIVO PESOS-->
         <!--================================================================-->
        <div style="padding:5px" id="panelCuerpoEfectivo">
                 <div class="m-toolbar">
                    <div class="m-title" style="text-align:left;">Efectivo pesos</div>                    
             </div>     
             
            <div style="padding:5px" >
                <input class="easyui-textbox" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" id="efectivoCobro" min="0"  decimalSeparator="," groupSeparator="." precision="2"  label="Efectivo $:"   style="width:60%" >
                <a href="javascript:void(0)" class="easyui-linkbutton" id="boton-ok-efectivo" onclick="valida_pesos();"  >Aceptar</a>
            </div>    
           
                         <div style="padding:5px" >
               
                <input class="easyui-numberbox" id="efectivoTotalCobro" min="0" decimalSeparator=","  groupSeparator="." precision="2" prefix="$" label="Total:" readonly="true"  prompt="$ total efectivo"  style="width:100%" >
                
            </div>
            <div style="padding:5px;display: none;" >
                
            </div>
            <div style="padding:5px;text-align: center;" >
                
                 <a href="javascript:void(0)" class="easyui-linkbutton" id="efectivo-cancel" data-options="plain:true,disabled:true" onclick="borrar_efectivo('#efectivoCobro','pesos')"><i class="fas fa-trash fa-lg fa-fw"></i> Deshacer </a>
                  <a href="javascript:void(0)" class="easyui-linkbutton" id="efectivo-ok"  onclick="acepta_efectivo($(this),'pesos')" data-options="disabled:true"><i class="fas fa-check fa-lg fa-fw"></i> Confirmar</a>
            </div>
        </div>
        <!--<FIN> CUERPO EFECTIVO PESOS-->
        
        <!--CUERPO EFECTIVO DOLARES--> 
        <!--=================================================================-->
        <div id="panelCuerpoDolar" style="padding:5px">
             <div class="m-toolbar">
                    <div class="m-title" style="text-align:left;">Moneda extranjera</div>                    
             </div>
            <div style="padding:3px" >    
                <input class="easyui-textbox" id="dolarCobro" min="0" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" label="U$S:"  decimalSeparator="," groupSeparator="." precision="2"  style="width:60%" >
                
<!--            </div>
             <div style="padding:5px" >    -->
                <input class="easyui-numberbox" id="cotiDolarCobro" min="0" decimalSeparator=","  groupSeparator="." precision="2" prefix="Cot: "    prompt="1" style="width:22%" >
                <a href="javascript:void(0)" class="easyui-linkbutton" id="boton-calcula-dolar"  onclick="dolar_a_peso();">Calcular</a>
            </div>    
            <div style="padding:5px" >    
                <input class="easyui-numberbox" id="dolarApeso" min="0" decimalSeparator=","  groupSeparator="." precision="2"  readonly="true" label="u$s a $:"   prompt="dolar a pesos" style="width:70%" >
                <a href="javascript:void(0)" class="easyui-linkbutton" id="boton-ok-dolar" disabled="true"  onclick="valida_dolar();">Aceptar</a>
                
            </div>
             <div style="padding:5px" >
               
                <input class="easyui-numberbox" id="dolarTotalCobro" min="0" decimalSeparator=","  groupSeparator="." precision="2" prefix="$" label="Efectivo:" readonly="true"  prompt="total efectivo $ "  style="width:100%" >
                
            </div>
           
            <div style="padding:5px;text-align: center;" >
                
                 <a href="javascript:void(0)" class="easyui-linkbutton" id="dolar-cancel" data-options="plain:true,disabled:true" onclick="borrar_efectivo('#dolarCobro','dolar')"><i class="fas fa-trash fa-lg fa-fw"></i> Deshacer</a>
                  <a href="javascript:void(0)" class="easyui-linkbutton" id="dolar-ok"  onclick="acepta_efectivo($(this),'dolar')" disabled="true"><i class="fas fa-check fa-lg fa-fw"></i> Confirmar</a>
            </div>
        </div> 
        <!--FIN CUERPO EFECTIVO DOLAR-->
        
        <!--CUERPO CHEQUES-->
        <!--=================================================================-->
        <div id="panelCuerpoCheque" style="padding:5px">

           

                <div class="m-toolbar">
                    <div class="m-title" style="text-align:left;">Cheques</div>
                    <div class="m-right">
                        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="mostrar_panel_alta_cheque();"><i class="fas fa-plus fa-lg fa-fw"></i>Nuevo </a>
                        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="borrar_cheque();"><i class="fas fa-trash fa-lg fa-fw"></i>Borrar </a>


                    </div>
                </div>
            
            <table id="tblCheques"></table>

        </div>   
        <!--FIN CUERPO CHEQUES-->    
        
        <footer style="margin-top: 5px;">
           
                <div style="padding: 5px;margin-bottom: 5px;">
                    <input class="easyui-numberbox" id="totalRecibo" min="0" decimalSeparator=","  groupSeparator="." precision="2"  data-options="readonly:true" prefix="$"  value="0"  prompt="" style="width:49%" label="A cubrir:">
                    <input class="easyui-numberbox" id="totalEfectivo" min="0" decimalSeparator=","  groupSeparator="." precision="2"  data-options="readonly:true" prefix="$"   prompt="0" style="width:49%" label="Efectivo:">
                </div>
                    <div style="padding:5px;margin-bottom: 5px;">
                    <input class="easyui-numberbox" id="totalCheque" min="0" decimalSeparator=","  groupSeparator="." precision="2"  data-options="readonly:true" prefix="$"  prompt="0" style="width:49%" label="Cheques:">    
                    <input class="easyui-numberbox" id="totalSaldo" min="0" decimalSeparator=","  groupSeparator="." precision="2"  data-options="readonly:true" prefix="$"  prompt="0" style="width:49%" label="Saldo:">
                    
                </div>
            <div style="padding:10px;">
                <!--<a href="javascript:void(0)" class="easyui-linkbutton" id="botonCheque" onclick="mostrar_panel_cheques();" style="width:47%" >Cheques</a>-->
                <a href="javascript:void(0)" class="easyui-linkbutton" id="botonResumen" data-options="disabled:true" style="width:100%" onclick="trae_resumen_recibo();">Siguiente <i class="fas fa-angle-right fa-fw fa-lg"></i></a>
            </div>
           
        </footer>
     </div> 
    <!--FIN PANEL PRINCIPAL-->  
    
    <!--PANEL ALTA DE CHEQUE--> 
        
        <div class="easyui-navpanel" id="panelAltaCheque">
        <header>
               <div class="m-toolbar">
                   <div class="m-title">Nuevo Cheque Tercero</div>
                    <div class="m-left">
                       
                       <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralCh',menuAlign:'left'"><i class="fas fa-bars"></i></a>


                    </div>

               </div>
            
           </header>
         <div id="mGeneralCh" class="easyui-menu"  style="width:200px;" data-options="itemHeight:30,noline:true">
             <div></div>
             <div>Recibo: <?php echo $nroRecibo;?></div>
            <div class="menu-sep"></div>
            <div><i class="fas fa-user-circle fa-lg fa-fw"></i> <?php echo $clienteObj->cliente; ?></div>
            <div class="menu-sep"></div>
             <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" style="width: 100%;text-align: left;" onclick="salida();"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
            
        </div>    
        
           <div style="padding:10px">
                <input class="easyui-numberbox" type="number" inputmode="numeric" pattern="[0-9]*" id="chNumero" min="0" required="true" missingMessage="Debe completar nro chque"    prompt="nro cheque" style="width:80%" label="Nro:"> 
            </div>   
        <div style="padding:10px"> <label for="m-buttongroup">Tipo:</label>
            <span class="m-buttongroup" style="padding-left:16%;">
                 
                <a href="javascript:void(0)" class="easyui-linkbutton" id="Normal" data-options="toggle:true,group:'gCheque',selected:true"  style="width:80px;height:30px">Normal</a> 
               <a href="javascript:void(0)" class="easyui-linkbutton" id="Electronico" data-options="toggle:true,group:'gCheque'" style="width:80px;height:30px">Electrónico</a> 
                
            </span>       
         </div>    
        <div style="padding:10px">
                <input class="easyui-textbox" id="chImporte" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" min="0" decimalSeparator="," precision="2"  groupSeparator="." required="true" missingMessage="valor cheque" prefix='$'   prompt="$ importe" style="width:60%" label="Importe:"> 
           <label for="saldoAltaCheque">A cubrir: <span id="saldoAltaCheque" style="padding-left:10px;"></span></label> 
        </div>    
        
        <div style="padding:10px">
             <select class="easyui-combobox" name="listaBancos" id="listaBancos"   data-options=" valueField: 'id',
                    textField: 'text',prompt:'seleccionar un banco',label:'Banco:'" style="width:100%;"></select>                    
               
            </div> 
           <div style="padding-top: 5px;padding-left:10px;">
                <!--<input class="easyui-textbox" id="chBancoCuit" data-options="readonly:true" style="width:70%" label="Bco CUIT:">--> 
                <input class="easyui-maskedbox" id="chBancoCuit" data-options="readonly:true" promptChar="#" mask="99-99999999-9" label="Bco CUIT:"  style="width:100%">
            </div>    
            <div style="padding:1px">
                
                <div style="padding:10px;float:left;width:49%">
                    <input class="easyui-datebox" id="chFechaEmision" required="true"   data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30,label:'Emision:',labelPosition:'top'" style="width:100%">
                </div>
                <div style="padding:10px;float:left;width:49%">
                    <input class="easyui-datebox" id="chFechaCobro" required="true"  data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30,label:'Cobro:',labelPosition:'top'" style="width:100%">
                </div>
            </div>
         
      <div style="padding:5px">
          <input class="easyui-textbox" label="Librador:" id="chLibrador" prompt="librador" value="<?php echo  $clienteObj->cliente;?>" style="width:100%">
            </div>
        <div style="padding:5px">
                <!--<input class="easyui-textbox" label="CUIT:" id="chCuit" prompt="Nro cheque" style="width:100%">-->
            <input class="easyui-maskedbox" id="chCuitLibrador" mask="99-99999999-9"  promptChar="#" value="<?php echo $cuit;?>" label="CUIT:" style="width:100%">
            </div>         
            
         <footer>
           
                
            <div style="padding:10px;text-align: center">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="alta_cheque();" style="width:45%" id="botonGuardarCheque"><i class="fas fa-check fa-fw fa-lg"></i> Aceptar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$.mobile.go('#panelMediosCobro','fade','left');mostrar_panel_cheques();" style="width:45%"><i class="fas fa-times fa-fw fa-lg"></i> Cancelar</a>
            </div>
           
        </footer>
          
            
        </div>
        
    <!-- fin PANEL DE ALTA DE CHEQUE TERCERO -->
    
    <!-- PANEL RESUMEN RECIBO -->
    <!-- ====================================================================-->
    <div class="easyui-navpanel" id="panelResumen">   
        <header>
               <div class="m-toolbar">
                   <div class="m-title">Resumen </div>
                    <div class="m-left">
                       
                       <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralCh',menuAlign:'left'"><i class="fas fa-bars"></i></a>


                    </div>

               </div>
            
           </header>
         <div id="mGeneralCh" class="easyui-menu"  style="width:200px;" data-options="itemHeight:30,noline:true">
             <div>Recibo</div>
             <div><?php echo $nroRecibo;?></div>
            <div class="menu-sep"></div>
            <div><i class="fas fa-user-circle fa-lg fa-fw"></i> <?php echo $clienteObj->cliente; ?></div>
            <div class="menu-sep"></div>
             <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="salida();"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
            
        </div>
          
           
         <div style="padding:5px" >
             <table id="tblResumenRecibo"></table>
             <div id="himputacion">
                 <div class="m-toolbar">
                     <div class="m-title">Imputaciones</div>
                 </div>
             </div>
            <table id="tblResumenImputacion" data-options="header:'#himputacion'"></table>
            
            <div id="hmedios">
                <div class="m-toolbar">
                    <div class="m-title">Medios de cobro</div>
                </div>
            </div>
            <table id="tblResumenMedios" data-options="header:'#hmedios'"></table>
         </div>    
         <footer>
           
                
            <div style="margin-bottom:5px">
                <!--<a href="javascript:void(0)" class="easyui-linkbutton" onclick="location.href='alta_recibo_medios_cobro.php'" style="width:100%"><i class="fas fa-angle-right fa-fw fa-lg"></i>Siguiente</a>-->
                <a href="javascript:void(0)" id="botonResumenFin" class="easyui-linkbutton primaria" onclick="guardar_recibo();" style="width:100%"><i class="fas fa-angle-right fa-fw fa-lg"></i> Finalizar</a>
                <div>
                <a href="javascript:void(0)" class="easyui-linkbutton" id="botonCheque" onclick="$.mobile.go('#panelMediosCobro','fade','left');mostrar_panel_cheques();" style="width:47%" ><i class="fas fa-chevron-left"></i> Cheques</a>
                 <a href="javascript:void(0)" class="easyui-linkbutton" id="botonEfectivo" onclick="$.mobile.go('#panelMediosCobro','fade','left');mostrar_panel_efectivo();" style="width:47%" ><i class="fas fa-chevron-left"></i> Efectivo</a>
                 </div>
                
            </div>
           
        </footer>
        </div>
    
    
    <!-- == FIN PANEL RESUMEN RECIBO -->  
    
    
   
    
    <!--== PANEL ENVIO EMAIL == -->
    
    
    
    
    <!--== FIN PANEL ENVIO == -->    
    <!--DIALGO DE MENSAJES--> 
        <div id="dlgMensaje" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Información'">
            <p id="mensajeDialog" style="text-align: center;">This is a message dialog.</p>
            <div class="dialog-button">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensaje').dialog('close');">OK</a>
            </div>
        </div>
    <!--FIN DIALOGO MENSAJES-->   
   
    <!--DIALGO DE CONFIRMACION ACEPTAR Y CANCELAR --> 
        <div id="dlgMensajeOption" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Confirmación'">
            <p id="mensajeDialogOption" style="text-align: center;">This is a message dialog.</p>
<!--            <div class="dialog-button">
                <a href="javascript:void(0)" id="aceptar" class="easyui-linkbutton" style="width:100%;height:35px" ><i class="fas fa-check"></i> Aceptar</a>
                <a href="javascript:void(0)" id="cancelar" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensajeOption').dialog('close');"><i class="fas fa-times"></i> Cancelar  </a>
            </div>-->
        </div>
    <!--FIN DIALOGO MENSAJES-->   
      <div id="spinner" class="spinner" style="display:block;">
               <div class="centro">
                    <img src="_img/logo-administranet-ecommerce.png">   
                    <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
               </div>
            </div>    
        
</body>
 <script>
     
     const numerito= new Intl.NumberFormat('es-AR',{
            style: 'decimal',                    
            minimumFractionDigits: 2
        }); 
        
        const dinero = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    minimumFractionDigits: 2
                   
                  });
     
    $.fn.datebox.defaults.formatter = function(date) {
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    var d = date.getDate();
    return (d < 10 ? '0' + d : d) + '/' + (m < 10 ? '0' + m : m) + '/' + y;
    };

    $.fn.datebox.defaults.parser = function(s) {
    if (s) {
    var a = s.split('/');
    var d = new Number(a[0]);
    var m = new Number(a[1]);
    var y = new Number(a[2]);
    var dd = new Date(y, m-1, d);
    return dd;
    } else {
    return new Date();
    }
};



    // al cambiar el porcentaje
    // variable que puedo guardar el efectivo y el dolar. si ya no queda plata
    // no deberia poder cargar cheques.
    var efectivoOK=1,chequeOK=1;
    function iniciar(){
//        traer_descuentos();
//        trae_tipo_retencion_cli();
//        trae_retenciones(); 
//        trae_totales();
        
        $('#spinner').show();
        $('#panelCuerpoCheque').hide('fast');
        $('#panelCuerpoDolar').hide('fast');
        $('#panelCuerpoEfectivo').show('fast');
        trae_coti_dolar(); 
        trae_caja_cheque();
        trae_caja_efectivo();
        trae_totales();
        trae_bancos();
        $('#spinner').hide();
    }
    
    
    // EFECTIVO
    // =========================================================================
    function mostrar_panel_efectivo(){
        
        //$.mobile.go('#panelEfectivo','slide','right');
        
        $('#panelCuerpoCheque').hide('fast');
        $('#panelCuerpoDolar').hide('fast');
        $('#panelCuerpoEfectivo').show('fast');
        //trae_totales_efectivo();
        
    
    }
    
    function mostrar_panel_dolar(){        
        $('#panelCuerpoCheque').hide('fast');        
        $('#panelCuerpoEfectivo').hide('fast');
        $('#panelCuerpoDolar').show('fast');
    }
    
    
    
    
    function acepta_efectivo(quien,moneda){
       var pesos,dolar,subtotal,subtotalPesos,subtotalDolar,coti,caja;
       pesos    =$('#efectivoCobro');
       dolar    =$('#dolarCobro');
       coti     =$('#cotiDolarCobro');
       subtotalPesos =$('#efectivoTotalCobro');
       subtotalDolar=$('#dolarTotalCobro');
       caja     =$('#listaCajaEfectivo');
       
       subtotal_efectivo(quien);
       // subtotal suma dolar y pesos
       
       subtotal = parseFloat(subtotalPesos.numberbox('getValue')) + parseFloat(subtotalDolar.numberbox('getValue'));
       
       //console.log("subtotal que valor tiene??:"+parseFloat(subtotal.numberbox('getValue')));
       if(subtotal===0){
           swal("advertencia","No ha ingresado Efectivo ni moneda extranjera","warning");
          // pesos.textbox().focus();
           return false;
       }
       var pesosVal=0,dolarVal=0;
       // valor de pesos
       if(!isNaN(pesos.numberbox('getValue'))){
          pesosVal=pesos.numberbox('getValue');
       }
       
       // valor del dolar
       console.log(dolar.numberbox('getValue'));
       if(dolar.numberbox('getValue')!==""){
           dolarVal=dolar.numberbox('getValue');
       }
       console.log("dolarVal"+dolarVal);
       if(efectivoOK===0){
            $.ajax({ 
                        type: 'GET', 
                        url: 'json_recibo.php', 
                        data: { altaEfectivo:1,
                            idcaja:caja.combobox('getValue'),
                            pesos:pesosVal,
                            dolar:dolarVal,
                            coti:coti.numberbox('getValue'),
                            subtotal:subtotal}, 
                        dataType: 'json',
                        beforeSend: function(){
                            $('#spinner').show('fast');
                        },
                        success: function (data) { 
                            console.log(data);

                            if(data.msg === "ok"){
                                     // recalcular 
                                if (moneda==="pesos"){
                                    
                                    swal("Hecho!","Efectivo confirmado","success");
                                     
                                    $('#efectivo-cancel').linkbutton('enable');
                                    $('#efectivo-ok').linkbutton('disable');
                                    $('#boton-ok-efectivo').linkbutton('disable');
                                    pesos.numberbox('disable');
                                    //quien.linkbutton('disable');
                                    subtotalPesos.numberbox('disable');
                                }
                                
                                if (moneda==="dolar"){
                                    swal("Hecho!","Efectivo Moneda extranjera confirmado","success");
                                    $('#dolar-cancel').linkbutton('enable');
                                    $('#dolar-ok').linkbutton('disable');
                                    $('#boton-calcula-dolar').linkbutton('disable');
                                    $('#boton-ok-dolar').linkbutton('disable');
                                    dolar.numberbox('disable');
                                    coti.numberbox('disable');
                                    subtotalDolar.numberbox('disable');
                                }
                                trae_totales();
                            }else{
                                     // error 
                                swal("Oops!","Hubo un inconveniente con el efectivo , vuelva a intentar","error");
                            }

                        },
                        complete: function(){
                            $('#spinner').hide('fast');
                        }
            });
        }
        else{
            //$('#mensajeDialog').text("Hubo un inconveniente con el efectivo, Corrija los montos.");
            swal("Oops!","Hubo un inconveniente con el efectivo, Corrija los montos","error");
            //$('#dlgMensaje').dialog('open').dialog('center');
        }
    }
    
    function dolar_a_peso(){
        var dolar=$('#dolarCobro').numberbox('getValue');
        console.log("dolar"+dolar);
        var coti;
        coti=$('#cotiDolarCobro').numberbox('getValue');
        console.log("cotizacion"+coti);
        var pesos = dolar*coti;
        console.log("pesos dolar"+pesos);
        //$('#dolarApeso').numberbox('setValue',pesos);
        if(pesos!==0&&pesos!=""){
            $('#dolarApeso').numberbox('setValue',pesos);
            $('#boton-ok-dolar').linkbutton('enable');
        }
        
   }
   
   
   
   function valida_dolar(){
       
       var miDolar=$('#dolarApeso').numberbox('getValue');
       if(miDolar!==""&&miDolar!==0){
           subtotal_efectivo($('#dolar-ok'));
       }
   }
   
    
    function valida_pesos(){
        var pesos= $('#efectivoCobro').numberbox('getValue');
        if(pesos!==""&&pesos!=0){
            console.log("valida pesos:"+pesos);
            subtotal_efectivo($('#efectivo-ok'));
        }
    }
    
    function subtotal_efectivo(quien){
        $('#spinner').show('fast');
        // controlo que no me pase de lo que tengo en el saldo.
        var pesos,dolar, subtotal,totalRecibo;           
        var subtotalPesos=$('#efectivoTotalCobro');
        var subtotalDolar=$('#dolarTotalCobro');
            pesos=$('#efectivoCobro').numberbox('getValue');
            dolar=$('#dolarApeso').numberbox('getValue');
            totalRecibo=$('#totalRecibo').numberbox('getValue');
            
        console.log('pesos::'+pesos+' dolar::'+dolar+' totalRc::'+totalRecibo);    
        console.log(totalRecibo);
        console.log(pesos);
        console.log(dolar);
        if(isNaN(dolar)||dolar===""){
            console.log("el dolar es un texto");
            dolar=0;
        }
        if(isNaN(pesos)||pesos===""){
            console.log("el peso es un texto");
            pesos=0;
        }
        console.log('pfloatRecibo=>'+parseFloat(totalRecibo));
        console.log('pfloatpesos=>'+parseFloat(pesos));
        console.log('pfloatdolar=>'+parseFloat(dolar));
        subtotal=parseFloat(totalRecibo)-(parseFloat(pesos)+ parseFloat(dolar));  
                
         console.log("subTotal::"+subtotal);   
        // valor negativo tengo plata de mas.
        if(subtotal<0){
            // todo mal hay plata de mas
            // EVALUAR con el sweetModal si acepta o si no en caso negativo lo vuelve a corregir en caso positivo sigue viaje..
            // completar el monto efectivo con lo que ya hay en el total.
            
            efectivoOK=0;
            swal("advertencia","El monto efectivo supera el total del recibo.<br>Se generara un Recibo a cuenta por la diferencia $"+subtotal+" recibo:: "+parseFloat(totalRecibo)+"-( pesos: "+parseFloat(pesos)+" + dolar: "+parseFloat(dolar)+")","warning")
//            $('#mensajeDialog').html("El monto efectivo supera el total del recibo.<br>Se generara un Recibo a cuenta por la diferencia"+subtotal+"recibo:: "+parseFloat(totalRecibo)+"-( pesos: "+parseFloat(pesos)+" + dolar: "+parseFloat(dolar)+")");
//            $('#dlgMensaje').dialog('open').dialog('center');
            subtotalPesos.numberbox('setValue',parseFloat(pesos));
            subtotalDolar.numberbox('setValue',parseFloat(dolar));
            //$('#efetivoCobro').numberbox().focus();
//            $('#botonCheque').linkbutton('enable');
            $('#spinner').hide('fast');
                
        }
        // valor positivo todo cool
        if(subtotal>0){
            // todo ok
            console.log("Debo guardar los dolares");
            console.log(pesos);
            console.log(dolar);
            console.log(parseFloat(pesos)+ parseFloat(dolar));
            subtotalPesos.numberbox('setValue',parseFloat(pesos));
            subtotalDolar.numberbox('setValue',parseFloat(dolar));
            efectivoOK=0;
//            $('#botonCheque').linkbutton('enable');
             $('#spinner').hide('fast');
        }
        
        if(subtotal===0){
//            $('#mensajeDialog').html("El monto efectivo es igual al Recibo<br> si carga cheques seran ingresados con <b>recibo a cuenta</b>");
//            $('#dlgMensaje').dialog('open').dialog('center');
            swal("información","El monto efectivo es igual al Recibo. \nSi carga cheques seran ingresados con RECIBO A CUENTA","warning");
            //$('#botonCheque').linkbutton('disable');
            $('#efectivoTotalCobro').numberbox('setValue',parseFloat(pesos)+ parseFloat(dolar));
            efectivoOK=0;
             $('#spinner').hide('fast');
            
        }
        
        console.log("como quedamos?::"+efectivoOK);
        if(efectivoOK===0){
            // habilito el boton confirmar.
           quien.linkbutton('enable');
//           $('#dolar-ok').linkbutton('enable');
           $('#spinner').hide('fast');
        }    
    }
    
    
    // borrar el efectivo.
    function borrar_efectivo(subtotalN,tipo){
       var subtotal=$(subtotalN);
//       console.log("a quien voy a bloquear=?");
//       console.log(quien);
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { bajaEfectivo:1,tipo:tipo}, 
                    dataType: 'json',
                    
                    beforeSend: function(){
                          $('#spinner').show('fast');
                    },
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 // recalcular 
                                 if(tipo==='dolar'){
                                    console.log("pongo total dolares en cero")
                                    $('#dolarTotalCobro').numberbox('setValue',0);
                                    $('#dolar-cancel').linkbutton('disable');
                                    $('#boton-ok-dolar').linkbutton('disable');
                                    $('#boton-calcula-dolar').linkbutton('enable');
                                 }else{
                                     console.log("pongo pesos en cero ");
                                     $('#efectivoTotalCobro').numberbox('setValue',0);
                                     $('#efectivo-cancel').linkbutton('disable');
                                     $('#boton-ok-efectivo').linkbutton('enable');
                                 }
                                 //console.log("bloqueando el boton del quien");
                                
                                 trae_totales();
                                 
                                subtotal.numberbox('enable');
                                subtotal.numberbox('textbox').focus();
                             }else{
                                 // error 
//                                 $('#mensajeDialog').text("hubo un inconveniente eliminacion del efectivo, vuelva a intentar.");
//                                 $('#dlgMensaje').dialog('open').dialog('center');
                                 swal("Oops!","hubo un inconveniente eliminacion del efectivo, vuelva a intentar.","error");
                                 //$('#listaDescuento').combobox('focus');
                             }
                            
                    },
                    complete: function(){
                          $('#spinner').hide('fast');
                    }
                });
        
    }
    
        function show1(id){
            //$('div.m-item').hide();
            $(id).show();
        }
        function hide1(id){
            $(id).hide();
        }
        
        
        
        
        function fechaReves(s){
                console.log("dentro de la fecha la reves.");
                console.log(s);
               var ss = s.split('/');
		var y = parseInt(ss[2],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[0],10);
                console.log("vuelta " +y+'-'+m+'-'+d);
                return y+'-'+m+'-'+d;
                
            } 
            
        function trae_caja_efectivo(){
            // solo los presento y si hay movimientos 
            var selEfectivo=$('#listaCajaEfectivo');
            
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeCajaEfectivo:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 var opt=data.caja;
                                 selEfectivo.combobox({data:opt});
                                 var opts = selEfectivo.combobox('options');
                                 console.log(opts);
                               //selEfectivo.combobox('select', items[0][opts.valueField]);
                                 
                             }else{
                                 // error 
                             }
                            
                            }
                });
        }
        function trae_caja_cheque(){
            // solo los presento y si hay movimientos 
            var selCheque=$('#listaCajaCheque');
            
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeCajaCheque:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 var opt=data.caja;
                                 selCheque.combobox({data:opt});
                                 
                             }else{
                                 // error 
                             }
                            
                            }
                });
        }
        
        
        function trae_coti_dolar(){
            var coti=$('#cotiDolarCobro');
            $.ajax({
                type: 'GET',
                url:    'json_recibo.php',
                data: {traeCotiDolar:1},
                success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 coti.numberbox('setValue',data.cotizacion);
                                 
                             }else{
                                 // error 
                                 coti.numberbox('setValue',1);
                             }
                            
                            }
            });
        }
        
        
     var indiceTabla=undefined;   
    
    // CHEQUES
    //=========================================================================
    
    
    
    
    
    // alta de cheque de tercero.
    function mostrar_panel_cheques(){
//        $.mobile.go('#panelCheque','slide','right');
        trae_lista_cheques();     
        $('#panelCuerpoEfectivo').hide('fast');
        $('#panelCuerpoDolar').hide('fast');        
       
         $('#panelCuerpoCheque').show('fast');   
        
    }
    
    function mostrar_panel_alta_cheque(){
        var saldoCh = $('#totalSaldo').numberbox('getValue');
        $('#saldoAltaCheque').html('<strong>$'+numerito.format(saldoCh)+'</strong>');
        $.mobile.go('#panelAltaCheque','slide','left');
        $('#chNumero').numberbox('clear').numberbox('textbox').focus();
    }
    
    
    // traer los Bancos 
    function trae_bancos(){
            // solo los presento y si hay movimientos 
            var bancos=$('#listaBancos');
            
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeBancos:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 var opt=data.banco;
                                 bancos.combobox({data:opt});
                                 
                             }else{
                                 // error 
                             }
                            
                            }
                });
        }
        
    // bancos    
    $('#listaBancos').combobox({
	onSelect: function(row){
		console.log("dentro del banco");
                console.log(row);
                var cuitBanco=$('#chBancoCuit');
                cuitBanco.maskedbox('setValue',row.cuit);
                var librador=$('#chLibrador');
//                librador.textbox('textbox').focus();
                $('#chFechaEmision').datebox('textbox').focus();
                // obtener el campo del monto y total del recibo.
            }
    });
    
    
    // alta de cheque
    function alta_cheque(){
        var boton=$('#botonGuardarCheque');
        
        var banco,codbanco,cuitbanco,
            librador,cuitlibrador,importe,numero,
            emision,vencimiento,cobro,tipoCheque;
    
        var limiteValor=$('#totalSaldo').numberbox('getValue');
        var mensaje="debe completar todos los datos del cheque."; 
        console.log('-----q opciones de tipo de cheque');
        $('a.easyui-linkbutton').each(function(){
                var opts = $(this).linkbutton('options');
                
                if (opts.selected && opts.group==="gCheque"){
                    console.log({opts});
                        console.log(opts.text + ' selected');
                        tipoCheque=opts.id;
                }
                
            });
        console.log({tipoCheque});
        
        //console.log([$('.easyui-linkbutton')]);
       // return false;
        // desabilito el boton
        boton.linkbutton('disable');
        boton.linkbutton({'text':'<i class="fas fa-circle-notch fa-spin"></i>...'});
        //boton.unbind('click');
//        boton.linkbutton('disabled');
         //$('#spinner').show();
        
        
        banco=$('#listaBancos').combobox('getText');
        codbanco=$('#listaBancos').combobox('getValue');
        cuitbanco=$('#chBancoCuit').maskedbox('getText');
        
        librador=$('#chLibrador');
        cuitlibrador=$('#chCuitLibrador');
        importe=$('#chImporte');
        numero=$('#chNumero');
        emision=fechaReves($('#chFechaEmision').datebox('getValue'));
        //console.log("fecha cobro;"+$('#chFechaCobro').datebox('getValue'));
        
        //vencimiento=fechaReves();
        cobro=fechaReves($('#chFechaCobro').datebox('getValue'));
        // controlar los vacios y los que son requeridos.
        
        console.log(cuitlibrador.maskedbox('getText'));
        console.log(cuitlibrador.maskedbox('getValue'));
        var errores =0;
        if(codbanco===""){
            errores++;
        }
        if(librador.textbox('getValue')===""){
            errores++;
        }
        if(cuitlibrador.maskedbox('getText')===""){
            errores++;
        }
        if(numero.numberbox('getValue')===""){
            errores++;
        }
        if(importe.numberbox('getValue')===""){
            errores++;
        }
        if(emision===""){
            errores++;
        }
        
        if(cobro===""){
            errores++;
        }
//        if(importe>limiteValor){
            //errores++;
//            mensaje="El valor del cheque no puede superar el saldo: <b>"+limiteValor+"</b>";
//        }
        
        if(errores>0){
            // hay campos vacios
            //$('#spinner').hide();
//             $('#mensajeDialog').text(mensaje);
//             $('#dlgMensaje').dialog('open').dialog('center');
             swal("advertencia!","Debe completar todos los campos","warning");
        }
        else{
            // mandar el ajax
            $.ajax({ 
                type: 'GET', 
                url: 'json_recibo.php', 
                data: { altaCheque: 1, 
                        codbanco:codbanco, 
                        banco:banco, 
                        cuitbanco:cuitbanco,
                        librador:librador.textbox('getValue'),
                        cuitlibrador: cuitlibrador.maskedbox('getText'),
                        numero:numero.numberbox('getValue'),
                        importe:importe.numberbox('getValue'),
                        emison:emision,                        
                        cobro:cobro,
                        tipo: tipoCheque

                    }, 
                dataType: 'json',
                beforeSend: function(){
                    $('#spinner').show();
                },
                success: function (data) {
                    
                        console.log(data);
                         if(data.msg === "ok"){
                             // hacer algo con el row.
                            //  reseteo los valores.
                            $('#listaBancos').combobox('clear');
                            $('#chBancoCuit').maskedbox('clear');
                            
//                            $('#chImporte').numberbox('clear');
//                            $('#chNumero').numberbox('clear');
                            $('#chImporte').textbox('clear');
                            $('#chNumero').textbox('clear');
                            $('#chFechaEmision').datebox('clear');
                           
                            $('#chFechaCobro').datebox('clear');  
                                                                   
                                                                                                                                             
                             // llamar arecalculo del recibo 
                             // y marcar la factura de alguna forma como lista.
                             // aviso que todo bien
                              //$('#mensajeDialog').text("Se imputo $"+monto+ " a factura: "+row.item+"" );
                              //$('#dlgMensaje').dialog('open').dialog('center');
                              
                              trae_lista_cheques();
                              $.mobile.go('#panelMediosCobro','slide','left');
                              mostrar_panel_cheques();
                              //$('#spinner').hide();
                        }
                },
                complete: function(e){
                    boton.linkbutton({'text':'<i class="fas fa-check fa-fw fa-lg"></i>Aceptar'});
                    boton.linkbutton('enable');
                    $('#spinner').hide();
                }
            });
        }
    }
    
    // funcion para eliminar un cheque
    function borrar_cheque(){
        // abrir la dialog que se lo asegura.
        //$('#spinner').show();
      
        var select = $('#tblCheques').datagrid('getSelected');
        if(select===null){
            swal("Advertencia","Debe seleccionar un cheque de la lista para borrar","warning");
            return false;
        }
        var mensaje;
        mensaje="¿Esta seguro de eliminar el <br>cheque <b>#"+ select.numero +"</b> por <b>$"+select.importe+"</b>?";
        $('#mensajeDialogOption').html(mensaje);
        
        $('#dlgMensajeOption').dialog({
            buttons:[{
                            text:'<i class="fas fa-check"></i> Aceptar',
                            handler:function(){
                                  
                                console.log("dentro del ok de la dialog.");
                                $.ajax({ 
                                        type: 'GET', 
                                        url: 'json_recibo.php', 
                                        data: { borraCheque: 1, 
                                                cod:select.cod, 
                                                numero:select.numero,
                                                importe:select.importe
                                            }, 
                                        dataType: 'json',
                                        beforeSend: function(){
                                            $('#spinner').show();
                                        },
                                        success: function (data){ 
                                                console.log(data);
                                                 if(data.msg === "ok"){
                                                     // hacer algo con el row.

                                                     // llamar arecalculo del recibo 
                                                     // y marcar la factura de alguna forma como lista.
                                                     // aviso que todo bien
                                                      //$('#mensajeDialog').text("Se imputo $"+monto+ " a factura: "+row.item+"" );
                                                      //$('#dlgMensaje').dialog('open').dialog('center');

                                                      trae_lista_cheques();
                                                      $('#dlgMensajeOption').dialog('close');
                                                      //$('#spinner').hide();
                                                      //$.mobile.go('#panelCheque','slide','left');
                                                }
                                        },
                                        complete: function(){
                                              $('#spinner').hide();
                                        }
                                    });       
                            }
			},
                        {
                            text:'<i class="fas fa-times"></i> Cancelar',
                            handler:function(){
                                $('#dlgMensajeOption').dialog('close');
                            }  
                        }
                ]
        });
        $('#dlgMensajeOption').dialog('open').dialog('center');

        
               
        // para borrar el cheque voy a manar los tres datos.
        
        
    }
    
    // funcion total de los cheques
    
    function trae_totales(){
       
                          
        var total,efectivo,cheque,            
            saldo;
            
        total=$('#totalRecibo');
        efectivo=$('#totalEfectivo');
        cheque=$('#totalCheque');
        saldo=$('#totalSaldo');
                
        
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { totalReciboCheque:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                        $('#spinner').hide('fast');
                        $('#spinner').show('fast');
                    },
                    success: function (data) { 
                            console.log("dentro del total recibo chequesues");
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 // coloco los limites de maximos.
                                
                                // lleno el footer 
                                total.numberbox('setValue',data.total);
                                efectivo.numberbox('setValue',data.efectivo);
                                cheque.numberbox('setValue',data.cheque);
                                saldo.numberbox('setValue',data.saldo);
                                console.log("saldo");
                                console.log(data.saldo);
                                // si el efectivo viene distinto de cero
                                // es porque se cargo efectivo en algun momento
                                // habilito los botones de borrar efectivo y dolar
                                if(parseFloat(data.efectivo)>0){
                                     $('#efectivo-cancel').linkbutton('enable');
                                     $('#dolar-cancel').linkbutton('enable');
                                }
                                
                                
                                if(data.saldo===0||data.saldo<0){
                                      $('#botonResumen').linkbutton('enable');
                                      
                                      
                                      
                                    // se cubrio todo el recibo asi que habilito el finalizar
                                    
                                    
                                }else{
                                    // tengo saldo positivo o sea falta cancelar el recibo
                                    // no habilito el boton de finalizar
                                    $('#botonResumen').linkbutton('disable');
                                }
//                                 pesos.numberbox('max',data.saldo.toString());
//                                 dolar.numberbox('max',maxDolar.toString());
                                 
                             }
                            
                        },
                        complete: function(){
                            $('#spinner').hide('fast');
                        }
                });
    }
    
    function trae_lista_cheques(){
        var tabla=$('#tblCheques');
        var cajaCheque = $('#listaCajaCheque');
        
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { listaCheques:1,idCaja:cajaCheque.combobox('getValue')}, 
                    dataType: 'json',
                    beforeSend:function(){
                        $('#spinner').show();
                    },
                    success: function (data) { 
                            if(data.msg === "ok"){
                                 var opt=data.cheques;
                                // console.log(opt);
                                 tabla.datagrid({
                                    singleSelect: true,
                                    fit:false,
                                    fitColumns:true,
                                    border: true,
                                    scrollbarSize: 0,
                                    
                                    columns:[[
                                                {field:'cod',title:'Cod',width:120,hidden:true},
                                                {field:'banco',title:'Banco',width:100},
                                                {field:'numero',title:'Número',width:100},
                                                {field:'importe',title:'Importe',width:80}
                                                
                                            ]],
                                   // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
                                   data:opt
                                   
                                });
                            }
                            if(data.msg==="vacio"){
                                if (tabla.data('datagrid')){
                                    // no initialization
                                    tabla.datagrid('loadData',[]); 
                                } 
                            }
                        },
                    complete: function(){
                        $('#spinner').hide();
                    }    
                });
        
        trae_totales();
    }
    
 
   
        
        
    function trae_retenciones(){
        var tabla=$('#tblRetenciones');
        
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { listaRetencion:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            //console.log(data);
                          
                             if(data.msg === "ok"){
                                 var opt=data.retencion;
                                // console.log(opt);
                                 tabla.datagrid({
                                    header:'#hh',     
                                    singleSelect: true,
                                    fit:false,
                                    fitColumns:true,
                                    border: true,
                                    scrollbarSize: 0,
                                    
                                    columns:[[
                                                 {field:'cod',title:'Cod',width:120,hidden:true},
                                                {field:'retencion',title:'Retencion',width:120},
                                                {field:'certificado',title:'Cert',width:80},
                                                {field:'porcentaje',title:'%',width:50,align:'right'},
                                                {field:'monto',title:'Monto',width:100,align:'right'}
                                            ]],
                                   // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
                                   data:opt
                                });
                                 
                             }
                            
                    }
                });
    }    
    // FIN RECIBO RESUMEN
    // ========================================================================    
    // resumen del medio cobro 
    function trae_resumen_recibo(){
        var tipoRec;
       
        // traer el resumen del recibo
        
        tipoRec="acuenta";
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeResumenRecibo:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                         $('#spinner').show();
                    },
                    success: function (data) { 
                        console.log(data);

                         if(data.msg === "ok"){
                            tipoRec=data.tiporec;
                            console.log("tiporec::");
                            console.log(tipoRec);
                             // dibujar la tabla resumen
                             activa_tabla_resumen_recibo(data.resumen);
                             // si es por imputacion traigo las facturas
                                
                            console.log("tiporec::");
                            console.log(tipoRec);
                            $.ajax({ 
                                    type: 'GET', 
                                    url: 'json_recibo.php', 
                                    data: { traeResumenImputacion:1}, 
                                    dataType: 'json',
                                    beforeSend: function(){
                                         $('#spinner').show();
                                    },
                                    success: function (data) { 
                                        console.log(data);

                                         if(data.msg === "ok"){
                                              console.log("llamando a imputacion ");
                                             activa_tabla_imputacion(data.imputacion);

                                             // dibujar la tabla resumen


                                         }
                                    },
                                    complete: function(){
                                         $('#spinner').hide();
                                    }
                            });
                         }
                    },
                    complete: function(){
                         $('#spinner').hide();
                    }
        });
        
        
        
        
        
        // si es a cuenta solo medios de cobro.
        
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeResumenMedios:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                         $('#spinner').show();
                    },
                    success: function (data) { 
                        console.log(data);

                         if(data.msg === "ok"){
                             activa_tabla_medios(data.medios);
                             control_final_recibo();
                             // dibujar la tabla resumen
                             

                         }
                    },
                    complete: function(){
                         $('#spinner').hide();
                    }
            });
    // como ya debi cargar todo ahora si muestro el resumen.
        
//     ver si esta en condiciones de hacer el recibo o falta completar algo
        
            
            
            
    $.mobile.go('#panelResumen','slide','left');
       //$('#spinner').hide();
    }
    
   function control_final_recibo(){
       $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { controlFinalRecibo:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                         $('#spinner').show();
                    },
                    success: function (data) { 
                        console.log(data);
                        
                         if(data.msg === "error"){
                             var deuda=data.deuda;
                             swal("Atención!","No puede finalizar el recibo, debe cubrir:"+deuda,"warning");
                             $('#botonFinalizar').linkbutton('disable');
                         }
                    },
                    complete: function(){
                         $('#spinner').hide();
                    }
            });
   }
    
    // trae los datos del recibo resumen.
    
    function activa_tabla_resumen_recibo(data){
        var tablaResumen=$('#tblResumenRecibo');
        tablaResumen.datagrid({     
            singleSelect: false,
            fit:false,
            fitColumns:true,
            border: true,
            scrollbarSize: 0,
            striped:true,
            columns:[[
                         {field:'campo',title:'',width:100},
                        {field:'valor',title:'',width:120,align:'right'}
                    ]],
           // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
           data:data
        });
    }
    
    // dibuja la tabla imputacion
    function activa_tabla_imputacion(data){
        var tablaImputacion=$('#tblResumenImputacion');
        tablaImputacion.datagrid({     
            singleSelect: false,
            fit:false,
            fitColumns:true,
            border: true,
            scrollbarSize: 0,
            striped:true,
            columns:[[
                        {field:'campo',title:'',width:100},
                        {field:'cantidad',title:'Cant',width:50,align:'center'},
                        {field:'valor',title:'Monto',width:120,align:'right'}
                    ]],
           // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
           data:data
        });
    }
    
    
    // dibuja la tabla medios de cobro.
    
    function activa_tabla_medios(data){
        var tablaMedio=$('#tblResumenMedios');
        tablaMedio.datagrid({     
            singleSelect: false,
            fit:false,
            fitColumns:true,
            border: true,
            scrollbarSize: 0,
            striped:true,
            columns:[[
                        {field:'campo',title:'',width:100},
                        {field:'cantidad',title:'Cant',width:50,align:'center'},
                        {field:'valor',title:'Monto',width:120,align:'right'}
                    ]],
            rowStyler:function(index,row){
                
                if (row.campo==='Total Recibo:'){
                    console.log(row);
                    return 'color: #fff;background-color: #17a2b8; border-color: #17a2b8;font-weight:bolder;font-size: 1.5 em;';
                }
            },    
           
           data:data
        });
    }
    
    
    // FIN DE RECIBO GUARDARLO
    function guardar_recibo(){
       
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { guardarRecibo:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                         $('#spinner').show();
                    },
                    success: function (data) { 
                        console.log({data});

                         if(data.msg === "ok"){
                            // actualizo el saldo del cliente.
                            
                            $.ajax({
                                type: 'POST', 
                                url: 'seleccionar-cliente.php', 
                                data: {codCliente:data.codcliente}, 
                                dataType: 'json',
                                success: function (data) {
                                    console.log(data);
                                    console.log("saldo del cliente actualizado.");
                                }
                            });
                            
                            //alert("se guardoe l recibito man nro:"+data.nroRecibo);
                            var mensajeFin="<p>Se ha generado el <br>recibo <strong>"+data.nroRecibo+"</strong>";
                                mensajeFin +=" por <strong>$"+data.importe+"</strong>.</p>";
                            if(data.asiento!=='no'){
                                mensajeFin +="<br>Asiento: <strong>"+data.asiento+"</strong>";
                            }
                            
                            var divMensaje=document.createElement('div');
        
        
                                divMensaje.innerHTML = mensajeFin;
                            //$('#cuerpoExitoFin').html(mensajeFin);
                             //$.mobile.go('#panelMensajeExito','slide','left');
                            // poner titulo que se genearo 
                            // poner el icono grande del ok en verde
                             // si todo salio bien debo 
                             // mostrar la placa de resumen.
//                            swal("Hecho!", content:divMensaje, "success")
                            swal({
                                title: "Hecho!",
                                content: divMensaje,
                                icon: "success"
                                
                              })
                            .then((value)=>{
//                                    location.href='listado-clientes.php';
                                    location.href='fin-comprobante.php';
                            });
                             

                         }
                         // error
                         if(data.msg==="error"){
                             // colocar en el titulo que no se pudo cargar el recibo.
                             // poner el mensaje de error y anular el boton de nuevo recibo
                             // pedir solo salir.
                             console.log(data);
                             //alert('hubo un error');
//                             var mensajeFin="sucedio lo siguiente  <strong>"+data.desc+"</strong> ";
//                             mensajeFin +=" y no se generó el recibo. Intentelo mas tarde.";
//                            $('#CuerpoErrorFin').html(mensajeFin);
//                             $.mobile.go('#panelMensajeError','slide','left');
                            var mensajeFinNoHtml ="Ocurrio un problema :"+JSON.stringify(data.desc)+"\n";  
                            swal("Ops!", mensajeFinNoHtml, "error")
                            .then((value)=>{
                                    
//                                    $.ajax({ 
//                                        type: 'GET', 
//                                        url: 'json_recibo.php', 
//                                        data: { salirRecibo:1}, 
//                                        dataType: 'json',
//                                        success: function (data) { 
//                                            console.log(data);
//
//                                             if(data.msg === "ok"){
//                                                console.log({data});
//
//                                                location.href='listado-clientes.php';
//
//
//                                             }
//                                             // error
//                                             if(data.msg==="error"){
//                                                 
//                                                location.href='listado-clientes.php';
//
//
//                                             }
//                                        }
//                                    });
                            
                            });
                           
                         }
                    },
                        complete: function(){
                            $('#spinner').hide();
                        }
            });
    }
  
    function salida(){
        $('#mGeneralEf').menu('hide', true);
        $('#mGeneralCh').menu('hide', true);
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
    
    
    iniciar();
    $('#efectivoCobro').focus();
      
      
    </script>
</html>