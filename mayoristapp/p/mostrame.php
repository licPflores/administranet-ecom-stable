<?php
$puntos=0;
	if(!isset($_SESSION))require_once '../sesion.inc.php';
	if(!isset($_SESSION['id_sesion'])){
			require_once '../sesion.inc.php';	

		}

if(!isset($mysqli)){
	require_once '../conexion-general.inc.php'; 
	$mysqli = $connV;
}

if ($mysqli->connect_errno) {

    echo "Error: Fallo al conectarse a MySQL debido a: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    die( "Error: " . $mysqli->connect_error . "\n");

}
$excesoDias = $_SESSION['cliente'][1];

if(!isset($_REQUEST['id_abm_premios']))	die("No tengo <b>id_abm_premios</b>");
define('IDP',$_REQUEST['id_abm_premios']);



 $sql = "SELECT p.id_abm_premios,
	p.nombre_premios,
    p.descripcion_premios,
    ROUND(p.puntos_premios,0) as puntos_premios,
    date_format(p.vigencia_premios,'%d/%m/%Y') as vigencia_premios,
    p.id_categoria_abm_premios,
    c.descripcion_categoria_premios,
    p.anulado,
	 date_format(p.vigencia_premios,'%d') as vpd,
	 date_format(p.vigencia_premios,'%m') as vpm,
	 date_format(p.vigencia_premios,'%Y') as vpy,
     f.url_foto,
     f.foto_principal,
     f.descripcion as fdesc,
	 p.saldo_premios
	
FROM sp_abm_premios p
LEFT OUter JOIN sp_categoria_abm_premios c ON p.id_categoria_abm_premios=c.id_categoria_abm_premios
LEFT OUTER JOIN sp_fotos_premios f ON p.id_abm_premios=f.id_abm_premios AND f.foto_principal='Si'
WHERE
 p.anulado='No' 
 AND p.id_abm_premios='".IDP."'
 LIMIT 1";
 
if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}
//$Registro = mysqli_fetch_object($resultado) or die("No tengo resultados $sql");
$Registro = mysqli_fetch_object($resultado) or die("No Lo siento no puedo mostrar el premio ".IDP);

$fotoPrincipal='';
	

$sql = "select  * from sp_fotos_premios where id_abm_premios='".IDP."' AND foto_principal='Si' AND anulado='No'";

if (!$resultado = $mysqli->query($sql)) {

    echo "Error: La ejecución de la consulta falló debido a: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    die( "Error: " . $mysqli->error . "\n");
    
}

$fotoPrincipal .= '<div class="foto-detalle" >';
while ($fotos = mysqli_fetch_object($resultado)) {
    // arreglar el link de la foto
    $a = explode(".",$fotos->url_foto);
    $urlGrande = $a[0] . "." . $a[1] . "." . $a[2] . "h" . "." . $a[3];
	$urlMediana = $a[0] . "." . $a[1] . "." . $a[2] . "m" . "." . $a[3];
	$urlChica = $a[0] . "." . $a[1] . "." . $a[2] . "t" . "." . $a[3];
    $fotoPrincipal .='<img src="'.$urlChica.'" alt="'.$fotos->descripcion.'" />'.PHP_EOL;	
    //print_r($fotos->url_foto);

    }
$fotoPrincipal .='</div>';	

?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Catalogo</title> 	
	
	<!--<link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/mobile.css">-->
	<?php	include "javascript.php";?>
        <script type="text/javascript" src="funciones.js"></script>
	<!--<script type="text/javascript" src="../_lib/easyui/jquery.easyui.mobile.js"></script>--> 
        
    <style>
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
        
        
            /* estilos del detalle para canjear.*/
        .foto-detalle{

            /*width: 40%;
            border: 0;
            margin-right: 5px;
            float: left;*/          
            
                    
                /*width: 100%;*/
            height: 180px;
            overflow: hidden;
            margin: 0 0 22px 0;
            background: none;
            display: inline-block; 
            text-align: center;
            max-width: 500px;
            float: left;
            min-width: 300px;
        }
        
        .foto-detalle img{
            font-family: 'latobold';
            height: 180px;
                    font-size: 16px;
                    text-decoration: none;
                    line-height: 22px;
                    cursor: pointer;
                    display: inline-block;
                    width: auto;
        }
        .cuerpo-body{
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
             max-width: 800px;
        }
        
         .titulo{
           /* font-size: 16px;*/
            font-family: 'OpenSans-SemiBold', sans-serif;
            font-size: 1.1rem;
            color: #737882;
            width: 100%;
            float: left;
            position: relative;
            min-height: 1px;
            
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        .detalle{
            text-overflow: ellipsis;
            
            font-family: 'OpenSans-SemiBold', sans-serif;
            font-size: 0.8rem;
            color: #737882;           
            box-sizing: border-box;
           /* overflow: hidden;*/
        }
        .categoria {
            text-overflow: ellipsis;
            
            font-family: 'OpenSans-SemiBold', sans-serif;
            font-size: 0.7rem;
            color: #737882;           
            box-sizing: border-box;
            
            padding-top: 10px;
            padding-bottom: 10px;
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
            width: 100%;
    float: left;
        }
        .puntos span{
            text-align: right;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            line-height: 22px;
            color: #737882;
            position:relative;
/*            right: 0px;
            top: 12px;*/
            box-sizing: border-box;
        }
      .saldo{
            text-align: right;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 1rem;
            line-height: 22px;
            color: #737882;
            /*width: 100%;*/
            box-sizing: border-box;
            width: 100%;
    float: right;
        }
        .saldo span{
            text-align: right;
            font-family: 'OpenSans-Regular', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            line-height: 22px;
            color: #737882;
            position:relative;
/*            right: 0px;
            top: 12px;*/
            box-sizing: border-box;
        }  
        .caja-canje{
            text-overflow: ellipsis;
            
            font-family: 'OpenSans-SemiBold', sans-serif;
            font-size: 0.8rem;
            color: #737882;           
            box-sizing: border-box;
        } 
        .footer-canje{
             padding-top: 5px;
            padding-bottom: 5px;
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
        .btn{}
        .btn-primary{}
        .btn-default{}
        </style>

</head>	


 
  <div class="easyui-navpanel">
        <header>
            <div class="m-toolbar">
                <div class="m-title"><div id="titulo"></div></div>
                <div class="m-left">
                    
                    <a href="<?php echo getenv("HTTP_REFERER");?>" class="easyui-linkbutton " data-options="plain:true,outline:true,back:true,animation:'pop',direction:''"><i class="fas fa-chevron-left fa-lg "></i> Volver</a>
                </div>
                <div class="m-right" >
                 
				  <!-- Derecho --> 
                  <a href="#" class="easyui-linkbutton" data-options="plain:true" onClick="javascript:$('#dlg1').panel('close');$('#panelCanasta').panel('open');Carting();">
                                               
                        <span class="fa-stack" style="font-size:14px;">
                           
                            <i class="fas fa-heart fa-stack-2x" style="color:Tomato"></i>
                            <strong  id="totalCanje" class="fa-stack-1x fa-inverse">-</strong>
                      </span>
                      
					   
                  </a>
                </div>
            </div>
        </header>
    <div class="cuerpo-body" id="panelcentral">
    <div class="categoria " ><?php echo $Registro->descripcion_categoria_premios ?></div>
      
 <?php echo $fotoPrincipal;?>
 

 <div class="ficha-detalle">
     <div class="titulo"><?php echo $Registro->nombre_premios?></div>	
      <div class="puntos"><?php echo $Registro->puntos_premios ?> <span>ptos</span></div>
      <div class="detalle"><?php echo $Registro->descripcion_premios ?></div>                   
     
      <div class="caja-canje">
       <div class="vencimiento">Vto: <?php echo $Registro->vigencia_premios ?> </div>
        <div><input type="text" id="cantidad" data-options="label:'Cantidad'"> x <?php echo $Registro->puntos_premios ?> <span> puntos</span> 
        
    </div>
        <div id="ctotal" ></div>
      </div>
      
     <div class="footer-canje"> 
	 <?php if(isset($excesoDias['exceso'])&&$excesoDias['exceso']==1):?>
		<div style="font-weight:bolder;color:tomato;width:100%;float:left;font-size:1.2em;padding:5px;">No puede canjear, por exceso en dias.</div>
<?php endif;?>
<?php if(!isset($excesoDias['exceso'])||isset($excesoDias['exceso'])&&$excesoDias['exceso']==0):?>	
        <a href="javascript:AgregarValorSW('<?php echo $Registro->nombre_premios?>')" class="easyui-linkbutton c6" data-options="" style="width:100%"><i class='fas fa-check fa-lg'></i> Agregar </a>
          <!--<a href="javascript:AgregaValor()" class="easyui-linkbutton " data-options="" style="width:100%"><i class='fas fa-heart fa-lg'></i> Agregar </a>-->
        <!--<a href="javascript:Consolidar()" class="easyui-linkbutton " data-options="disabled:true,animation:'pop',direction:''" style="width:99%"><i class='fas fa-check fa-lg'></i> Confirmar Canjes</a>-->  
		<?php endif;?>
    </div>
 </div> 
  
 

</div>

<!--<div class='modal-footer'>	

</div>-->
<div id="dlg2" class="easyui-panel" style="padding:20px 6px;" >

    <!--Agregar el id_ de la tabla para eliminar luego actualizar -->
    <div id="imgseleccionada"></div>
    <div id="ccantidad"></div>

</div>	
<div class="dialog-button" id='footerdlg2'>
    <a href="javascript:Seleccionar()" class="easyui-linkbutton" style="width:100%;height:35px" >Quitar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlg2').panel('close');$('#panelCanasta').panel('open')">Cerrar</a>

</div>

	
	
<div id="dlg1"  style="padding:20px 6px;">
    <div style="margin-bottom:10px">
        <div id="contenidoDialogo"></div>

    </div>        
</div>
<div class="dialog-button" id="bajodlg1">
    <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlg1').panel('close');$('#panelcentral').panel('open');"> <i class="fas fa-angle-right fa-lg fa-fw"></i> Continuar</a>
</div>

	
	
<!-- canasta -->	
<div id="panelCanasta">
    <table id="Canastilla">
        <thead>
                            <tr>
                                <th data-options="field:'ID',hidden:true">ID</th>
                                <th data-options="field:'Premio',align:'left',width:200">Premio</th>
                                <th data-options="field:'Cantidad',align:'center'">Cant</th>
                                <th data-options="field:'Puntos',align:'center'">Ptos</th>
                                <th data-options="field:'Total',align:'right'">Total</th>
                            </tr>
                        </thead>
    </table>

    <div id="PieCanastilla"><center><img src="carrello.gif" height='100%' /></center></div>
    <div id="panelCanastaPie">
        <center>
                <!--<a href="javascript:void(0)" class="easyui-linkbutton" style="width:45%;" onclick="Consolidar()" data-options="toggle:true,group:'g2',plain:true"><i class="fas fa-check fa-fw fa-lg"></i> Canjear</a>-->
            <a href="javascript:void(0)" class="easyui-linkbutton c1" style="width:90%;height:35px" onclick="consolidar_canje()" data-options="group:'g2',plain:true"><i class="fas fa-check fa-fw fa-lg"></i> Canjear</a> <a href="javascript:void(0)" class="easyui-linkbutton" style="width:45%;" onclick="consolidar_canje()" data-options="toggle:true,group:'g2'"><i class="fas fa-check fa-fw fa-lg"></i> Canjear</a>
            <!--<a href="javascript:void(0)" class="easyui-linkbutton" style="width:45%;" onclick="javascript:$('#panelCanasta').panel('close');$('#panelcentral').panel('open')" data-options="toggle:true,group:'g2',plain:true"><i class="fas fa-times fa-fw fa-lg"></i> Cancelar</a>-->
        </center>
    </div>

</div>

<!--spinner admNET-->
<div id="spinner" class="spinnerAdm" style="display:none;">
    <div class="centro">
        <img src="../_img/logo-administranet-ecommerce.png">   
        <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
    </div>
</div>    
<!--fin spinner-->

<!--inicio del codigo JAVASCRIPT-->
<script>  
    
const numerito= new Intl.NumberFormat('es-AR',{
    style: 'decimal',                    
    minimumFractionDigits: 0
});  

$( document ).ready(function() {

     ItemsenelCarrito();
});

var cdad=$('#cantidad');
var maximo = 0;


$.ajax({
    async: false,
    timeout: 10,
    data: {
        "articulo" : '<?php echo IDP;?>'
        },
    dataType: 'json',
    url: "./json/sp_ab_premios_json.php",
        beforeSend: function() {
        // setting a timeout
        //$('#spinner').show('fast');
    }
})
.done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         console.log( "La solicitud se ha completado correctamente." );
         console.log({data});
		 console.log(data.rows[0].saldo_premios);
		 if(data.rows[0].saldo_premios){
			maximo = data.rows[0].saldo_premios; 
		 }
		// maximo = data.
		

		
     }
 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
        // lista.text( "La solicitud a fallado: " +  textStatus);
     }
})
.complete(function(){
   // $('#spinner').hide('fast');
    }
);

$('#ctotal').text('Disponible: '+maximo);
var desactivado=false;
var cantidades=1;
if(maximo<1){
desactivado=true;	
cantidades=0;
}

cdad.numberbox({
    min:1,
    precision:0,
	value:cantidades,
	required: true,
	disabled:desactivado,
	max: maximo
});



    
let Principal = $('#panelcentral');
let ventana = $('#panelCanasta');

ventana.panel({
    width:'100%',
    
	closable: false,
	modal: true,
        title:'<i class="fas fa-gift fa-lg fa-fw"></i> Tu canje  <a href="javascript:void(0)" style="color:inherit;float:right;text-decoration:none;" onclick="javascript:$(\'#panelCanasta\').panel(\'close\');$(\'#panelcentral\').panel(\'open\');" data-options="group:\'g2\',plain:true"><i class="fas fa-times "></i> Cerrar</a>',
	openAnimation: 'fade',
	closeAnimation: 'fade',
	loadingMessage: 'Cargando...',
	closed: true,
	//footer:'#panelCanastaPie',
	maximized: true,
	onOpen: function(){
	//	Principal.panel('close');
	},
	onClose: function(){
	//	Principal.panel('open');
	}
}); 



Principal.panel({
    width:'100%',
	openAnimation: 'fade',
	closeAnimation: 'fade',
	loadingMessage: 'Cargando...'
}); 


let dlg2 = $('#dlg2');

dlg2.panel({
    width:'100%',
	closable: true,
	closed:true,
	footer: '#footerdlg2',
	openAnimation: 'fade',
	closeAnimation: 'fade',
	loadingMessage: 'Cargando...',
	onOpen: function(){
	//	Principal.panel('close');
		//$('#dlg1').panel('close');
		//Principal.panel('close');
	},
	onClose: function(){
	//	Principal.panel('open');
	}
}); 


var dlg1 = $('#dlg1');
dlg1.panel({
    width:'100%',
	footer: '#bajodlg1',
	closable: true,
	closed:true,
	title:'Premio agregado!',
	openAnimation: 'fade',
	closeAnimation: 'fade',
	loadingMessage: 'Cargando...',
	onOpen: function(){
	//	Principal.panel('close');
		
	},
	onClose: function(){
	//	Principal.panel('open');
	}
}); 

    
function AgregarValorSW(nombrePremio){
    
    var cantCanje= $('#cantidad').numberbox('getValue');
    var puntosPremio = $('.puntos').text();
    
   
    Swal.fire({
            position: 'top',
            title: "Está Seguro?",
            html: "Agregará: <strong>"+nombrePremio+ "<strong><br>  Cant: <strong>" + cantCanje +"</strong> un x <strong>" + puntosPremio + "<strong><br>  al carrito!",
            icon: "warning",
            showConfirmButton:true,          
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText:'<i class="fas fa-check fa-lg"></i> Si!',
            cancelButtonText:'<i class="fas fa-times fa-lg"></i> No'
            
        })
        .then((result) => {
            if (result.value) {
              let articulo=<?php echo $_REQUEST['id_abm_premios'];?>;

                let puntos=<?php echo $puntos;?>;

                $.ajax({
                   async: false,
                   timeout: 10,
                    data: {
                        "articulo" : articulo,
                        "puntos" : puntos,
                        "agregar" : $('#cantidad').numberbox('getValue')
                        },
                        dataType: "json",
                    url: "./json/canasta.php",
                            beforeSend: function() {
                      //  $('#panelcentral').panel('close');
                       // lista.html("<img src='../_lib/sw/loader.gif' >");
                      
                    }
                })
                .done(function( data, textStatus, jqXHR ) {
                    if ( console && console.log ) {
                        //alert("premio agregado correctamente");
                        console.log( "La solicitud se ha completado correctamente." );
                       

            //                   $('#contenidoDialogo').html('<p>'+data.Mensaje+"</p>\n");
            //
            //                   $('#dlg1').panel('open');
                        if(data.estado==="ok"){
                            
                               Swal.fire({
                                   position: 'top',
                                   title: "Hecho!", 
                                   text: "Premio agregado al carrito", 
                                   icon:"success"
                                })  
                                .then((value) => {
                                   location.href="catalogo.php";
                                });
                        }
                        if(data.estado==="error"){
                            Swal.fire("Oops!", " "+data.Mensaje, "error")
                                .then((value) => {
                                   //location.href="catalogo.php";
                                });
                        }
                    }
                })
                .fail(function( jqXHR, textStatus, errorThrown ) {
                    if ( console && console.log ) {
                        console.log( "La solicitud a fallado: " +  textStatus);
                        Swal.fire("Oops, ocurrió un problema!", textStatus,"error" );
                    }
               })
               .complete(function(){
                  // $('#spinner').hide('fast');
               });

                ItemsenelCarrito();


        //    swal("Poof! Your imaginary file has been deleted!", {
        //      icon: "success",
        //    });
          } else{
              // chau chau

          }
        });
    
    
}


function AgregaValor(){
		
    Principal.panel('close');
    dlg2.panel('close');	
    let articulo=<?php echo $_REQUEST['id_abm_premios'];?>;

    let puntos=<?php echo $puntos;?>;

    $.ajax({
       async: false,
       timeout: 10,
        data: {
            "articulo" : articulo,
            "puntos" : puntos,
            "agregar" : $('#cantidad').numberbox('getValue')
            },
            dataType: "json",
        url: "./json/canasta.php",
                beforeSend: function() {
          //  $('#panelcentral').panel('close');
           // lista.html("<img src='../_lib/sw/loader.gif' >");
           $('#spinner').show('fast');
        }
    })
    .done(function( data, textStatus, jqXHR ) {
        if ( console && console.log ) {
            //alert("premio agregado correctamente");
            console.log( "La solicitud se ha completado correctamente." );
             $('#spinner').hide('fast');  


//                   $('#contenidoDialogo').html('<p>'+data.Mensaje+"</p>\n");
//
//                   $('#dlg1').panel('open');

                   Swal.fire("Hecho!", "Premio agregado al carrito", "success")
                    .then((value) => {
                       location.href="catalogo.php";
                    });
                   
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


    ItemsenelCarrito();


}


$.ajax({
   

    dataType: "json",
    url: "./json/puntosdeusuarios.php",
	    beforeSend: function() {
        
       // lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
        console.log( "La solicitud se ha completado correctamente." );

        var ptos = Math.round(data.rows[0].saldo_premios);
        $('#titulo').text("Tiene: "+ptos+" ptos");
     }
 })	



	

//$( document ).ready(function() {
	
	let caja = $('#Canastilla');
	let tabla = $('#Canastilla');
	let pietabla = $("#PieCanastilla");
	
function Carting(){
	console.log("Mostrando carting en mostrame.php -------------------------");
	
	//panelCanasta
	Principal.panel('close');
    dlg1.panel('close');

    
        $.ajax({

            url: "./json/canasta.php",
                    beforeSend: function() {

             //  caja.html("<img src='../_lib/sw/loader.gif' >");
             $('#spinner').show('fast');
             
            }
        })
        .done(function( data, textStatus, jqXHR ) {
            if ( console && console.log ) {
                console.log( "Datos obtenidos para cagar Tabla de canasta." );
                console.log({data});
	
                if( data === undefined || data === null || data==="") {
                    console.log("me retiro vicotorioso?");
                    Principal.panel('open');
                   // dlg1.panel('open');
                    $('#panelCanasta').panel('close');
                    return;
                }

                let Objeto = $.parseJSON(data);
                let Columnas = Objeto.columnas;
                let Datos = Objeto.datos;
                        //console.log(Objeto);
                let strr = '';
                if(Objeto.Mensaje)console.log(Objeto.Mensaje);
                if(Datos.length>0){
                 
//                    strr+="<thead><tr>\t";
//                    $.each(Columnas, function( index, value ) {
//                        console.log(index);
//                        if (value==="Total" || value==="Cantidad"){
//                            strr+="<th data-options=\"field:'"+value+"',align:'center'\">"+value+"</th>\t";	 
//                        }else{
//                            strr+="<th data-options=\"field:'"+value+"'\">"+value+"</th>\t";	 
//                        }
//
//                    });
//                    
//                    strr+="</tr></thead>\n";

                    //console.log(Datos);
                    var porcion;
                    var Acumulador = 0;
                    $.each(Datos, function( index, value ) {
                            console.log(Datos[index]);
                            porcion=Datos[index].Total;
//                            porcion = porcion.toString();
//                            porcion= porcion.replace(/<[^>]*>/g, '');
                            porcion = Number(porcion);
                            Acumulador+=porcion;

                    });
                    var xpuntos = 0;
                    $.ajax({
                        async: false,
                        dataType: 'json',
                        url: "./json/puntosdeusuarios.php",
                        success: function( resultado, textStatus, jqXHR ) {

                            xpuntos=resultado.rows[0].saldo_premios;

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
                     console.log('mostrame php abriendo el panel canasta......');
                     $('#PanelCanasta').panel('open');
                }
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
       // $('#panelCanasta').open();
 //strr+="<div id='abajoCarrito' >Total General "+Datos.TotalGeneral+"</div>\n";
 
}
//tabla.datagrid('reload');

//    });
	
function Comela(id){


		$.ajax({
   
    data: {
			"eliminar" : id
			},
    url: "./json/canasta.php",
	    beforeSend: function() {
        // setting a timeout
        //lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         console.log( "La solicitud se ha completado correctamente." );
		


		
     }
 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
         lista.text( "La solicitud a fallado: " +  textStatus);
     }
});
		
}
	
	
function formatPrice(val,row){
	//return '<span style="color:red;">('+val+')</span>';
	return '<img src="'+val+'" width="40px" />';
	return val;

}




function Seleccionar(){
	let fila = $('#Canastilla').datagrid('getRows');
	//alert("id: "+fila[0].id_abm_premios);
	console.log(fila[0].ID);
	$.ajax({
   
        data: {
                            "eliminar" : fila[0].ID
                            },
        url: "./json/canasta.php",
                beforeSend: function() {
            // setting a timeout
            $('#imgseleccionada').html("<img src='../_lib/sw/loader.gif' >");
        }
    })
     .done(function( data, textStatus, jqXHR ) {
         if ( console && console.log ) {

    location.reload(true);
    //$('#dlg2').dialog('close');

         }
     })
     .fail(function( jqXHR, textStatus, errorThrown ) {
         if ( console && console.log ) {
             console.log( "La solicitud a fallado: " +  textStatus);
             lista.text( "La solicitud a fallado: " +  textStatus);
         }
    });
	
	
	
}

function AbrirDialogo(){
	
	let fila = $('#Canastilla').datagrid('getSelected');
	console.log(fila);
	html = '<h1>'+fila.Premio+'</h1>';
	html += 'Cargando imagenes....';
	$('#imgseleccionada').html(html);
	html = '<h1>'+fila.Premio+'</h1>';
	let prefe = "";
		$.ajax({
			dataType: "json",
			data: { id: fila.ID },
			url: "./json/imagenproducto.php",
			beforeSend: function() {
				
			}
})
 .done(function( data, textStatus, jqXHR ) {
   
console.log("fotos: ");
console.log(data);
 $.each(data, function( index, value ) {
	 
	html+='<img src="'+data[index].url+'" width=100 />'; 
	console.log('cargando.. '+data[index].url);
 });

		$('#imgseleccionada').html(html);
     
 });
	

	
	
	
	$('#dlg2').panel('open');
	$('#panelCanasta').panel('close');
	
}






function ItemsenelCarrito(){
var tc = $('#totalCanje');
var url = "./json/cantidadItemsCanasta.php";
						   
	$.ajax({
	   dataType: 'json',
		url: url,
		beforeSend: function() {
		// setting a timeout
		$('#spinner').show('fast');
                tc.hide();
                
		console.log("Consultando items en el carrito");
		},
		success: function( data, textStatus, jqXHR ) {
			tc.text(data);
			tc.hide().fadeIn();
			console.log("Consulta de Items terminada");
			},
		error: function( jqXHR, textStatus, errorThrown ) {
				tc.text(0);
		},
                complete: function(){
                    $('#spinner').hide('fast');
                }
	});
}



        
 </script>
