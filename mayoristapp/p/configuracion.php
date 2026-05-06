<!doctype html>
<html>

<head>
    <meta charset="UTF-8">  
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Catalogo</title>  
 
	<!--<link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/mobile.css">-->
	<?php	include "javascript.php";?>
	<script type="text/javascript" src="../_lib/easyui/jquery.easyui.mobile.js"></script>


	</head>
<body>
    <div class="easyui-navpanel">
        <header>
            <div class="m-toolbar">
                <span class="m-title" id="mtitle">Configuración</span>
           
		   </div>
			
        </header>

 <div id="tab" class="easyui-accordion" title="Actualizacion de Puntaje" style="width:100%;height:100%;"><!-- inicia tab -->
  <div title="Coeficiente de puntaje" data-options="collapsed:true" style="padding:20px;display:none;">


<div>
    <input type="text" id="importePuntos" class="easyui-numberbox"  style="width:40%;">
    <input type="text" id="vcp" class="easyui-numberbox"  style="width:40%;" >
    
   
</div>   
<p>
    <a id="botonvcp" href="#">   <i class="fas fa-check fa-lg fa-fw"></i> Guardar</a>
</p>
<p>Se determina <b>Cuanto monto($)</b> se necesita para generar <b>puntos</b> de canje.<br>
Es decir si colocamos $100 en el campo Monto, y 1 en el campo puntos , se otorgarán 10 puntos, 
debido a que cada $100 se da 1 punto.</p>

<p><input class="easyui-numberspinner" id="fechita" >
<a id="botonfechita" href="#"><i class="fas fa-check fa-lg fa-fw"></i> Guardar</a></p>
<p>La vigencia , se refiera a la cantidad de meses que duran los puntos ortorgados en una venta.
Si colocamos el valor 3 , los puntos otorgados por una venta el dia de hoy, caducaran en 3(tres) meses.</p>


<script>
$('#botonvcp').linkbutton({	
	onClick: function(){
	
$.ajax({
	url: "./json/sp_configuracion.php",
	dataType: 'json',
	data:{ valor_calculo_puntaje: $('#vcp').numberbox('getValue'),
                monto_puntaje: $('#importePuntos').numberbox('getValue')
        },
	    beforeSend: function() {
        // setting a timeout
      //  lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         
     //    console.log( data.Mensaje );
         alert( data.Mensaje );

		
     }
 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
       
     }
});
	
		
	
	}
});
$('#vcp').numberbox({
    min:0,
    precision:0,
	labelPosition:'top',
	label:'Puntos:',
        
	onChange: function(nuevo,viejo){
	

	
	}
});
$('#importePuntos').numberbox({
    min:0,
    precision:0,
	labelPosition:'top',
	label:'Monto:',
        prefix:'$',
	onChange: function(nuevo,viejo){
	

	
	}
});

$('#botonfechita').linkbutton({
	
	onClick: function(){
		
$.ajax({
	url: "./json/sp_configuracion.php",
	dataType: 'json',
	data:{ vencimiento_puntaje: fechita.numberspinner('getValue')  },
	    beforeSend: function() {
        // setting a timeout
      //  lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         
         //console.log( data.Mensaje );
         alert( data.Mensaje );

		
     }
 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
       
     }
});	
		
	
	
	}		
		
	
})
let fechita = $("#fechita")

fechita.numberspinner({
	min:0,
	label:'Vigencia en meses:',
	labelPosition:'top',
	spinAlign:'horizontal',
	onChange: function(nuevo,viejo){
		
	
	}
});



///Consulta de Valores predeterminado
$.ajax({
	url: "./json/sp_configuracion.php",
	dataType: 'json',
	data:{ 
            valor_calculo_puntaje: $('#vcp').numberbox('getValue'),
            monto_puntaje: $('#importePuntos').numberbox('getValue')  },
	    beforeSend: function() {
        // setting a timeout
      //  lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         
         console.log( data.datos[0] );
		$('#vcp').numberbox('setValue',data.datos[0].valor_calculo_puntaje) ;
                $('#importePuntos').numberbox('setValue',data.datos[0].valor_cada_puntaje) ;
		
     }
 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
       
     }
});






$.ajax({
	url: "./json/sp_configuracion.php",
	dataType: 'json',
	data:{ vencimiento_puntaje: fechita.numberspinner('getValue')  },
	    beforeSend: function() {
        // setting a timeout
      //  lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         
         console.log( data.datos[0].vencimiento_puntaje );
		 let dias = data.datos[0].vencimiento_puntaje;
		
fechita.numberspinner('setValue', dias)
		
     }
 })
 .fail(function( jqXHR, textStatus, errorThrown ) {
     if ( console && console.log ) {
         console.log( "La solicitud a fallado: " +  textStatus);
       
     }
});	
</script>

 
 
</div>
<div title="Actualizacion masiva de puntaje por categoría" data-options="collapsed:true" style="padding:20px;display:none;" >

<h1>Actualizacion masiva de puntos</h1>

 

Filtro


<div id="arriba"></div>
<table id="cc" heigth=100>
<thead>
<tr><th data-options="field:'descripcion_categoria_premios',width:150">Categoria</th>  
<th data-options="field:'id_categoria_abm_premios',width:80">ID</th>  
</tr>
</thead>
</table>
<script>

let cc = $('#cc'); 
let str = "";
            cc.datagrid({
                url: './json/sp_categoria_abm_premios_json.php',
				textField: 'descripcion_categoria_premios',
				multiple: true,
				border:false,
				fitColumns:true,
idField: 'id_categoria_abm_premios',
onSelect: function(index,row){
	
	let str='';
	let vector = cc.datagrid('getSelections');
	$.each(vector, function( index, value ) {
  
  str =  str+value.descripcion_categoria_premios+', ';
  
});

$("#arriba").text(str);	
	
}				
            });

</script>


<br>
<br>
<br>
<input class="easyui-numberbox" label="Porcentaje:" labelPosition="left" id="numerocpremio" style="width:30%;">
<br>
<br>
<br>
<br>
<input id="cpremio" class="easyui-slider" style="width:'50%'">
<br>
<br>
<br>
<script>

$('#cpremio').slider({
    min: -100,
	max: 100,
	showTip: true,
	rule: [-100,'|',-50,'|',0,'|',50,'|',100],
	tipFormatter: function(value){
        return value + '%';
    },    
	onChange: function(nuevo,viejo){
	//	alert('nuevo '+nuevo+' viejo '+viejo);
		$('#numerocpremio').numberbox('setValue', nuevo);
	}
});

$('#numerocpremio').numberbox({
    min:-100,
	max: 100,
    precision:0,
	onChange: function(nuevo,viejo){
	//	alert('nuevo '+nuevo+' viejo '+viejo);
		$('#cpremio').slider('setValue', nuevo);
	}
	
});
var X = $('#cpremio').slider('getValue');
if(X){
	$('#numerocpremio').numberbox('setValue', X);
}else{
	$('#numerocpremio').numberbox('setValue', 0);
}

</script>
<center><a id="btnC" href="#">Actualizar Categoria</a></center>

<script>
$('#btnC').linkbutton({   
	size: 'large',
	width: '100%',
	onClick: function(){
		
	$.messager.confirm({
	title: 'AdministraNET',
	msg: 'Desea cambiar los puntos de <b>'+$("#arriba").text()+'</b> categorias ?',
	fn: function(r){
		if (r){	
		
		
	let valor = $('#cpremio').slider('getValue');
	let strr='';
	let vertor = cc.datagrid('getSelections');
	console.log(vertor)
	$.each(vertor, function( index, value ) {
  
  
  strr =  strr+value.id_categoria_abm_premios+' ';
 
});
		
		
		
		$.ajax({
	url: "./json/porcentaje_premio.php",
	dataType: 'json',
	data:{ Porcentaje: valor,
			Categorias: strr
			},
	    beforeSend: function() {
       
      //  lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         
		console.log('Estado: '+textStatus );
		alert(data.mensaje);
		$('#tt').datagrid('reload');
		//$('#tab').tabs('select',2);	
     }
 });
 
 
 
 
 
 
 }
	}
});
 
	}
});
</script>

</div>
<!-- ahora productos -->

<div title="Actualizacion masiva de puntaje por producto" style="padding:20px;display:none;"
data-options="collapsed:true"
>



<h2>Productos ( Todos y selección)</h2>
<div id="abajo"></div>

<table id="tt" heigth=100>
<thead>
<tr><th data-options="field:'id_abm_premios',width:30">ID</th>  
<th data-options="field:'nombre_premios',width:40">Premio</th>  
<th data-options="field:'descripcion_premios',width:40">Descripcion</th>  
<th data-options="field:'puntos_premios',width:40">Puntos</th>  
</tr>
</thead>
</table>



<script>

let tt = $('#tt'); 
 str = "";
            tt.datagrid({
                url: './json/sp_ab_premios_json.php',
				textField: 'nombre_premios',
				multiple: true,
				border:false,
				fitColumns:true,
				idField: 'id_abm_premios',
				onSelect: function(index,row){
					//$("#abajo").empty();
					let str='';
					let vector = tt.datagrid('getSelections');
					$.each(vector, function( index, value ) {
						//alert( index + ": " + value );
						str =  str+value.descripcion_premios+', ';
						console.log("str: "+str);
						});

					$("#abajo").text(str);	
	
						}				
            });

</script>
 

Pocentaje (Aumenta – Disminuye)

<center>
	<input class="easyui-numberbox" label="Porcentaje:" labelPosition="left" id="numeropremio" style="width:30%;">
	<input id="ppremio" class="easyui-slider" style="width:'70%'">
</center>
<script>

$('#ppremio').slider({
    min: -100,
	max: 100,
	width: '60%',
	showTip: true,
	rule: [-100,'|',-50,'|',0,'|',50,'|',100],
	value:0,
	tipFormatter: function(value){
        return value + '%';
    },
	onChange: function(nuevo,viejo){
	//	alert('nuevo '+nuevo+' viejo '+viejo);
		$('#numeropremio').numberbox('setValue', nuevo);
	}
});

$('#numeropremio').numberbox({
    min:-100,
	max: 100,
    precision:0,
	value:0,
	onChange: function(nuevo,viejo){
	//	alert('nuevo '+nuevo+' viejo '+viejo);
		$('#ppremio').slider('setValue', nuevo);
	}
	
});
$('#numeropremio').numberbox('setValue', $('#ppremio').slider('getValue'));
</script>

<center><a id="btnP" href="#">Actualizar Productos</a></center>



<script>
$('#btnP').linkbutton({
    
	size: 'large',
	width: '100%',
	onClick: function(){
	 valor = $('#ppremio').slider('getValue');
	 strr='';
	let vvv = tt.datagrid('getSelections');
	
	$.each(vvv, function( index, value ) {
  
  
  strr =  strr+value.id_abm_premios+' ';
 
});
		



	$.messager.confirm({
	title: 'AdministraNET',
	msg: 'Desea cambiar los puntos de <b>'+$("#abajo").text()+'</b> premios ?',
	fn: function(r){
		if (r){	
		
		
		
		
		$.ajax({
	url: "./json/porcentaje_premio.php",
	dataType: 'json',
	data:{ Porcentaje: valor,
			Productos: strr
			},
	    beforeSend: function() {
       
      //  lista.html("<img src='../_lib/sw/loader.gif' >");
    }
})
 .done(function( data, textStatus, jqXHR ) {
     if ( console && console.log ) {
         
		console.log( textStatus );
		console.log( data.mensaje );
		alert(data.mensaje)
		tt.datagrid('reload');
     }
 });
 
 
 
 }
	}
});
 
 
 
 
	}
});
</script>

</div>
</div><!-- inicia tab -->
</body>    
</html>