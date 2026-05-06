<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Premios</title>
    <?php include "javascript.php";?>
		<script type="text/javascript" src="datagrid-filter.js"></script>

</head>
<body>
 

    <table id="dg" class="easyui-datagrid" style="width:100%;height:100%">
       
    </table>
    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton"  plain="true" onclick="newUser()"><i class="fas fa-plus fa-lg fa-fw"></i> Nuevo</a>
        <a href="javascript:void(0)" class="easyui-linkbutton"  plain="true" onclick="editUser()"><i class="fas fa-edit fa-lg fa-fw"></i> Editar</a>
		 <a href="sp_fotos_premios.php" class="easyui-linkbutton"  plain="true" style="width:90px"><i class="fas fa-image fa-lg fa-fw"></i> Fotos</a>
		 <!--<a href="sp_categoria_abm_premios.php" class="easyui-linkbutton" iconCls="icon-ok" plain="true" style="width:150px">Categoria</a>-->
		 <?php include "buscador.php";?>
   </div>


	<div id="dialogo" class="easyui-dialog" style="width:60%" data-options="closed:true,modal:true,border:'thin'">
            <div id="contenidodialogo" style="padding:10px;"></div>
            <div id="imagendialogo" style="margin:5px;width: 95%;float:left;"></div>
            <div style="width: 100%;">
                <center>
            <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="javascript:$('#dialogo').dialog('close')" style="width:90px"><i class="fas fa-times fa-lg fa-fw"></i> Cerrar</a>
            </center>
            </div>
	</div>
	
    <div id="dlg" class="easyui-dialog" style="width:700px" data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px" ENCTYPE="multipart/form-data">
<!--            <h3>Nuevo premio</h3>-->
			
            <div style="margin-bottom:10px">
                <input name="nombre_premios" class="easyui-textbox" required="true" label="Premio:" style="width:100%">
            </div>                
            <div style="margin-bottom:10px">
                <input name="descripcion_premios" class="easyui-textbox" required="true" label="Descripción:" labelPosition="top" multiline="true" style="width:100%">
            </div>
            <div style="margin-bottom:10px">
                <input name="puntos_premios" class="easyui-numberbox" required="true" label="Puntos:" style="width:100%">
            </div>
<div style="margin-bottom:10px">
          <input name="sc2" id="sc2" class="easyui-datebox"   prompt="dd/mm/YYYY" label="Vigencia:" data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30" style="width:100%">
        </div>   
        <div style="margin-bottom:10px">
        <!--      <input name="vigencia_premios" id="vigencia_premios" class="easyui-datebox" required="true" label="Vigencia:" style="width:100%"  >
            -->
            <input type=hidden name="vigencia_premios" id="vigencia_premios"  >


        </div>
        <div style="margin-bottom:10px">
            <input id="id_categoria_abm_premios" name="id_categoria_abm_premios"  style="width:100%">
        </div>			 

        <div style="margin-bottom:10px">
            <input type="text" id="saldo_premios" name="saldo_premios" style="width:100%">
        </div>			 



        <div style="margin-bottom:10px">
            <input type=hidden  name="anulado" id="anulado"  >
            <input class="easyui-switchbutton" label="Anulado:" id="Estado">
        </div>
        <div style="margin-bottom:10px">
            <label for="imgprincipal">Foto Principal:</label>    <input type="file" class=""  id="imgprincipal" name="archivo" accept=".png, .jpg, .jpeg" />

        </div>

<!--		<h5>Fecha de Vigencia</h5>
		<div id="sc" class="easyui-calendar" style="width:250px;height:250px;"></div>-->
			

        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="saveUser()" style="width:90px"><i class="fas fa-check fa-lg fa-fw"></i> Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="javascript:$('#dlg').dialog('close')" style="width:90px"><i class="fas fa-times fa-lg fa-fw"></i> Cancelar</a>
       
    </div>
<script type="text/javascript">

$('#imagendialogo').empty();
$('#dg').datagrid({
	singleSelect: true,
	idField: 'id_abm_premios',
	height: 'auto',
	title: 'Lista de premios',
        url:'json/sp_ab_premios_json.php',
        toolbar: '#toolbar',
	pagination: true,
        rownumbers: true,
        fitColumns: true,
        columns:[[
            {field:'nombre_premios',title:'Nombre',width:'42%',align:"left"},
            {field:'descripcion_premios',title:'Descripcion',width:'30%',align:'left',hidden:'true'},
            {field:'puntos_premios',title:'Puntos',width:'10%',align:'center'},
            {field:'vigencia_premios',title:'Vigencia',width:'15%',align:'center'},
            {field:'descripcion_categoria_premios',title:'Categoria',width:'20%',align:'left'},
            {field:'saldo_premios',title:'Saldo',width:'15%',align:'center'}
        ]],
	defaultFilterOperator: 'beginwith',
	onDblClickRow: function(index,datos){
		
		$.ajax({
                        data: {
			"id_abm_premios" : datos.id_abm_premios
			},
			dataType: "json",
                        url: "./json/sp_fotos_premios_json.php",
                        beforeSend: function() {

                            $('#imagendialogo').html("<img src='../_lib/sw/loader.gif' >").show('slow');
                        }
                })
                .done(function( data, textStatus, jqXHR ) {
                    if ( console && console.log ) {
                        console.log( "Se completo la consulta de fotos del premio. "+[{data}]);
                        console.log({data});
                        var imagenesHTML='';
                       var vector = data.rows;
                       vector.forEach(function(element) {
                           
                           if(element.url_foto){
                               if(element.foto_principal=="Si"){
                                    imagenesHTML+='<div style="width:75px;height:90px;float:left;text-align:center;">';
                                    imagenesHTML+='<center><img src="'+element.url_foto+'" style="height:75px;float:left;" title="Principal" /></center>';
                                    imagenesHTML+='<span style="margin-top:80px;background-color:#000;color:#fff;">Principal</span>';
                                    imagenesHTML+='</div>';
                               }else{
                                    imagenesHTML+='<div style="width:75px;height:90px;float:left;text-align:center;">';
                                    imagenesHTML+='<center><img src="'+element.url_foto+'" style="height:75px;float:left;" title="Foto" /></center>';
                                    imagenesHTML+='<span style="margin-top:80px"></span>';
                                    imagenesHTML+='</div>';
                               }
                           }
                       });

                       $('#imagendialogo').empty();
                       //console.log("imagenes htmls"+imagenesHTML});
                       if(imagenesHTML!=""){
                           $('#imagendialogo').html(imagenesHTML).show();
                       }
               //        imagenesHTML="";
                    }
                });
		
		var dlg = $('#contenidodialogo');
		var chtml = '<center><h2>'+datos.nombre_premios+'</h2></center>';
                 chtml += '<h3>Categoría: '+datos.descripcion_categoria_premios+"</h3>\n";
		 chtml += '<p>Descripción:<br>'+datos.descripcion_premios+"</p>\n";
		 chtml += '<p>Puntos: <strong>'+datos.puntos_premios+" pts</strong></p>\n";
		
		
		dlg.empty();
		dlg.html(chtml);
		$('#dialogo').dialog('open').dialog('center').dialog('setTitle','Detalle');
		
		
	}
});


       $(function(){
            var dg = $('#dg');
            dg.datagrid('enableFilter', [{
                field:'puntos_premios',
                type:'textbox'
                
            },{
                field:'saldo_premios',
                type:'textbox'
            }
			,{
                field:'nombre_premios',
                type:'textbox'
            }]);
//        dg.datagrid('defaultFilterOperator',{'beginwith'});
//            dg.datagrid('disableFilter',[{field:'vigencia_premios'}])
        
        });
		



        var url;
        function newUser(){
            $('#dlg').dialog('open').dialog('center').dialog('setTitle','Nuevo Premio');
			
            $('#fm').form('clear');
            $('#Estado').switchbutton('uncheck');
            url = 'json/insert.php?CUAL=sp_ab_premios';
			$('#imgprincipal').show('slow');
        }
        function editUser(){
            var row = $('#dg').datagrid('getSelected');
			$('#imgprincipal').hide('slow');
            if (row){
				//console.log(row);
				var eee = row.anulado;
				//alert(eee);
				$('#saldo_premios').numberbox('setValue',row.saldo_premios);
				if( row.anulado=='No')$('#Estado').switchbutton('uncheck');
				if( row.anulado=='Si')$('#Estado').switchbutton('check');
				
				$('#dlg').dialog('open').dialog('center').dialog('setTitle','Editar Premio');
				$('#fm').form('load',row);

				fechita=new Date(parseInt(row.vpm)+'/'+parseInt(row.vpd)+'/'+row.vpy);


				//$('#sc').calendar('moveTo',new Date(parseInt(row.vpm)+'/'+parseInt(row.vpd)+'/'+row.vpy));
//                                $('#sc2').datebox('moveTo',new Date(parseInt(row.vpm)+'/'+parseInt(row.vpd)+'/'+row.vpy));
                             //   $('#sc2').datebox('setValue',new Date(parseInt(row.vpm)+'/'+parseInt(row.vpd)+'/'+row.vpy));
                                $('#sc2').datebox('setValue',parseInt(row.vpm)+'/'+parseInt(row.vpd)+'/'+row.vpy);
			
			
			
                url = 'json/editar.php?CUAL=sp_ab_premios&id='+row.id_abm_premios;
            }
        }
        function saveUser(){
            $('#fm').form('submit',{
                url: url,
                onSubmit: function(){
                    return $(this).form('validate');
                },
                success: function(result){
                    //console.log(result);
                    var re = eval('('+result+')');
					
                    if (re.resultado){
                        $.messager.show({
                            title: 'Mensaje',
                            msg: re.resultado
                        });
                    } else {
        //nada
                    }
                    $('#dlg').dialog('close');        // close the dialog
                        $('#dg').datagrid('reload');    // reload the user data
                }
            });
        }
        
		
		

$('#sc2').datebox({
   
	onSelect: function(date){
           
		var vigencia = $('#vigencia_premios');
		vigencia.val(date.getDate()+'/'+(date.getMonth()+1)+'/'+date.getFullYear());
	}
});


        $('#Estado').switchbutton({
           
			onText: 'Si',
			offText: 'No',
            onChange: function(checked){
                
				if(checked==true)$('#anulado').val('Si');
				if(checked==false)$('#anulado').val('No');
            }
        });
		
	
 $('#id_categoria_abm_premios').combogrid({
    url:'json/sp_categoria_abm_premios_json.php',
	idField:'id_categoria_abm_premios',
    textField:'descripcion_categoria_premios',
	label: 'Categoria:',
	labelPosition: 'top',
	required: true,
	
	columns:[[
        
        {field:'descripcion_categoria_premios',title:'Categoría',width:'90%'}
    ]]
});




        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }
		
		


$('#saldo_premios').numberbox({
    min:0,
    precision:0,
	label: 'Saldo',
	required: true
});

    </script>
	
	

</body>
</html>