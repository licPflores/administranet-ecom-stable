<?php

define('MAXIMO', 4);//Nimero de productos que apareceran
?><div>
		<input  id="categoria" class="easyui-combogrid" style="width:50%;" placeholder="Categoria" data-options="toggle:true,group:'g2',plain:true,prompt:'Categoría'">
		<!--<input id="articulo" class="easyui-combogrid" style="width:40%;" placeholder="Articulo" data-options="toggle:true,group:'g2',plain:true,prompt:'buscar premio...'" >-->
		<!--<input id="limitep" style="width:20%" data-options="toggle:true,group:'g2',plain:true, label:'Puntos'">-->
</div>
<script>
var ptousuarios;
var dg = $('#dg');
// $('#articulo').combogrid({
//    url:'json/sp_ab_premios_json.php',
//	idField:'id_abm_premios',
//    textField:'nombre_premios',
//	columns:[[
//        
//        {field:'nombre_premios',title:'Nombre:',width:'80%'},
//       // {field:'descripcion_premios',title:'Descripcion:',width:'50%'},
//        {field:'puntos_premios',title:'Puntos:',width:'20%'}
//    ]],	
//	onSelect: function(nuevo, ObjetoDeMiDeseo) {
//		buscar();
//		
//	}
//	
//});





 $('#categoria').combogrid({
    url:'json/sp_categoria_abm_premios_json.php',
	idField:'id_categoria_abm_premios',
    textField:'descripcion_categoria_premios',
	columns:[[
        
        {field:'descripcion_categoria_premios',title:'Categoria:',width:'100%'}
    ]],	
	onSelect: function(nuevo, ObjetoDeMiDeseo) {
		//buscar();
		buscar();
		
		
	}
	
	
});

function recargaArticulo(){
//	 $('#articulo').combogrid({
	var g = $('#categoria').combogrid('grid');	// get datagrid object
	var datum =  g.datagrid('getSelected');
	console.log(datum.id_categoria_abm_premios);
	
	var x = $('#articulo').combogrid('grid');
var r = x.datagrid('load', {
    categoria: datum.id_categoria_abm_premios
});
}

//$('#limitep').numberspinner({
//	min: 0,
//	value: 999,
//	onChange: function(nuevo, viejo) {
//		buscar();
//	}
//});



//$.ajax({
//	url: "./json/puntosdeusuarios.php",
//	dataType: 'json',
//	    beforeSend: function() {
//    
//    },
//	success: function( data, textStatus, jqXHR ) {
//     if ( console && console.log ) {
//         console.log( "puntos de usuario obtenidos." );
//		 let ptos = data.rows[0].saldo_premios;
//		 let vencen = data.rows[0].vencimiento;
//       
//		$('#limitep').numberspinner('setValue', ptos);
//		}
//	 },
//	 error: function( jqXHR, textStatus, errorThrown ) {
//     if ( console && console.log ) {
//         console.log( "La solicitud ha fallado: " +  textStatus);
//		 let ptos = 0;
//		 let vencen = '01/01/2099';
//		
//		$('#limitep').numberspinner('setValue', ptos);
//       
//     }
//}
//	
//});




						
function buscar(){
    var maximo = <?php echo MAXIMO;?>;

    //let articulo=$('#articulo').textbox('getValue');


    var g = $('#categoria').combogrid('grid');	// get datagrid object
    var r = g.datagrid('getSelected');
    var categoria;

    if(r){

        categoria = r.id_categoria_abm_premios;

    }else{

        let categoria =0;	
    }

    //let puntosObtenidos=$('#limitep').numberspinner('getValue');


    dg.datagrid('load',{
        "categoria" : categoria,
        //"puntos" : puntosObtenidos
    });

}
</script>