<!doctype html>
<html>
<?php
if (!isset($_SESSION))
    require_once '../sesion.inc.php';
if (!isset($_SESSION['id_sesion'])) {
    require_once '../sesion.inc.php';
}

if (!isset($mysqli)) {
    require_once '../conexion-general.inc.php';
    $mysqli = $connV;
}

define('MAXIMO', 4); //Nimero de productos que apareceran


$resultado = $mysqli->query("select DATABASE() AS db");
$fila = $resultado->fetch_object();

$basededatos = $fila->db;
$excesoDias = $_SESSION['cliente'][1];
?>
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Catalogo</title>  
 
	<!--<link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/mobile.css">-->
	<?php	include "javascript.php";?>
        <script type="text/javascript" src="funciones.js"></script>

       <?php

       
 // echo "<h3> BD: ".$basededatos." Borrame</h3> \n";     

?>
	<!--<script type="text/javascript" src="../_lib/easyui/jquery.easyui.mobile.js"></script>-->
        <style >
            /* spinner carrito */
            
            .spinnerAdm {
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
        .spinnerAdm div.centro{
            margin-top: 30%;
            background-color: #ffffff;
            height:150px;
            text-align: center;
            padding: 30px;
        }
        .spinnerAdm div.texto{
            margin-top: 30px;
            font-size: 20px;
        }
            
          .m-list  li {
                float: none;
                width: 100%;
                height: 100%;
                
                margin: 11px 0;
                background-color: #fff;
                -webkit-box-shadow: 0px 0px 10px 2px rgba(0,0,0,0.4);
                -moz-box-shadow: 0px 0px 10px 2px rgba(0,0,0,0.4);
                box-shadow: 0px 0px 10px 2px rgba(0,0,0,0.4);
                cursor: pointer;
               
                white-space: normal;
                padding-left: 11px;
             padding-right: 11px;
             line-height: 22px;
            }  
       .l-btn-text{
           font-size:18px;
           line-height: 40px;
       }
        .list-image{

            /*width: 40%;
            border: 0;
            margin-right: 5px;
            float: left;*/          
            
                    
                width: 100%;
            height: 280px;
            overflow: hidden;
            margin: 0 0 22px 0;
            background: none;
            display: inline-block; 
            text-align: center;
        }
        
        .list-image img{
            font-family: 'latobold';
            height: 280px;
                    font-size: 16px;
                    text-decoration: none;
                    line-height: 22px;
                    cursor: pointer;
                    display: inline-block;
                    width: auto;
        }
        .cuerpo-grilla{
            min-height: calc(100% + 22px);
            margin: 10px 10px 20px 10px;        
        }
        
        .list-header{
           /* font-size: 16px;*/
            font-family: 'OpenSans-SemiBold', sans-serif;
            font-size: 1.3rem;
            color: #737882;
            width: 100%;
            float: left;
            position: relative;
            min-height: 1px;
            
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
         .list-content{
            text-overflow: ellipsis;
            
            font-family: 'OpenSans-SemiBold', sans-serif;
            font-size: 1rem;
            color: #737882;           
            box-sizing: border-box;
           /* overflow: hidden;*/
        }
        .puntos{
            text-align: right;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 1.25rem;
            line-height: 22px;
            color: #337ab7;
            /*width: 100%;*/
            box-sizing: border-box;
        }
        .puntos span{
            text-align: right;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            line-height: 22px;
            color: #737882;
            position: absolute;
            right: 21px;
            bottom: 7px;
            box-sizing: border-box;
        }
        /* estilos para la canasta o carrito y el data grid.
        ************************************************************************
        */
        #panelCanasta{
            padding:3px;
        }
        .mis-puntos{
            text-align: left;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 1rem;
            line-height: 22px;
            color: #337ab7;
            height: 30px;
            margin-top: 22px;
            width: 100%;
            padding-left: 5%;
             padding-right: 5%;
        }
        .subtotal-puntos{
            text-align: left;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 1rem;
            line-height: 22px;
            color: #337ab7;
            height: 30px;
            width: 100%;
            padding-left: 5%;
             padding-right: 5%;
        }
        .total-puntos{
            text-align: left;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 1rem;
            line-height: 22px;
            color: #337ab7;
            height: 30px;
            margin-bottom: 22px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: #337ab7 dotted 1px;
            width: 100%;
            padding-left: 5%;
             padding-right: 5%;

        }
        .subtotal-puntos span,.mis-puntos span, .total-puntos span{
                float: right;
                font-family: 'OpenSans-Regular', sans-serif;
                font-size: 0.85rem;
                line-height: 22px;
                color: #337ab7;
                font-weight: bolder;
        }
        .btn{
            display: inline-block;
            margin-bottom: 0;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
            -ms-touch-action: manipulation;
            touch-action: manipulation;
            cursor: pointer;
            background-image: none;
            border: 1px solid transparent;
            white-space: nowrap;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            border-radius: 4px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .btn-primary{
            color: #fff;
            background-color: #337ab7;
            border-color: #2e6da4;
        }
        
        </style>
	</head>
        <body>
            <!--spinner admNET-->
<div id="spinner" class="spinnerAdm" style="display:block;">
    <div class="centro">
        <img src="../_img/logo-administranet-ecommerce.png">   
        <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
    </div>
</div>    
<!--fin spinner-->
            <div class="easyui-navpanel">
                <header>
                    <div class="m-toolbar">
                        <div class="m-title" id="mtitle">Seleccionar Premio</div>


                        <div class="m-right" style="font-size:14px;">
                            <!-- Derecho --> 
                            <a href="javascript:void(0);" style="top: 0%;margin-top: -10px;" class="easyui-linkbutton" data-options="plain:true" onClick="cargarCanasta();">
                                <span class="fa-stack">

                                    <i class="fas fa-heart fa-stack-2x" style="color:Tomato"></i>
                                    <strong  id="totalCanje" class="fa-stack-1x fa-inverse">-</strong>
                                </span>
                            </a>
                        </div>
                    </div>

                </header>

                
    <!--CATALOGO DE PREMIOS A CANJEAR CON PAGINACIONES-->            
                <div class="easyui-panel" id="panelista" data-options="openAnimation:'fade',closeAnimation:'fade'">

                    <div class="m-buttongroup" style="width:100%;">
                        <div style="padding:3px">
                            <input  id="categoria" class="easyui-combogrid" style="width:98%;"  data-options="toggle:true,group:'g2',plain:true,prompt:'Categorías...'">
                        </div>
                        <div style="padding:3px">
                            <input id="articulo" class="easyui-combogrid"  style="width:98%;"  data-options="toggle:true,group:'g2',plain:true,prompt:'Premios...'" >
                        </div>
                        <div style="padding:3px">
                            <input id="limitep" style="width:98%;" data-options="toggle:true,group:'g2',plain:true,label:'Puntos:'">
                        </div>
                    </div>



                    <div id="lista"></div>

                    <div class="easyui-pagination" data-options="total:0" id="paginador"></div>
                </div>
<!--  FIN CATALAGO DE PREMIOS -->

<!--CANASTA O CARRITO DONDE SE MUESTRA LO QUE SE VA A CANJEAR Y SE FINALIZA-->
                <div id="panelCanasta">
                    <table id="Canastilla">
                        <thead>
                            <tr>
                                <th data-options="field:'ID',hidden:true">ID</th>
                                <th data-options="field:'Premio',align:'left',width:200">Premio</th>
                                <th data-options="field:'Cantidad',align:'center'">Cant</th>
                                <th data-options="field:'Puntos',align:'center'">Ptos</th>
                                <th data-options="field:'Total',align:'right',width:80">Total</th>
                                
                            </tr>
                        </thead>
                    </table>

                    <div id="PieCanastilla"></div>
                    <div id="panelCanastaPie">
                        <center>
                            <a href="javascript:void(0)" class="easyui-linkbutton c1" style="width:90%;height:35px" onclick="$('#spinner').show('fast');consolidar_canje()" data-options="group:'g2'"><i class="fas fa-check fa-fw fa-lg"></i> Canjear</a>
                            <!--<a href="javascript:void(0)" class="easyui-linkbutton" style="width:45%;height:35px" onclick="javascript:$('#panelCanasta').panel('close')" data-options="group:'g2',plain:true"><i class="fas fa-trash-alt fa-fw fa-lg"></i> Vaciar</a>-->
                            
                        </center>
                    </div>
                </div>
<!-- FIN CARRITO -->


<!--DIALOGO VIEJO PARA MOSTRAR AGREGAR A CARRITO-->
                <div id="dlg1" class="easyui-dialog" style="padding:20px 6px;width:90%;" data-options="inline:true,top:0,modal:true,closed:true,title:''">
                    
                    <!--Agregar el id_ de la tabla para eliminar luego actualizar -->
                    <div id="imgseleccionada"></div>
                    <div id="ccantidad"></div>
                    <div id="pieDetalleItem" class="dialog-button">

                       

                    </div>
                </div>

<!--==============================================-->

            </div>


<script>
const numerito= new Intl.NumberFormat('es-AR',{
    style: 'decimal',                    
    minimumFractionDigits: 0
});     
/*
*   FUNCIONES DE JAVASCRIPT DECLARACION
*   =============================================================================
*/



    
    


 $('#articulo').combogrid({
        url:'json/sp_ab_premios_json.php',
	    idField:'id_abm_premios',
        textField:'nombre_premios',
        emptyMsg:'Sin premios para su consulta',
	columns:[[
        
        {field:'nombre_premios',title:'Nombre:',width:'80%'},
       // {field:'descripcion_premios',title:'Descripcion:',width:'50%'},
        {field:'puntos_premios',title:'Puntos:',width:'20%'}
    ]],	
	onSelect: function(nuevo, ObjetoDeMiDeseo) {
		buscar();
		
	}
	
});





 $('#categoria').combogrid({
    url:'json/sp_categoria_abm_premios_json.php',
	idField:'id_categoria_abm_premios',
    textField:'descripcion_categoria_premios',
	columns:[[
        
        {field:'descripcion_categoria_premios',width:'100%'}
    ]],	
	onSelect: function(nuevo, ObjetoDeMiDeseo) {
		//buscar();
		recargaArticulo();
		buscardesdeCategoria();
		
	}
	
	
});

$('#limitep').numberspinner({
	min: 0,
	value: 1,
	onChange: function(nuevo, viejo) {
		buscar();
	}
});

$('#paginador').pagination({
    pageList: [5,10],
    pageSize:5,
    total: 5,
    layout:['links'],
    onSelectPage:function(pageNumber, pageSize){
        $(this).pagination('loading');
        
        var g = $('#categoria').combogrid('grid');	// get datagrid object
        var r = g.datagrid('getSelected');
        var categoria=0;

        if(r){

            categoria = r.id_categoria_abm_premios;

        }else{

            let categoria =0;	
        }

        let puntosObtenidos=$('#limitep').numberspinner('getValue');
        let lista=$("#lista");
        //alert('pageNumber:'+pageNumber+',pageSize:'+pageSize);       
        
       
        // simulo la funcion buscar porque le paso parametros
        $.ajax({
            dataType: "json",
                data:{
                        "pageNumber": pageNumber,
                        'pageSize':pageSize,
                        'puntos':puntosObtenidos,
                        'categoria':categoria
                },
                url: "./json/catalogo1.php",
        beforeSend: function() {
                    // setting a timeout
                    //lista.html("<img src='../_lib/sw/loader.gif' >");
                    $('#spinner').show('fast');
                }
        })
        .done(function( data, textStatus, jqXHR ) {
            if ( console && console.log ) {
                console.log( "Catalogo 1 consultado, pagina "+pageNumber );
               lista.html(data.listaHtml);
                
                if(data.cuantos!==0){
                    console.log("dentro del total");
                    $(this).pagination({total: data.cuantos});
                }
                //$(this).pagination('loaded');
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud a catalogo2 fallado: " +  textStatus);
               // lista.text( "La solicitud a fallado: " +  textStatus);
            }
       })
       .complete(function(){
           $('#spinner').hide('fast');
       });	
        
        
        
    }
});



function recargaArticulo(){
//	 $('#articulo').combogrid({
     var puntosObtenidos=$('#limitep').numberspinner('getValue');
	var g = $('#categoria').combogrid('grid');	// get datagrid object
	var datum =  g.datagrid('getSelected');
	console.log(datum.id_categoria_abm_premios);
	
	var x = $('#articulo').combogrid('grid');
        var r = x.datagrid('load', {
            categoria: datum.id_categoria_abm_premios,
            puntos:puntosObtenidos
            // buscar los articulos filtrados por categoria...
        });
}




function buscar(){
    var maximo = <?php echo MAXIMO; ?>;

    let articulo=$('#articulo').textbox('getValue');


    var g = $('#categoria').combogrid('grid');	// get datagrid object
    var r = g.datagrid('getSelected');
    var categoria=0;

    if(r){

        categoria = r.id_categoria_abm_premios;

    }else{

        let categoria =0;	
    }
	
    let puntosObtenidos=$('#limitep').numberspinner('getValue');
    let paginador = $('#paginador');
    let lista=$("#lista");
    console.log('Categoria para buscar en buscar::'+categoria);
//lista.load("./json/catalogo1.php");
    console.log("Mando articulo:"+articulo+" puntos obtenidos:"+puntosObtenidos+" categoria?:"+categoria);
    $.ajax({
        dataType: "json",
        data: {
            "articulo" : articulo,
            "puntos" : puntosObtenidos
        },
        url: "./json/catalogo1.php",
        beforeSend: function() {
            // setting a timeout
            //lista.html("<img style='width:100%;' src='carrello.gif' >");
             $('#spinner').show('fast');
        }
    })
     .done(function( data, textStatus, jqXHR ) {
         if ( console && console.log ) {
             console.log( "consutla en BUSCAR llamado desde los puntos.=?." );
             console.log({data});
              
             lista.html(data.listaHtml);
             if(data.cuantos!==0){
                paginador.pagination({'total': data.cuantos});
              }



         }
     })
     .fail(function( jqXHR, textStatus, errorThrown ) {
         if ( console && console.log ) {
             console.log( "La solicitud premio a fallado: " +  textStatus);
             lista.text( "La solicitud ha fallado: " +  textStatus);
         }
    })
    .complete(function(){
         $('#spinner').hide('fast');
    });



}


function buscardesdeCategoria(){
    var maximo = <?php echo MAXIMO;?>;

    let articulo=$('#articulo').textbox('getValue');


    var g = $('#categoria').combogrid('grid');	// get datagrid object
    var r = g.datagrid('getSelected');
    var categoria;

    if(r){

                     categoria = r.id_categoria_abm_premios;

    }else{

                    let categoria =0;	
    }

    let puntosObtenidos=$('#limitep').numberspinner('getValue');

    let lista=$("#lista");
    let  paginador = $('#paginador');
    //lista.load("./json/catalogo1.php");
    $.ajax({
        dataType: "json",
        data: {
                "categoria" : categoria,
                "puntos" : puntosObtenidos
                },
        url: "./json/catalogo1.php",
                beforeSend: function() {
            // setting a timeout
            //lista.html("<img style='width:100%;' src='carrello.gif' >");
             $('#spinner').show('fast');
        }
    })
     .done(function( data, textStatus, jqXHR ) {
         if ( console && console.log ) {
             console.log( "se ha completado consulta de premio." );
                     lista.html(data.listaHtml);
                    
             if(data.cuantos!==0){
                paginador.pagination({'total': data.cuantos});
              } 



         }
     })
     .fail(function( jqXHR, textStatus, errorThrown ) {
         if ( console && console.log ) {
             console.log( "La solicitud premio a fallado: " +  textStatus);
             lista.text( "La solicitud ha fallado: " +  textStatus);
         }
    })
    .complete(function(){
         $('#spinner').hide('fast');
    });

}

	
function cargarCanasta(){
    $('#panelCanasta').panel('open');
    console.log("Cargar CAnasta() catalogo.php--------------");
    $.ajax({   
        url: "./json/canasta.php",
    beforeSend: function() {
        
     //  caja.html("<img src='../_lib/sw/loader.gif' >");
        $('#spinner').show('fast');
        }
    })
    .done(function( data, textStatus, jqXHR ) {
        if ( console && console.log ) {
            console.log( "Datos obtenidos para cagar Tabla. con el detalle de articlos--------->" );
            if(data=="")return;
            if( data === undefined || data === null)return;
            let Objeto = $.parseJSON(data);
            let Columnas = Objeto.columnas;
            let Datos = Objeto.datos;
            console.log({Objeto});
            let strr = '';
	
            if(Objeto.Mensaje)console.log(Objeto.Mensaje);
		
            if(Datos.length>0){	


                var porcion;
                var Acumulador = 0;
                console.log({Datos});
                $.each(Datos, function( index, value ) {
                    console.log({value});
                        porcion=Datos[index].Total;
                //	porcion = porcion.toString();
                //	porcion= porcion.replace(/<[^>]*>/g, '');
                        porcion = Number(porcion);
                        console.log('Acumulador::'+Acumulador);
                        console.log('porcion::'+porcion);
                        Acumulador+=porcion;

                });
                var xpuntos = 0;
                $.ajax({
                    async: false,
                    dataType: 'json',
                    url: "./json/puntosdeusuarios.php",
                    success: function( resultado, textStatus, jqXHR ) {
                        console.log("consultando los puntos del usuario para hacer calculo para mostrar los puntos que quedn.");
                        console.log({resultado});
                        xpuntos=resultado.rows[0].puntos_premios;

                     //  caja.html("<img src='../_lib/sw/loader.gif' >");
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        xpuntos=1;                                    
                    }

                });

                var otrodiv;

                otrodiv="<div class='mis-puntos'>Tus puntos: <span >"+numerito.format(xpuntos)+" pts</span></div>\n";
                otrodiv+="<div class='subtotal-puntos'>A canjear: <span>("+numerito.format(Acumulador*-1)+") pts</span></div>\n";

               
                otrodiv+="<div class='total-puntos'>Saldo: <span>"+numerito.format((xpuntos-Acumulador))+" pts</span></div>\n";


                //$('#pietabla').empty();
                $('#PieCanastilla').html(otrodiv);
                //$('#Canastilla').append(strr);
                $('#Canastilla').datagrid({
                         idField: 'ID',
                         singleSelect: true,
                         data: Datos,
                         nowrap: false,
                         fitColumns:true,
                         onSelect: function(index,row){
                                 //alert(index)
                                 AbrirDialogo();
                         }
                 });
                 
                
            }
         $('#spinner').hide('fast');
         
        }
    })
    .fail(function( jqXHR, textStatus, errorThrown ) {
        if ( console && console.log ) {
            console.log( "La solicitud a fallado: " +  textStatus);

        }
    })
    .complete(function(){
         $('#spinner').hide('fast');
    });
  
 
 
}




	
function quitarItem(id,premio){
    $('#dlg1').dialog('close');
    console.log('Dentro del eliminar un item------------------------');
    console.log('a quien elimino:::'+premio);
    //Swal.fire();
    Swal.fire({
      position:'top',  
      title: 'Está seguro?',
      text: "No podrá deshacer la acción!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, quitar!'
    }).then((result) => {
      if (result.value) {
        $.ajax({   
                data: {
                    "eliminar" : id
                },
                url: "./json/canasta.php"	    
            })
        .done(function( data, textStatus, jqXHR ) {
            if ( console && console.log ) {
                 console.log( "La solicitud se ha completado correctamente." );
                 Swal.fire({
                    position:'top',  
                    title:'Listo!',
                    text:'Su premio fue quitado.',
                    icon:'success'
                });
                //
                                    
                  Carting();
                  totalItemsCanasta();
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
         if ( console && console.log ) {
             console.log( "La solicitud a fallado: " +  textStatus);
             lista.text( "La solicitud a fallado: " +  textStatus);
         }
        });

      }
    });
    
		
}

function totalItemsCanasta(){
    console.log('Consultando el total de premios en canasta--------');
    var tc = $('#totalCanje');
    var url = "./json/cantidadItemsCanasta.php";
    $.ajax({
                dataType: 'json',
                url: url,
            beforeSend: function() {
             // setting a timeout
                    tc.hide();
                     //$('#spinner').show('fast');
                 }
             })
             .done(function( data, textStatus, jqXHR ) {
                tc.text(data);
                tc.hide().fadeIn();
                })
            .fail(function( jqXHR, textStatus, errorThrown ) {
                    tc.text(0);
                    })
            .complete(function(){
                //$('#spinner').hide('fast');
            });        
    
}
	
function formatPrice(val,row){
	//return '<span style="color:red;">('+val+')</span>';
	return '<img src="'+val+'" width="40px" />';
	return val;

}


//function Botonear(val,row){
	//return '<span style="color:red;">('+val+')</span>';
	//return '<img src="'+val+'" width="40px" />';
	//return val;
	//console.log(row);

//}



function Seleccionar(){
    let fila = $('#Canastilla').datagrid('getRows');
    if(fila[0].ID){
        $.ajax({

            data: {
                    "eliminar" : fila[0].ID
            },
            url: "./json/canasta.php",
            beforeSend: function() {
            // setting a timeout
               $('#spinner').show('fast');
            }
        })
        .done(function( data, textStatus, jqXHR ) {
            if ( console && console.log ) {
                $('#spinner').hide('fast');
                location.reload(true);
                $('#dlg1').dialog('close');

            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            if ( console && console.log ) {
                console.log( "La solicitud a fallado: " +  textStatus);
                lista.text( "La solicitud a fallado: " +  textStatus);
            }
        });
	
    }else{
	alert("No hay producto Seleccionado para quitar de la canasta");
    }
	
}

function AbrirDialogo(){
	
    let fila = $('#Canastilla').datagrid('getSelected');
    console.log(fila);
    var html = '<h3><center>'+fila.Premio+'</center></h3>';
    html += 'Cargando imagenes....';
    $('#imgseleccionada').html(html);
    html = '<h3><center>'+fila.Premio+'</center></h3>';
    let prefe = "";
    $.ajax({
        dataType: "json",
        data: { id: fila.ID,size: 't' },
        url: "./json/imagenproducto.php",
        beforeSend: function() {
             $('#spinner').show('fast');
        }
    })
    .done(function( data, textStatus, jqXHR ) {   
        console.log("fotos: ------------");
        console.log({data});
        $.each(data, function( index, value ) {
               html+='<center><img src="'+data[index].url+'" /></center>'; 
               console.log('cargando.. '+data[index].url);
        });
        var botonCierre='<center> <a href="javascript:void(0)"  style="font-weight:bold;width:50%;padding:5px;color:inherit;text-decoration:none;margin:2px;" onclick="$(\'#dlg1\').dialog(\'close\')"><i class="fas fa-times fa-lg"></i> Cerrar</a>';
        botonCierre +=' <a href="javascript:void(0)"  style="font-weight:bold;width:50%;padding:5px;color:red;text-decoration:none;margin:2px;" onclick="quitarItem('+fila.ID+',\''+fila.Premio+'\')"><i class="fas fa-trash-alt fa-lg"></i> Quitar</a></center>';
        $('#imgseleccionada').html(html);
        $('#pieDetalleItem').html(botonCierre);
        
     
    })
    .complete(function(){
         $('#spinner').hide('fast');
    });
    $('#dlg1').dialog('open');
	
}

/**
*   acciones en JAVASCRIPT
*   ================================================================================================
*/

// CANTIDAD DE ITEMS EN CANASTA
// ==============================================================================
$(window).load(function(){
   // PAGE IS FULLY LOADED  
   // FADE OUT YOUR OVERLAYING DIV
   //$('#spinner').fadeOut();
});
$( document ).ready(function() {
    console.log('Iniciando el calulo de items , puntos, y demas.........>');
   
    var tc = $('#totalCanje');
    var url = "./json/cantidadItemsCanasta.php";

    $.ajax({
        dataType: 'json',
        url: url,
    beforeSend: function() {
     // setting a timeout
            tc.hide();
             //$('#spinner').show('fast');
        }
    })
    .done(function( data, textStatus, jqXHR ) {
        // $('#spinner').hide('fast');
        tc.text(data);
        tc.hide().fadeIn();
        })
    .fail(function( jqXHR, textStatus, errorThrown ) {
            tc.text(0);
            })
    .complete(function(){
       // $('#spinner').hide('fast');
    });        

    // CANASTA
	
    let caja = $('#Canastilla');
    let tabla = $('#Canastilla');
    let pietabla = $("#PieCanastilla");

    //Carting();

	////////////////////////////////
// PUNTOS DEL CLIENTE Y ALTA DE datos en articulos
    $.ajax({
        url: "./json/puntosdeusuarios.php",
        dataType: 'json',
    
    success: function( data, textStatus, jqXHR ) {
        $('#spinner').hide('fast');
        if ( console && console.log ) {
            console.log( "puntos de usuario obtenidos." );
            let ptos = data.rows[0].saldo_premios;
            let vencen = data.rows[0].vencimiento;
       //  console.log(data.rows[0].saldo_premios);
		
            $("#mtitle").html("Tus puntos: "+ptos);
            console.log('metiendo los datos en el limite p de puntos.');
            $('#limitep').numberspinner('setValue', ptos);
            var x = $('#articulo').combogrid('grid');
            var r = x.datagrid('load', {
                puntos: ptos
                // buscar los articulos filtrados por categoria...
            });
        }
    },
    error: function( jqXHR, textStatus, errorThrown ) {
        if ( console && console.log ) {
           // $('#spinner').hide('fast');
            console.log( "La solicitud ha fallado: " +  textStatus);
            let ptos = 0;
            let vencen = '01/01/2099';
            $("#mtitle").text("Tus puntos: "+ptos+"<br>vencen "+vencen);
            $('#limitep').numberspinner('setValue', ptos);       
        }
    }	
    });

    let ventana = $('#panelCanasta');
    let ilista = $('#panelista');

    ventana.panel({
        
    //    height: 300,
        closable: false,
        modal: true,
            title:'<i class="fas fa-gift fa-lg fa-fw"></i> Tu canje  <a href="javascript:void(0)" style="color:inherit;float:right;text-decoration:none;" onclick="javascript:$(\'#panelCanasta\').panel(\'close\')" data-options="group:\'g2\',plain:true"><i class="fas fa-times "></i> Cerrar</a>',
        openAnimation: 'fade',
        closeAnimation: 'fade',
        loadingMessage: 'Cargando...',
        closed: true,
    //	footer:'#panelCanastaPie',
    //	header: '#panelCanastaBarra',
        onOpen: function(){
        ilista.panel('close');	
        },
        onClose: function(){
            ilista.panel('open');	
        }
        
    }); 
    //$('#spinner').hide('fast');
	
});     
<?php if(isset($excesoDias['exceso'])&&$excesoDias['exceso']==1):?>
	Swal.fire({
		title: 'Atención',
		icon: 'warning',
		html: 'El cliente <strong>no puede realizar canjes</strong>, ha excedido su limite de crédito en <strong><?php echo $excesoDias['dias_exceso_limite'];?> días</strong><br> Por favor, regularice su situación',
		showCancelButton: false,
		confirmButtonText: 'Aceptar',		
		
	});
	//.then((resultado) => {
    //        if (resultado.isConfirmed) {
    //            parent.location.href = "../listado-clientes.php";
    //        }

    //    });
<?php endif;?>
</script>

</body>    
</html>