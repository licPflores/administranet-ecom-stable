<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
/* 
 * PUNTOS CON easy ui
 * programacion 100% mobil
 * por pasos con guia.
 * 
 * 
 */
require_once '../sesion.inc.php';
//echo "<pre>";
//print_r($_SESSION);

?>
<!doctype html>
<html>
<head>
     <meta charset="UTF-8">  
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gestion Puntos Clientes</title>  
    

    <link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/bootstrap/easyui.css"> 
    <link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/mobile.css"> 
    
    <link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/icon.css">  
   
<!--     <link rel="stylesheet" type="text/css" href="_css/main_styles.css">-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous"> 
    <script type="text/javascript" src="../_lib/easyui/jquery.min.js"></script>  
    <script type="text/javascript" src="../_lib/easyui/jquery.easyui.min.js"></script> 
    <script type="text/javascript" src="../_lib/easyui/jquery.easyui.mobile.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    
</head>
    <body>
        <pre>
            <?php // print_r($_SESSION);?>
        </pre>
    <div class="easyui-navpanel" id="P1">
        <header>
            <div class="m-toolbar">
                <div class="m-title">Gestión saldo de clientes </div>
                
            </div>


        </header>
         
         <div style="padding:5px" >            
            
            <div style="padding:5px">
                <select name="listaClientes" id="listaClientes"  style="width:80%;"></select>                    
                <a href="javascript:void(0)" class="easyui-linkbutton" id="btn-confirma-cliente" onclick="confirmar_cliente()" ><i class="fas fa-check fa-fw"></i> Confirmar</a>    
            </div> 
 
           
            
        </div>   
        <div style="padding:10px;display:none" id="divDatosClienteSeleccionado">
            <h3>Cliente objetivo</h3>
            
                <input class="easyui-textbox" id="nombreCliente"   data-options="readonly:true" style="width:100%" label="<strong>Cliente:</strong>"> 
            
                <input class="easyui-numberbox" id="puntosActuales" min="0" max="99999"  data-options="readonly:true" style="width:100%" suffix="pts" label="<strong>Saldo:</strong>"> 
            
                <input type="hidden" id="codCliente" name="codCliente" >
                <input type="hidden" id="idSpSaldo" name="idSpSaldo" >
                
           
            
        </div>

        <div id="acciones" style="padding:10px 20px;" >
        <h3>Acciones</h3>
        <div style="margin-bottom:10px">
            <span class="m-buttongroup">
                <a href="javascript:void(0)" class="easyui-linkbutton" id="agregar" data-options="toggle:true,group:'g1'"  onclick="agregar_puntos()">Agregar puntos</a> 
                <a href="javascript:void(0)" class="easyui-linkbutton" id="colocar" data-options="toggle:true,group:'g1'" onclick="colocar_puntos()">Colocar puntos</a> 
                <a href="javascript:void(0)" class="easyui-linkbutton" id="transferir" data-options="toggle:true,group:'g1'"  onclick="transferir_puntos()">Transferir puntos</a> 
                <input type="hidden" id="queAccion" name="queAccion">
            </span>
            <div>
                <p id="cartelAccion">Seleccionar una accón.</p>
            </div>    
        </div>
        <div style="margin-bottom:10px;padding:20px">
            
    
            <input  id="puntosNuevos"  precision="0"  required="true" data-options="disable:true" missingMessage="debe completar puntos" prompt="00 puntos" style="width:50%" label="<strong>Puntos:</strong>"> 
        
        </div>
        <div style="padding:20px" >            
            
            <div id="divClienteDestino">
                <select name="listaClientesT" id="listaClientesT"  style="width:100%;" ></select> 
                <input type="hidden" id="nombreClienteDestino">
                <input type="hidden" id="codClienteDestino">
                <input type="hidden" id="idSpSaldoDestino">
                <input type="hidden" id="puntosActualesDestino">
            </div>                
                
        </div>
        
        <div style="text-align:center;padding:10px;" id="divBotones">
            <div id="dlg-buttons">
                <a href="javascript:void(0)" class="easyui-linkbutton" id="confirmaAccion"  style="width:90px"><i class="fas fa-check fa-lg fa-fw"></i> Guardar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" id="cancelarAccion" onclick="restaurar()" style="width:90px"><i class="fas fa-times fa-lg fa-fw"></i> Cancelar</a>
            
            </div>
        </div>

    </div>

    
    
   
</body>
 <script>
     
    function inicio(){
    // solo los presento y si hay movimientos 
        let botonConfirmaCliente,panelCliente,botonera,acciones,listaCliente,divClienteDestino;
        let puntosNuevos;
        $.ajax({ 
            type: 'GET', 
            url: 'json/gestion-puntos-json.php', 
            data: { accion:'clientes'}, 
            dataType: 'json',
            success: function (data) { 
               // console.table(data.data);

                if(data.msg === "ok"){
                    dataClientes=data.data;
                    listaCliente.combogrid({
                        prompt:'seleccionar un cliente',
                        label:'<strong>Cliente:</strong>',
                        idField:'Codigo',
                        textField:'ncliente',
                        striped:true,
                        // url:'json/lista-clientes-puntos.json',
                        data:dataClientes,
                        mode:'local',
                        loadMsg:'Espere por favor',
                        columns:[[
                            
                            //{field:'id_manual_cli',title:'Id:',width:'15%',align:'left'},
                            {field:'ncliente',title:'Cliente:',width:'99%',align:'left'},
                            //{field:'saldoTotal',title:'Puntos:',width:'10%',align:'right'}
                        ]],	  
                        
                        filter: function(q, row){
                            var opts = $(this).combogrid('options');
                            //var x=q.toString();
                            
                          //  if(x.length>2){
                                //console.log(x.length);
                                //console.log(row[opts.textField]);
                                return row[opts.textField].indexOf(q) == 0;
                            //}
                        },
                        onSelect: function(index,row) {
                            //console.table(row);
                            
                            $('#nombreCliente').textbox('setValue', row.id_manual_cli+' '+row.nombre_cliente);
                            $('#puntosActuales').textbox('setValue',parseInt(row.saldoTotal));
                            $('#idSpSaldo').val(row.id_sp_saldo);
                            $('#codCliente').val(row.Codigo);
                            //console.log(row.id_sp_saldo==null);
                            // no tengo creada una cuenta solo puedo colocar puntos.
                            // si tengo saldo en cero no puedo transferir
                            if(row.id_sp_saldo===null){
                                $('#agregar').linkbutton('disable');
                                $('#transferir').linkbutton('disable');
                            }
                            // tengo saldo en cero no puedo transferir
                            if(parseInt(row.id_sp_saldo)===0){
                                $('#transferir').linkbutton('disable');
                                $('#agregar').linkbutton('disable');
                            }

                        }
                    
                    });
                }

            }
        });

                    
        listaCliente=$('#listaClientes');
        divClienteDestino = $('#divClienteDestino');
        acciones = $('#acciones');
        queCliente = $('#codCliente');
        acciones = $('#acciones');
        botonera = $('#divBotones');
        panelCliente = $('#divDatosClienteSeleccionado');
        puntosNuevos = $('#puntosNuevos');
        

        //console.log(dataClientes);
        /** opcion 1 */
        

        panelCliente.hide();
        divClienteDestino.hide();
        acciones.hide();    
        //puntosNuevos.hide(); 
        botonera.hide();
    }

        
    /** aceptar un cliente  */
    function confirmar_cliente(){
        // si confirmamos el cliente 
        let listaClientes,botonConfirmaCliente,panelCliente,acciones,botonera;
        let puntosNuevos,botonConfirma;
        botonConfirma =$('btn-confirma-cliente');
        puntosNuevos = $('#puntosNuevos');
        listaClientes=$('#listaClientes');
        queCliente = $('#codCliente');
        acciones = $('#acciones');
        botonera = $('#divBotones');
        panelCliente = $('#divDatosClienteSeleccionado');

        if(queCliente.val()===''){
            swal.Fire({
                icon:'warning',
                text: 'Debe seleccionar un cliente'
            });
            return false;
        }
        botonConfirma.linkbutton('disable',true);
        listaClientes.combogrid('disable',true);

        panelCliente.show();
        acciones.show();
        // puntosNuevos.show();
        puntosNuevos.numberbox();

        botonera.show();
    }

    /** agregar puntos, foco al input y lo pone en cero oculta otra cosa. */
    function agregar_puntos(){
        console.log('agregando puntos solo paso el foco y cierro y coloco en cero transferir.');
        let cartel= $('#cartelAccion');
        cartel.text('Agrega puntos al cliente objetivo, toma los puntos actuales y le suma los puntos colocados.');
        $('#queAccion').val('agregar');
        $('#puntosNuevos').numberbox('enable');
        //$('#puntosNuevos').textbox('textbox').focus();
        $('#puntosNuevos').numberbox('clear').numberbox('textbox').focus(); 
        limpia_transferencia();


    }

    /** colocar puntos, foco al input y lo pone en cero oculta otra cosa. */
    function colocar_puntos(){
        console.log('pisando los puntos');
        let cartel= $('#cartelAccion');
        cartel.text('Reemplaza el saldo actual del cliente objetivo, por los puntos colocados en el campo puntos');
        $('#queAccion').val('colocar');
        $('#puntosNuevos').numberbox('enable');
        //$('#puntosNuevos').click();
        
        //$('#puntosNuevos').numberbox('textbox').select();
        $('#puntosNuevos').numberbox('clear').numberbox('textbox').focus(); 

        
        limpia_transferencia();
    }
    /** 
    transferir puntos De un cliente a otro. 
    */
    function transferir_puntos(){   
        // poner puntos de cliente A en puntos agregar , mostrar cliente B, 
        console.log('transferencia de puntos de clientes.');
        let cartel= $('#cartelAccion');
        let htmlCartel='Transfiere <strong>todos</strong> los puntos del <strong>cliente objetivo</strong>, hacia el cliente <strong>destino</strong>,';
        htmlCartel +='si el destino tiene saldo, se le agregaran los puntos transferidos.';
        htmlCartel +='<br> El cliente <strong>objetivo tendrá 0(CERO)</strong> puntos.';
        cartel.html(htmlCartel);
        $('#queAccion').val('transferir');
        let dataClientes,puntosNuevos;            
        let listaCliente = $('#listaClientesT');
        let divClienteDestino = $('#divClienteDestino');
        puntosNuevos = $('#puntosNuevos');
        divClienteDestino.show();
        puntosNuevos.numberbox('disable',false);
        puntosNuevos.numberbox('setValue',$('#puntosActuales').numberbox('getValue'));
        puntosNuevos.numberbox('readonly',true);
        $.ajax({ 
            type: 'GET', 
            url: 'json/gestion-puntos-json.php', 
            data: { accion:'clientes'}, 
            dataType: 'json',
            success: function (data) { 
                //console.table(data.data);

                if(data.msg === "ok"){
                    dataClientes=data.data;
                    listaCliente.combogrid({
                        prompt:'cliente destino',
                        label:'<strong>Transferir a:</strong>',
                        idField:'Codigo',
                        striped:true,
                        textField:'ncliente',
                    // url:'json/lista-clientes-puntos.json',
                        data:dataClientes,
                        mode:'local',
                        loadMsg:'Espere por favor',
                        columns:[[
                            
                           // {field:'id_manual_cli',title:'Id:',width:'15%',align:'left'},
                            {field:'ncliente',title:'Cliente:',width:'99%',align:'left'},
                            //{field:'saldo_premios',title:'Puntos:',width:'10%',align:'right'}
                        ]],	
                        
                        filter: function(q, row){
                            var opts = $(this).combogrid('options');
                            //var x=q.toString();
                            //i/f(x.length>2){
                                //console.log(x.length);
                                //console.log(row[opts.textField]);
                                return row[opts.textField].indexOf(q) == 0;
                            //}
                        },
                        onSelect: function(index,row) {
                            //console.table(row);
                            
                            $('#nombreClienteDestino').val(row.id_manual_cli+' '+row.nombre_cliente);
                            $('#puntosActualesDestino').val(parseInt(row.saldoTotal));
                            $('#idSpSaldoDestino').val(row.id_sp_saldo);
                            $('#codClienteDestino').val(row.Codigo);


                        }
                        
                    });
                    
                }

            }
        });
        
        
    }

    function limpia_transferencia(){
        let codigoClienteDestino,idSpSaldoDestino,divClienteDestino,puntosNuevos,listaClientesT;
        codigoClienteDestino=$('#codClienteDestino');
        idSpSaldoDestino =$('#idSpSaldoDestino');
        puntosNuevos = $('#puntosNuevos');
        divClienteDestino = $('#divClienteDestino');
        listaClientesT = $('#listaClienteT');

        codigoClienteDestino.val('');
        idSpSaldoDestino.val('');
        puntosNuevos.numberbox('setValue',0);
        listaClientesT.combogrid('clear');
        divClienteDestino.hide();

    }

    function validar_datos(){
        let idCliente, puntosAntes,puntosNuevos,accion,idClienteDestino,puntosAntesDestino;
        idCliente = $('#codCliente').val();
        puntosAntes= $('#puntosActuales').numberbox('getValue');
        puntosNuevos = $('#puntosNuevos').numberbox('getValue');
        accion = $('#queAccion').val();
        idClienteDestino = $('#codClienteDestino').val();
        
        // cliente seleccionado inicial.
        if(idCliente==''){
            swal.Fire({
                icon:'warning',
                text:'Debe seleccionar un Cliente Objetivo'
            });
            return false;
        }
        // puntos nuevos
        if(puntosNuevos==''||isNaN(puntosNuevos)){
            swal.Fire({
                icon:'warning',
                text:'Debe colocar un valor numérico en los puntos a asignar'
            });
            return false;
        }else{
            if(puntosNuevos<0){
                // valores negativos
                swal.Fire({
                    icon:'warning',
                    text:'Los puntos no puede tener valores negativos'
                });
                return false;
            }
        }
        // si soy transferir valido que haya un cliente destino seleccionado.
        if(accion=='transferir'){
            if(idClienteDestino==''){
                swal.Fire({
                    icon:'warning',
                    text:'Debe seleccionar un cliente destino'
                });
                return false;
            }
            
        }

        return true;
    }

    function armar_html(accion){
        let html ='';
        
        if(accion=='agregar'){
            let cliente,saldo,puntos,saldoNuevo;

            cliente=$('#nombreCliente').textbox('getValue');
            saldo =$('#puntosActuales').numberbox('getValue');
            puntos=$('#puntosNuevos').numberbox('getValue');
            saldoNuevo = (parseInt(saldo)+parseInt(puntos));
            console.log({cliente,saldo,puntos,saldoNuevo});
            html +='Se <strong>agregarán</strong> '+ puntos +' pts a <br><strong>'+cliente+'</strong> Saldo actual:<strong>'+saldo+' pts</strong><br>';                
            html +='Su nuevo saldo será: <strong>'+saldoNuevo+' pts</strong>';
            //console.log(html);
        }
        if(accion=='colocar'){
            let cliente,saldo,puntos,saldoNuevo;
            cliente=$('#nombreCliente').textbox('getValue');
            saldo =$('#puntosActuales').numberbox('getValue');
            puntos=$('#puntosNuevos').numberbox('getValue');
            saldoNuevo = parseInt(puntos);
            console.log({cliente,saldo,puntos,saldoNuevo});
            html +='Se <strong>pondrán</strong> '+ puntos +' pts a <br><strong>'+cliente+'<strong> Saldo actual:</strong>'+saldo+' pts</strong><br>';                        
            html +='Su nuevo saldo será: <strong>'+saldoNuevo+' pts</strong>';
            //console.log(html);
        }
        if(accion=='transferir'){   
            let clienteOrigen,saldoOrigen,puntos,clienteDestino,saldoDestino,saldoFinalDestino;
            //console.log($('#nombreCliente').textbox('getValue'));
            //$('#nombreCliente').textbox('readOnly',false);
            clienteOrigen   = $('#nombreCliente').textbox('getValue');
            saldoOrigen     = $('#puntosActuales').numberbox('getValue');
            puntos          = $('#puntosNuevos').numberbox('getValue');
            clienteDestino  = $('#nombreClienteDestino').val();
            saldoDestino    = $('#puntosActualesDestino').val();
            saldoFinalDestino = (parseInt(saldoDestino)+parseInt(puntos));

            html +='Se <strong>tranfieren</strong> '+puntos+' pts a <br><strong>'+clienteDestino+'</strong> Saldo actual:<strong>'+saldoDestino+'</strong> pts<br>';
            html +='Su saldo consolidado será: <strong>'+saldoFinalDestino+'</strong>pts.<br>';
            html +='El Cliente: <strong>'+clienteOrigen+'</strong> tendrá su saldo en <strong>0</strong> pts<br>';   
            
        }
        return html;
    }
    /** funcion que restablece todo a cero. */
    function restaurar(){
        Swal.fire({
                title: '¿Estás seguro?',
                text: 'Si cancelás se reestablecerán todas las opciones',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonText: 'No',           
                
                //cancelButtonColor: '#d33',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.value) {
                    location.reload();
                }
            }); 
    }

    /** GUARDAR TODO */
    function generar_accion(){  
        // traer todos los datos y configuar segun accion.
        let accion,idCliente,idSaldoCliente,saldoCliente,puntos,idClienteDestino,idSaldoClienteDestino,saldoClienteDestino,saldoConsolidado;
        accion          = $('#queAccion').val();
        idCliente       = $('#codCliente').val();
        idSaldoCliente  = $('#idSpSaldo').val();
        saldoCliente    = $('#puntosActuales').numberbox('getValue');
        puntos          = $('#puntosNuevos').numberbox('getValue');
        idClienteDestino    = $('#codClienteDestino').val();
        idSaldoClienteDestino   = $('#idSpSaldoDestino').val();
        saldoClienteDestino     = $('#puntosActualesDestino').val();
        if(accion=='agregar'){
            saldoConsolidado=parseInt(saldoCliente) + parseInt(puntos);
            //console.log('agregar saldoConsolidado::',saldoConsolidado);
        }

        if(accion=='colocar'){
            saldoConsolidado= parseInt(puntos);
            //console.log('colocar saldoConsolidado::',saldoConsolidado);
        }

        if(accion=='transferir'){
            saldoConsolidado=parseInt(saldoClienteDestino)+ parseInt(puntos);
            //console.log('transferir saldoConsolidado::',saldoConsolidado);
        }

    
        $.ajax({ 
            type: 'GET', 
            url: 'json/gestion-puntos-json.php', 
            data: { 
                accion:'alta',
                motivo:accion,
                idCliente:idCliente,
                idSaldoCliente:idSaldoCliente,
                saldoCliente:saldoCliente,
                puntos:puntos,
                idClienteDestino:idClienteDestino,
                idSaldoClienteDestino:idSaldoClienteDestino,
                saldoClienteDestino:saldoClienteDestino,
                saldoConsolidado:saldoConsolidado
                
                }, 
            dataType: 'json',
            success: function (data) { 
                //console.log({data});

                if(data.msg === "ok"){
                    Swal.fire({
                        icon:'success',
                        title:'Excelente',
                        text:'Los cambios se realizaron con exito'
                    }).then((result) => {
                        if (result.value) {
                            location.reload();
                        }
                    }); 

        
                    
                    
                }
                if(data.msg=='error'){
                    Swal.fire({
                        icon: 'error',
                        title: 'Ooops!',
                        text: 'No se pudieron gestionar los movimientos.intente más tarde'
                    });
                }
            }
        });
    }

    /** acciones */
    
    
    // consulta y confirma y validad datos 
    $('#confirmaAccion').on('click',function(){
        let htmlTxt,datosOk,accion;
        accion=$('#queAccion').val();
       // console.log('la accion tomada',accion);
        // validando acciones.
        datosOk=validar_datos();
        console.log(armar_html(accion));
        htmlTxt = armar_html(accion);
        if(datosOk===true){
            // armando el mensaje a mostrar           
            Swal.fire({
                title: 'Estas seguro?',
                html: htmlTxt,
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: 'No',           
                
                //cancelButtonColor: '#d33',
                confirmButtonText: 'Si!'
            }).then((result) => {
                if (result.value) {
                    generar_accion();
                }
            }); 
        }
    });
    
    inicio();
    </script>
</html>