<?php
require_once 'sesion.inc.php';
if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}
$nroRecibo="";
$desde=date("d/m/Y", strtotime("-1 year"));
$hasta=date("d/m/Y");
if(isset($_SESSION["recibo"])){
    $nroRecibo=$_SESSION["recibo"]["nroRecibo"];
}
//echo "<pre>";
//print_r($_SESSION["recibo"]);
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
<?php 
    // recupero el recibo sesion para sacar los datos de ahi.
$rec=$_SESSION["recibo"];
?>
<body>
    
    <div class="easyui-navpanel" id="panelImputacion" data-options="openDuration:600">
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

        <div id="dl" data-options="
                fit: true,
                border: false,
                lines: true,
                title:'Facturas pendientes'
                ">
        </div>
       <footer>
            <div class="m-toolbar" style="padding:3px;">
                <div style="margin:1px;width:32%;float:left;">
                    
                    <input class="easyui-numberbox" id="montoImputarTotal" min="0" groupSeparator='.' decimalSeparator="," precision="2"  data-options="readonly:true,prefix:'$',labelPosition:'top',labelAlign:'right'"   prompt="0" style="width:100%" label="Imputado">
                </div>
                    <div style="margin:1px;width:32%;float:left;">
                    <input class="easyui-numberbox" id="montoDeudaTotal" min="0" groupSeparator='.' decimalSeparator=","  precision="2"  data-options="readonly:true,prefix:'$',labelPosition:'top',labelAlign:'right'"   prompt="0" style="width:100%" label="Saldo">
                </div>    
                <div style="margin:1px;width:32%;float:left;">
                <input class="easyui-numberbox" id="montoAcuenta" min="0" groupSeparator='.' decimalSeparator=","  precision="2"  data-options="readonly:true,prefix:'$',labelPosition:'top',labelAlign:'right'"   prompt="0" style="width:100%" label="A Cuenta">
                    
                </div>
                <div style="padding:3px;">
                    <a id="btn" href="javascript:void(0)" class="easyui-linkbutton primaria" style="width:100%" onclick="finalizar_imputacion()">Siguiente <i class='fas fa-chevron-right fa-fw fa-lg'></i></a>
                </div>
            </div>
        </footer>
    </div>
    
    
    <div id="dlgImputacion" class="easyui-dialog" style="padding:20px 6px;width:80%;top:0px" data-options="inline:true,modal:true,closed:true,title:'Imputar'">
        <div style="margin-bottom:10px">
           <!--<input class="easyui-textbox" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" id="montoImputar" min="0" decimalSeparator="," precision="2" required="true" prefix="$" missingMessage="Debe completar el monto a impútar"   prompt="monto efectivo " style="width:90%" label="Monto:">--> 
           <input class="easyui-textbox" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" id="montoImputar" min="0"  required="true" decimalSeparator="," prefix="$" missingMessage="Debe completar el monto a impútar"   prompt="monto efectivo " style="width:90%" label="Monto:"> 
        </div>
        <div style="margin-bottom:10px" id="cuerpoDlgImputacion">            
        </div>     

    </div>
    
    <div id="dlgNoImputa" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Desimputar'">
         <p id="mensajeDialogNo">¿Esta seguro que desea desimputar la factura?</p>
    </div>
    
    <div id="dlgMensaje" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Información'">
        <p id="mensajeDialog" style="text-align: center;">This is a message dialog.</p>
            <div class="dialog-button">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensaje').dialog('close');"><i class="fas fa-check fa-lg fa-fw"></i> OK</a>
            </div>
        </div>
    
    <!-- FIN resumen de lo imputado -->
    <div class="easyui-navpanel" id="panelFin" data-options="openDuration:600">
        <header>
            <div class="m-toolbar">
                <span class="m-title">Recibo</span>
                 <div class="m-left">
                       
                       <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralEfin',menuAlign:'left'"><i class="fas fa-bars"></i></a>


                    </div>
            </div>
        </header>
        <div id="mGeneralEfin" class="easyui-menu" style="width:200px;" data-options="itemHeight:30,noline:true">
            <div>Rec: <?php echo $nroRecibo;?></div>
            <div class="menu-sep"></div>
            <div> <i class="fas fa-user-circle fa-lg fa-fw"></i><?php echo $clienteObj->cliente; ?></div>
            <div class="menu-sep"></div>
             <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="salida()"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
        </div>
        
        <div id="dlFin" data-options="
                fit: true,
                border: false,
                lines: true,
                title: 'Resumen de Imputación',
                ">
        </div>
        
       <footer>
           <div style="text-align:center;padding:5px">
                    
               
                        <a href="#" class="easyui-linkbutton" data-options="plain:true" style="width:49%" onclick="$.mobile.go('#panelImputacion','slide','right');"><i class="fas fa-times fa-lg fa-fw"></i> Cancelar</a>
                        <a href="#" class="easyui-linkbutton" style="width:49%" onclick="confirmar_imputacion()"> <i class="fas fa-check fa-lg fa-fw"></i> Confimar </a>
                    
                </div>
<!--            <div class="m-toolbar">-->
                <div style="padding:10px">
                    <input class="easyui-numberbox" id="totalImputado" min="0" groupSeparator='.'  decimalSeparator="," precision="2"   data-options="readonly:true,prefix:'$'"   prompt="0" style="width:90%" label="Imputado:">
                    
                </div>
            <!--</div>-->
        </footer>
         
    </div>
    <div id="spinner" class="spinner" style="display:none;">
               <div class="centro">
                    <img src="_img/logo-administranet-ecommerce.png">   
                    <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
               </div>
            </div>  
    <script>
//        history.pushState(null, null, location.href);
//    window.onpopstate = function () {
//        history.go(1);
//    };
        
        const numerito= new Intl.NumberFormat('es-AR',{
            style: 'decimal',                    
            minimumFractionDigits: 2
        }); 
        
        const dinero = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    minimumFractionDigits: 2
                   
                  });
        
        $.fn.datebox.defaults.formatter = function(date){
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		return (d<10?('0'+d):d)+'/'+(m<10?('0'+m):m)+'/'+y;
	};
	
	
	$.fn.datebox.defaults.parser = function(s){
		if (!s) return new Date();
		var ss = s.split('/');
		var y = parseInt(ss[2],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[0],10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
			return new Date(y,m-1,d);
		} else {
			return new Date();
		}
	};
        function buscar(){
                console.log('debo volver a pasar por esto');
                $('#dl').datalist('load',{
                    name: 'easyui',
                    subject: 'datagrid',
                    listarFacturas:1,
//                    desde: fechaReves($('#fDesde').datebox('getValue')),
//                    hasta: fechaReves($('#fHasta').datebox('getValue')),
                    cliente: <?php echo $objCliente->Codigo; ?>
                });
            }
        function fechaReves(s){
               var ss = s.split('/');
		var y = parseInt(ss[2],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[0],10);
                return y+'-'+m+'-'+d;
        } 
        
        // total de imputacion
        
        function total_imputado(){
            

            $.ajax({ 
                        type: 'GET', 
                        url: 'json_recibo.php', 
                        data: { totalReciboCheque:1}, 
                        dataType: 'json',
                        success: function (data) { 
                                console.log("dentro del total recibo chequesues");
                                console.log(data);

                                 if(data.msg === "ok"){
                                     console.log("dentro del ok del imputado total::"+data.total);
                                    // lleno el footer 
                                   
                                    $('#montoImputarTotal').numberbox('setValue',data.total);
                                 }

                            }
            });
            
        } 
        
        // calculo de la deuda de facturas.
        
        function calculo_deuda(){
            console.log("calculando la deuda ");
            var deuda=0,totalDeuda=$('#montoDeudaTotal');
            var rows = $('#dl').datalist('getRows');
            console.log({rows});
            for(var i=0; i<rows.length; i++){
              console.log(rows[i].Saldo);
              deuda +=parseFloat(rows[i].Saldo);
            }
             totalDeuda.numberbox('setValue',deuda);
        }
        
        // finalizar de imputacion.
        
        function finalizar_imputacion(){
            $.ajax({ 
                type: 'GET', 
                url: 'json_recibo.php', 
                data: { finImputacion: 1
                    }, 
                dataType: 'json',
                success: function (data) { 
                        console.log(data);
                        if(data.msg==="fallo"){
                             $('#mensajeDialog').text("Debe imputar facturas" );
                             $('#dlgMensaje').dialog('open').dialog('center');
                        }
                         if(data.msg === "ok"){
                             // hacer algo con el row.
                             var fact=data.resumen;
                             $('#dlFin').datalist({
                                data: fact,
                                textField: 'factura',          
               
               
                                textFormatter: function(value,row){
                //                    console.log(row);
                                    return 'Fact: ' + value +  '  Saldo: '+ dinero.format(row.saldo) +'<br> <strong> Imputado: '+dinero.format(row.imputado)+'</strong>';
                                }
                             });
                             $('#totalImputado').numberbox('setValue',data.total);
                             $.mobile.go('#panelFin','slide','right'); 

                        }
                }
            });
        }
        
        function confirmar_imputacion(){
            $('#spinner').show();
            location.href="alta_recibo_descuento.php";
            
        }
        
        // funcion para traer lo que hay a cuenta.
        
        function traer_recibos_cuenta(){
            var montoaCuenta=$('#montoAcuenta');
            $.ajax({ 
                  type: 'GET', 
                  url: 'json_recibo.php', 
                  data: { traeAcuenta:1}, 
                  dataType: 'json',
                  success: function (data) { 
                      console.log(data);

                       if(data.msg === "ok"){
                          console.log({data});
                          montoaCuenta.numberbox('setValue',data.acuenta);
                       }
                       // error
                       if(data.msg==="error"){
                           // colocar en el titulo que no se pudo cargar el recibo.
                           // poner el mensaje de error y anular el boton de nuevo recibo
                           // pedir solo salir.
                          // alert('hubo un error');
                          console.log(data);
                          montoaCuenta.numberbox('setValue',0);


                       }
                  }
              });
            
            
        }
        
        $(function(){
            
            // rango de fechas 
            
            $('#dl').datalist({
                //data: data,
                url: 'json_recibo.php',
                queryParams: {
                    name: 'easyui',
                    subject: 'datagrid',
                    listarFacturas:1
//                    desde: fechaReves($('#fDesde').datebox('getValue')),
//                    hasta: fechaReves($('#fHasta').datebox('getValue')),
                    
                },
                emptyMsg:'No se encontraron resultados',        
                textField: 'item',
                valueField:'id',
                checkbox:false,
                checkOnSelect:false,
                singleSelect: false,
                textFormatter: function(value,row,index){
//                    console.log( $('#dl').datalist('refreshRow',index););
                        //console.log({row});
                       
                    
                    var seleccionados=  $('#dl').datalist('getSelections');
                    var linea='<a href="javascript:void(0)" class="datalist-link" >'+ value +' Fecha: '+row.FechaB+'<br> Saldo: <strong>$'+numerito.format(row.Saldo)+  '</strong> </a>';
                    //console.log(seleccionados);
                    if(seleccionados.length>0){
                        jQuery.each( seleccionados, function( i, val ) {
                           //console.log("i."+i);
                           //console.log("val:"+jQuery.param( val ));
                          // console.log("seleccionada row:"+ jQuery.param( row ));
                           if(val.id_recibo_factura===row.id_recibo_factura){
                             //  console.log("igualdad encontrada");
//                               linea='<a href="javascript:void(0)" class="datalist-link" ><strong><i class="fas fa-check-circle fa-lg fa-fw"></i> ' + value +'</strong> Saldo: <strong>$'+numerito.format(row.Saldo)+  '</strong>  </a><button onclick="desimputar('+index+')">Desimputar</button>';
                                linea='<a href="javascript:void(0)" class="datalist-link" ><strong><i class="fas fa-check-circle fa-lg fa-fw"></i> ' + value +'</strong> Saldo: <strong>$'+numerito.format(row.Saldo)+  '</strong>  </a>';
                           }
                        });
                        
                    }else{
                        linea='<a href="javascript:void(0)" class="datalist-link" >' + value + ' Fecha: '+row.FechaB+' <br>Saldo: <strong>$'+numerito.format(row.Saldo)+  '</strong> </a>';
                    }
//                    console.log("deuda ("+index+") =>"+numerito.format(calculoDeuda));
//                    console.log("deuda Global =>"+numerito.format(totalGlobalDeuda));
                   
                    // ver si es posible que quede con un check o tilde para mostrar que se imputo la factura mas alla del color.
                    //agregar un valor del row que sea imputado o vacio y ahi se lo pongo al i <> i
                    return linea;
                },
                onLoadSuccess: function(){
                    calculo_deuda();
                },   
                        //onClickRow
                onSelect: function(index,row){
                    
                    $('#dlgImputacion').dialog({
                        'title':row.item,
                        'buttons':[{
                                text:'<i class="fas fa-plus fa-lg fa-fw"></i> Imputar',
				
				handler:function(){
                                    //console.log(row);
                                    var monto=parseFloat($('#montoImputar').numberbox('getValue'));
                                    var maximo=parseFloat(row.Saldo);
                                    var todoBien=0;
                                    // guardar el importe en el json.
                                    console.log($('#montoImputar').numberbox('getValue'));
                                    console.log('monto:'+monto);
                                    console.log('maximo:'+maximo);
                                   console.log(monto>maximo);
                                   console.log('que resultado');
                                    if(monto>maximo){
                                        todoBien ++;
                                        $('#mensajeDialog').html("El monto $"+ monto +" a imputar supera el máximo <strong>$"+row.Saldo+"</strong>");
                                        $('#dlgMensaje').dialog('open').dialog('center');
                                        //$('#montoImputar').numberbox('clear').numberbox('textbox').focus();
                                        $('#montoImputar').numberbox('setValue',0);
                                        console.log("NO PODES SEGUIR");
                                    }
                                    if(monto<=0){
                                        todoBien++;
                                        $('#mensajeDialog').text("El monto debe ser mayor a cero y positivo ");
                                        $('#dlgMensaje').dialog('open').dialog('center');
                                    }
                                     //console.log(row);
                                    // ajax de la imputacion 
                                    if(todoBien==0){
                                        $.ajax({ 
                                                type: 'GET', 
                                                url: 'json_recibo.php', 
                                                data: { imputarFactura: 1, 
                                                        idrecibofactura:row.id_recibo_factura, 
                                                        codmodfact:row.CodigoMovimiento, 
                                                        fecha: row.Fecha,
                                                        nrofactura:row.NroComprobante,
                                                        importe:row.Importe,
                                                        cancelado:row.Cancelado,                                                    
                                                        saldo: row.Saldo,
                                                        tipocomprobante:row.TipoComprobante,
                                                        vencimiento:row.Vencimiento,
                                                        condventa:row.CondVenta,
                                                        aimputar: monto

                                                    }, 
                                                dataType: 'json',
                                                success: function (data) { 
                                                        console.log(data);
                                                         if(data.msg === "ok"){
                                                             // hacer algo con el row.
                                                             row.Saldo=row.Saldo-monto;

                                                             //console.log(row.Saldo);
                                                             // llamar arecalculo del recibo 
                                                             // y marcar la factura de alguna forma como lista.
                                                             // aviso que todo bien
                                                             //$(this).datalist.refreshRow(index);
                                                             $('#dlgImputacion').dialog('close');
                                                             $('#dl').datalist('refreshRow',index);
                                                               total_imputado();
                                                               calculo_deuda();
                                                              $('#mensajeDialog').html("Se imputo <b>$"+monto+ "</b> <br>a factura: <b>"+row.item+"</b>" );

                                                              $('#dlgMensaje').dialog('open').dialog('center');

                                                             // $('#dl').datalist('selectRow',index);
                                                              //$('#dl').datalist('freezeRow',index);

                                                        }
                                                }
                                            });
                                    
                                    }
                                    
                                    
                                }
                            },
                            {
                                    text:'<i class="fas fa-times fa-fw fa-lg"></i> Cancelar',
                                    handler:function(){
                                        $('#dlgImputacion').dialog('close');
                                        // preguntar primero si fui seleccionado antes.
                                        var seleccionados=  $('#dl').datalist('getSelections');
                                        $('#dl').datalist('unselectRow',index);
                                    }
                                
                        }]
                        
                    });
                    // asigno datos antes de seguir
                    
                    $('#dlFecha').textbox('setValue',row.FechaB);
                    $('#dlImporte').textbox('setValue',row.Importe);
                   // $('#dlCancelado').textbox('setValue',row.Cancelado);
                    $('#dlaCancelar').textbox('setValue',row.Saldo);
                    var cuerpoImp="";
                    cuerpoImp+='<p><label class="textbox-label">Fecha: </label> <label class="textbox-label"><strong>'+row.FechaB+'</strong></label></p>';
                    cuerpoImp+='<p><label class="textbox-label">Importe: </label> <label class="textbox-label"><strong>'+dinero.format(row.Importe)+'</strong></label></p>';
                    cuerpoImp+='<p><label class="textbox-label">Saldo: </label> <label class="textbox-label"><strong>'+dinero.format(row.Saldo)+'</strong></label></p>';
                    $('#cuerpoDlgImputacion').html(cuerpoImp);
                    
                    
//                    $('#montoImputar').numberbox('setValue',row.Saldo);
                    $('#montoImputar').textbox('setValue',row.Saldo);
                    //$('#montoImputar').val(row.Saldo);
                    
                     // bloq
                    
                    
                    $('#dlgImputacion').dialog('open').dialog('hcenter');
                    $('#montoImputar').numberbox('textbox').focus();
//                    $('#p2-title').html(row.item);
//                    $.mobile.go('#p2');
//                console.log(row);
                },
                      
                onBeforeUnselect:function(index,row){
                    //alert('me hicieron before un select unselect');  
                    //console.log('before unselect'+{row});
                     $('#dlgNoImputa').dialog({
                        'title':row.item,
                        'buttons':[{
                                text:'<i class="fas fa-trash fa-fw fa-lg"></i> Desimputar',
                                handler:function(){
                                    // ajax de la imputacion 
                                    $.ajax({ 
                                            type: 'GET', 
                                            url: 'json_recibo.php', 
                                            data: { desimputarFactura: 1, 
                                                    idrecibofactura:row.id_recibo_factura 
                                                    
                                                            
                                                }, 
                                            dataType: 'json',
                                            success: function (data) { 
                                                    console.log(data);
                                                     if(data.msg === "ok"){
                                                         // hacer algo con el row.
                                                         console.log(" Desimputar saldoantes::::"+row.Saldo);
                                                         var nuSaldo=data.saldoNuevo; 
                                                         var monto = nuSaldo-row.Saldo;
                                                         row.Saldo=nuSaldo;
                                                         console.log("Saldo nuevo::"+row.Saldo);
                                                         
                                                         // llamar arecalculo del recibo 
                                                         // y marcar la factura de alguna forma como lista.
                                                         // aviso que todo bien
                                                           $('#dlgNoImputa').dialog('close');
                                                          $('#mensajeDialog').html("Se desimputó <b>$"+dinero.format(monto)+ "</b> <br>a factura: <b>"+row.item+"</b>" );
                                                          $('#dlgMensaje').dialog('open').dialog('center');
                                                         
                                                           $('#dl').datalist('refreshRow',index);
                                                          //$('#dl').datalist('unselectRow',index);
                                                          total_imputado();
                                                          calculo_deuda();
                                                          //$('#dl').datalist('freezeRow',index);
                                                    }
                                            }
                                        });
                                }
                            }
                        ,
                            {
                                    text:'<i class="fas fa-times fa-lg fa-fw"></i>Cancelar',
                                    handler:function(){
                                        $('#dlgNoImputa').dialog('close');
                                        return false;
                                    }                                
                        }]
                        
                    });  
                    // que no se abra si esta sin imputar nada.
                   console.log('no quiero imputar sin nada');
                   console.log(row);
                   var monti=parseFloat($('#montoImputarTotal').numberbox('getValue'));
                   console.log(monti);
                   if(monti!==0){
                        $('#dlgNoImputa').dialog('open').dialog('center');
                   }
                     //return false;
                }        
                
                
                
                        
            });
           // consultar si hay plata a favor
//           var facturitasD=  $('#dl').datalist('getRows');
//           console.log("facturitasD");
//           console.log({facturitasD});
           traer_recibos_cuenta();
        });
        
    function desimputar(row,index){
        console.log("hola vengo a desimputar");
        console.log({row});
        console.log({index});
        console.log("index a desimputar::"+index);
    
    }
    
    function salida(){
        $('#mGeneralEf').menu('hide', true);
        //$('#mGeneralCh').menu('hide', true);
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
</body> 
</html>
