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
//print_r($_SESSION);
if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}
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
     <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
    <div class="easyui-navpanel" id="P1">
        <header>
            <div class="m-toolbar">
                <span class="m-title">Recibo</span>
                 <div class="m-left">
                       
                       <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralEf',menuAlign:'left'"><i class="fas fa-bars"></i></a>


                    </div>
            </div>
        </header>
        <div id="mGeneralEf" class="easyui-menu" style="width:200px;" data-options="itemHeight:30,noline:true">
            <div>Rec: <?php echo $nroRecibo;?></div>
            <div class="menu-sep"></div>
            <div> <i class="fas fa-user-circle fa-lg fa-fw"></i><?php echo $clienteObj->cliente; ?></div>
            <div class="menu-sep"></div>
             <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="salida()"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
        </div>
        <div style="padding:5px" >
           
            <div class="m-title">Descuentos</div>
           
             <div style="padding:5px" >
                <select class="easyui-combobox" name="listaDescuento" id="listaDescuento"   data-options=" valueField: 'id',
                        textField: 'text',prompt:'% descuento...'" style="width:40%;">
                            
                </select>
                                 
<!--            </div>    
             <div style="padding:5px" >-->
               
                <input class="easyui-numberbox" id="montoDescuento" min="0" groupSeparator='.' decimalSeparator="," precision="2" prefix="$"    prompt="$ descuento" style="width:30%" >
                <a href="javascript:void(0)" class="easyui-linkbutton" id="descuento-ok"  onclick="acepta_descuento_autom($(this))"><i class="fas fa-check fa-fw"></i></a>
                 <a href="javascript:void(0)" class="easyui-linkbutton" id="descuento-cancel" disabled="true" onclick="borrar_descuento_autom($(this))"><i class="fas fa-times fa-fw"></i></a>
            </div>

       </div>
        <div style="padding:5px" >
        <div id="hh">
                <div class="m-toolbar">
                    <div class="m-title" style="text-align: left;">Retenciones</div>
                    <div class="m-right">
                        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="agregar_retencion()"><i class="fas fa-plus fa-lg"></i> Nueva</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="borrar_retencion()"><i class="fas fa-trash fa-lg"></i> Borrar</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="vaciar_retenciones()"><i class="fas fa-trash-alt fa-lg"></i> Vaciar</a>


                    </div>
                </div>
            </div>   
           
            
            <table id="tblRetenciones"></table>
      
        </div>    
           
            
        
        <footer>
           
                <div style="padding:2px;margin-bottom: 2px;">
                    <input class="easyui-numberbox" id="totalRecibo" min="0" groupSeparator='.' decimalSeparator="," precision="2"  data-options="readonly:true" prefix="$"   prompt="0" style="width:49%" label="A cubrir:">
                    <input class="easyui-numberbox" id="totalDescuento" min="0" groupSeparator='.' decimalSeparator="," precision="2"  data-options="readonly:true" prefix="$"   prompt="0" style="width:49%" label="Descuento:">
                </div>    
                <div style="padding:2px;margin-bottom: 2px;">
                    <input class="easyui-numberbox" id="totalRetencion" min="0" groupSeparator='.' decimalSeparator="," precision="2"  data-options="readonly:true" prefix="$"  prompt="0" style="width:49%" label="Retención:">
                    <input class="easyui-numberbox" id="totalSaldo" min="0" groupSeparator='.' decimalSeparator="," precision="2"  data-options="readonly:true" prefix="$"  prompt="0" style="width:49%" label="Saldo:">
                    
                </div>
            <div style="margin-bottom:5px">
                <a href="javascript:void(0)" class="easyui-linkbutton primaria" onclick="siguiente();" style="width:100%">Siguiente <i class="fas fa-angle-right fa-fw fa-lg"></i></a>
            </div>
           
        </footer>
     </div>   
        
    <!-- ALTA RETENCION -->    
        <div id="dialogRetencion" class="easyui-dialog" style="padding:20px 6px;width:80%;top:0px" data-options="inline:true,modal:true,closed:true,title:'Alta de retencion'">
             
        <div style="margin-bottom:5px">
             <select class="easyui-combobox" name="listaRetencion" id="listaRetencion"   data-options=" valueField: 'id',
                    textField: 'text',prompt:'tipo de retencion...',label:'Tipo:'" style="width:100%;"></select>
                    
               
            </div>
        <div style="margin-bottom:5px">
            <input class="easyui-textbox" inputmode="numeric" type="number" pattern="[0-9]*" label="Cert:" id="dlCertificado" prompt="Nro certificado" style="width:100%">
            </div>
        
            
             <div style="margin-bottom:5px">
                 <input class="easyui-textbox" inputmode="decimal" type="number" pattern="\d+(,\d{2})?"  id="dlImporte" min="0" groupSeparator='.' decimalSeparator="," precision="2" required="true" missingMessage="Debe completar el monto" prefix='$'   prompt="$ a retener" style="width:70%" label="Importe:"> 
            <!--</div><input class="easyui-textbox" inputmode="numeric" type="text" pattern="\d+(,\d{2})?"  id="dlImporte" min="0" decimalSeparator="," precision="2" required="true" missingMessage="Debe completar el monto" prefix='$'   prompt="$ a retener" style="width:70%" label="Importe:">--> 
            <div style="margin-bottom:5px">
               <input class="easyui-datebox" id="dlFechafCertificado"  prompt="" data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30,label:'Fecha:'" style="width:80%" >
            </div>
        <div style="margin-bottom:5px">
            <input class="easyui-numberbox" id="dlPorcentaje" min="0" max="100" decimalSeparator="," precision="1" required="true" missingMessage="Debe completar %" suffix='%'   prompt="porcentaje" style="width:70%" label="%:" value="1" > 
            </div>

        
    </div>
     <!--FIN ALTA RETENCION -->
    
        <!--DIALGO DE MENSAJES--> 
        <div id="dlgMensaje" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Information'">
            <p id="mensajeDialog">This is a message dialog.</p>
            <div class="dialog-button">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensaje').dialog('close');">OK</a>
            </div>
        </div>
        <!--FIN DIALOGO MENSAJES-->
        
    <div id="spinner" class="spinner" style="display:none;">
               <div class="centro">
                    <img src="_img/logo-administranet-ecommerce.png">   
                    <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
               </div>
            </div>  
</body>
 <script>
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
    
    // salida de los descuentos
    
    function siguiente(){
        $('#spinner').show();
        location.href="alta_recibo_medios_cobro.php";
    }
    
    // al cambiar el porcentaje
    $('#listaDescuento').combobox({
	onSelect: function(row){
		var porciento = row.id;
                var objSaldo,vSaldo,descuento;
                 
                 objSaldo=$('#totalSaldo');
                 
                vSaldo=objSaldo.numberbox('getValue');
                descuento=vSaldo*porciento/100;
                $('#montoDescuento').numberbox('setValue',descuento);
                console.log("vSaldo::"+vSaldo);
                console.log("descuento::"+descuento);
                // obtener el campo del monto y total del recibo.
            }
    });
    
    function iniciar(){
        $('#spinner').show();
        traer_descuentos();
        trae_tipo_retencion_cli();
        trae_retenciones(); 
        trae_totales();
    }
    
    function trae_totales(){
        $('#spinner').show();
        var total,
            descuento,
            retencion,
            saldo;
        total=$('#totalRecibo');
        descuento=$('#totalDescuento');
        retencion=$('#totalRetencion');
        saldo=$('#totalSaldo');
        
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { totalRecibo:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 total.numberbox('setValue',data.total);
                                 descuento.numberbox('setValue',data.descuento);
                                 retencion.numberbox('setValue',data.retencion);
                                 saldo.numberbox('setValue',data.saldo);
                                 
                             }else{
                                 // error 
                             }
                            
                            },
                    complete: function(){
                        $('#spinner').hide();
                    }
                });
    }
    
    function acepta_descuento_autom(quien){
        $('#spinner').show();
        var lPorcentaje=$('#listaDescuento').combobox('getValue');
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { altaDescuento:1,porcentaje:lPorcentaje}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 // recalcular 
                                 quien.linkbutton('disable');
                                 // bloqueo el porcentaje y dejo el importe en el campo 
                                 //$('#listaDescuento').combobox('disabled');
                                 $('#descuento-cancel').linkbutton('enable');
                                  $('#listaDescuento').prop( "disabled", false );
                                 trae_totales();
                             }else{
                                 // error 
                                 $('#mensajeDialog').text("hubo un inconveniente con el descuento, vuelva a intentar.");
                                 $('#dlgMensaje').dialog('open').dialog('center');
                                 $('#listaDescuento').combobox('focus');
                             }
                            
                            },
                    complete: function(){
                        $('#spinner').hide();
                    }
                });
    }
    
    function borrar_descuento_autom(quien){
       $('#spinner').show();
        $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { bajaDescuento:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 // recalcular 
                                 quien.linkbutton('disable');
                                 $('#descuento-ok').linkbutton('enable');
                                 $('#montoDescuento').numberbox('setValue',0);
                                 $('#listaDescuento').combobox('unselect');
                                 $('#listaDescuento').combobox('clear');
                                 $('#listaDescuento').prop( "disabled", true );
                                 trae_totales();
                             }else{
                                 // error 
                                 $('#mensajeDialog').text("hubo un inconveniente eliminacion de descuento, vuelva a intentar.");
                                 $('#dlgMensaje').dialog('open').dialog('center');
                                 $('#listaDescuento').combobox('focus');
                             }
                            
                    },
                    complete: function(){
                        $('#spinner').hide();
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
            console.log("fechaVacio");
            console.log(s);
               var ss = s.split('/');
		var y = parseInt(ss[2],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[0],10);
                return y+'-'+m+'-'+d;
            } 
            
        function traer_descuentos(){
            // solo los presento y si hay movimientos 
            
            var selDescuento=$('#listaDescuento');
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeDescuentos:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 var opt=data.descuentos;
                                 selDescuento.combobox({data:opt});
                                 
                             }else{
                                 // error 
                             }
                            
                            }
                });
        }
        function trae_tipo_retencion_cli(){
            var selRet=$('#listaRetencion');
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { traeRetencionCli:1}, 
                    dataType: 'json',
                    success: function (data) { 
                            console.log(data);
                          
                             if(data.msg === "ok"){
                                 var opt=data.tipoRetencion;
                                 selRet.combobox({data:opt});
                                 
                             }else{
                                 // error 
                             }
                            
                            }
                });
            
        }
     var indiceTabla=undefined;   
    
    // agrego una retencion 
   
    function agregar_retencion(){
        
        var ventana=$('#dialogRetencion');
        console.log('adentro');
        ventana.dialog({
                        'title':'Alta retención',
                        'buttons':[{
                                text:'<i class="fas fa-check fa-lg fa-fw"></i>Aceptar',
				
				handler:function(){
                                    $('#spinner').show();
                                    console.log("click en aceptar");
                                    var codRetencion=$('#listaRetencion').combobox('getValue'),
                                        tipoRetencion=$('#listaRetencion').combobox('getText'),
                                        fecha=fechaReves($('#dlFechafCertificado').datebox('getValue')),
                                        certificado=$('#dlCertificado').textbox('getValue'),
                                        porcentaje=$('#dlPorcentaje').numberbox('getValue'),
                                        monto=$('#dlImporte').numberbox('getValue');
//                                    console.log("codRetencion::"+codRetencion);
//                                    console.log("retencion::"+tipoRetencion);
                                    console.log("fecha::"+fecha);
//                                    console.log("certificado::"+certificado);
//                                    console.log("porcentaje::"+porcentaje);
//                                    console.log("monto::"+monto);
                                    // guardar el importe en el json.
                                                                        
                                    if(monto<=0){
                                        $('#spinner').hide();
                                        $('#mensajeDialog').text("El monto debe ser mayor a cero y positivo ");
                                        $('#dlgMensaje').dialog('open').dialog('center');
                                        return false;
                                    }
                                    
                                    // ajax de la imputacion 
                                    $.ajax({ 
                                            type: 'GET', 
                                            url: 'json_recibo.php', 
                                            data: { altaRetencion: 1, 
                                                    cod:codRetencion, 
                                                    tipo:tipoRetencion, 
                                                    fecha: fecha,
                                                    certificado:certificado,
                                                    porcentaje:porcentaje,
                                                    monto:monto
                                                }, 
                                            dataType: 'json',
                                            beforeSend: function(){
                                                $('#spinner').show();
                                            },
                                            success: function (data) { 
                                                    console.log(data);
                                                     if(data.msg === "ok"){
                                                         // hacer algo con el row.
                                                         
                                                         // llamar arecalculo del recibo 
                                                         // y marcar la factura de alguna forma como lista.
                                                         // aviso que todo bien
                                                          //$('#mensajeDialog').text("Se imputo $"+monto+ " a factura: "+row.item+"" );
                                                          //$('#dlgMensaje').dialog('open').dialog('center');
                                                          //vacio todo al guardar
                                                        $('#listaRetencion').combobox('setText','');
                                                        
                                                        $('#dlFechafCertificado').datebox('clear');
                                                        $('#dlCertificado').textbox('clear');
                                                        //$('#dlPorcentaje').textbox('clear');
                                                        $('#dlImporte').textbox('clear');
                                                        $('#dialogRetencion').dialog('close');
                                                        trae_retenciones();
                                                        trae_totales();
                                                    }
                                            },
                                            complete: function(){
                                                $('#spinner').hide();
                                            }
                                        });
                                    
                                    
                                    
                                    
                                }
                            },
                            {
                                     text:'<i class="fas fa-times fa-lg fa-fw"></i>Cancelar',
                                    handler:function(){
                                        
                                        $('#dialogRetencion').dialog('close');
                                    }
                                
                        }]
                        
                    });
                    ventana.dialog('open');
    }
    
     function borrar_retencion(){
//        onsole.log("borrando un dato.");    
//            $('#tblRetenciones').datagrid('cancelEdit', indiceTabla)
                // obtener el renglon
               var row=$('#tblRetenciones').datagrid('getRowIndex');
               var select = $('#tblRetenciones').datagrid('getSelected');
               console.log("que row:"+ JSON.stringify(row));
               console.log("que selected :"+JSON.stringify(select));
//               alert("no puedo borrar");
               console.log(select.key);
               var key=select.key;
               
        
        swal({
           title: "¿Está seguro borrar?",
           text: "Si acepta, se eliminará la retención "+select.certificado + " de $"+select.monto,
           icon: "warning",
            buttons: ["Cancelar", "Borrar"],
           dangerMode: true,
         })
        .then((willDelete) => {
            if (willDelete) {
                console.log('dentro del willdelete');
                $.ajax({
                   type:'GET',
                   url: 'json_recibo.php',
                   data:{bajaRetencion:1,key:key},
                   dataType: 'json',
                   beforeSend: function(){
                       $('#spinner').show();
                   },
                   success: function(data){
                       console.log({data});
                      trae_retenciones();
                        trae_totales();
                   },
                   complete: function(){
                       $('#spinner').hide();
                   }
                });
          } 
      });
        
    }    
    
    function vaciar_retenciones(){
        swal({
           title: "¿Está seguro de vaciar todo?",
           text: "Si acepta, se eliminarán todas las retenciones!",
           icon: "warning",
            buttons: ["Cancelar", "Vaciar"],
           dangerMode: true,
         })
        .then((willDelete) => {
          if (willDelete) {
              console.log('dentro del willdelete');
              $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { vaciarRetencion:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                        $('#spinner').show();
                    },
                    success: function (data) {
                        trae_retenciones();
                        trae_totales();
                    },
                    complete: function(){
                        $('#spinner').hide();
                    }
            });
          } 
      });
            
    }
        
    // buscar las retenciones     
    function trae_retenciones(){
                                                
        //$('#spinner').show();
        var tabla=$('#tblRetenciones');
        
            $.ajax({ 
                    type: 'GET', 
                    url: 'json_recibo.php', 
                    data: { listaRetencion:1}, 
                    dataType: 'json',
                    beforeSend: function(){
                      $('#spinner').show();  
                    },
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
                                                {field:'key',title:'Key',width:120,hidden:true},
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
                             if(data.msg==="no"){
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
    }    
        
    // no hya plata a cuenta hay que hacer medios de cobro 
    // directamente
    // descuento
     function salida(){
        $('#mGeneralEf').menu('hide', true);
//        $('#mGeneralCh').menu('hide', true);
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
     
    </script>
</html>