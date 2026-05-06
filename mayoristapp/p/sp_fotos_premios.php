<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Premios</title>
<?php include "javascript.php";?>
<script type="text/javascript" src="datagrid-filter.js"></script>
</head>
<body>
 

    <table id="dg" style="width:100%">

    </table>
    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton"  plain="true" onclick="newUser()"><i class='fas fa-plus fa-lg fa-fw'></i> Nuevo</a>
        <a href="javascript:void(0)" class="easyui-linkbutton"  plain="true" onclick="editUser()"><i class='fas fa-edit fa-lg fa-fw'></i> Editar</a>
    <!--<a href="sp_ab_premios.php" class="easyui-linkbutton" iconCls="icon-ok" plain="true" style="width:90px">Premios</a>-->
    <!--<a href="sp_categoria_abm_premios.php" class="easyui-linkbutton" iconCls="icon-ok" plain="true" style="width:150px">Categoria</a>-->
	 <?php include "buscadorCategoria.php";?>
   </div>
       
        <div id="dialogo" class="easyui-dialog" style="width:50%" data-options="closed:true,modal:true,border:'thin'">
            <div id="contenidodialogo"></div>
            <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="javascript:$('#dialogo').dialog('close')" style="width:90px"> <i class='fas fa-times fa-lg fa-fw'></i>Cerrar</a>
        </div>
        
        
     <!--EDICION DE LAS FOTOS-->   
    <div id="dlg" class="easyui-dialog" style="width:80%;top:0px" data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
        <form id="fm" method="post"style="margin:0;padding:20px 50px">
            
            <div style="margin-bottom:10px">
			<input id="id_abm_premios" name="id_abm_premios"  style="width:100%">
            </div>		
			
            <div style="margin-bottom:10px">
                <input name="descripcion" class="easyui-textbox" label="Descripción:" labelPosition="top" multiline="true" style="width:100%">
            </div>
			
			
			<div style="margin-bottom:10px">
			<input type=hidden  name="foto_principal" id="foto_principal"  >
                <input class="easyui-switchbutton" label="Defecto:" id="fp">
            </div>
		 	
			<div style="margin-bottom:10px">
			<input type=hidden  name="anulado" id="anulado"  >
                <input class="easyui-switchbutton" label="Anulado:" id="Estado">
            </div>
		<div style="margin-bottom:10px">
				<input name="url_foto" id="urlfoto" class="easyui-textbox" required="true" label="Url Fotos:" style="width:100%">
				 <div id="cajaimagen" style="padding-left: 12%;padding-top:10px;"></div>

			</div>	

        </form>
		<fieldset >
		
		<form action="subidor.php" id="subidor" method="post" enctype="multipart/form-data" style="margin:0;padding:5px 5px">
			<input name="archivo" class="easyui-filebox" label="Foto:" style="width:80%"></input>
			<button type="submit" class="easyui-linkbutton" ><i class='fas fa-cloud-upload-alt fa-lg fa-fw'></i> Subir foto</button>
		</form>
                </fieldset>

    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="saveUser()" style="width:90px"><i class='fas fa-check fa-lg fa-fw'></i>Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="javascript:$('#dlg').dialog('close')" style="width:90px"><i class='fas fa-times fa-lg fa-fw'></i>Cerrar</a>
    </div>
    <script type="text/javascript">
        
        $('#dg').datagrid({
            singleSelect: true,
            idField: 'id_fotos_premios',
            //height: 'auto',
            title: 'Fotos de premios',
            url:'json/sp_fotos_premios_json.php',
            toolbar: '#toolbar',
            pagination: true,
            autoRowHeight:true,
//            rownumbers: true,
            nowrap:false,
            columns:[[
                {field:'dpremio',title:'Premio',width:'40%'},
                {field:'url_foto_link',title:'Imagen',width:'15%',align:'center'},
                {field:'url_foto',title:'Link',hidden:true},
                {field:'foto_principal',title:'Principal',width:'10%',align:'center'},
                {field:'nCategoria',title:'Categoria'},
                {field:'fecha_creacion',title:'Fecha'}
                
            ]],
        defaultFilterOperator: 'beginwith',
	onDblClickRow: function(index,data){
		
		var dlg = $('#contenidodialogo');
		var chtml = '<center><h3>'+data.descripcion+'</h3>';
		chtml = chtml+'<h4>'+data.dpremio+'<br />';
		chtml = chtml+'Principal: '+data.foto_principal+'</h4>';
		chtml = chtml+'<h5>Categoria: '+data.nCategoria+'</h5>';
		chtml = chtml+'<img src="foto.php?catp=1&mini=2&url='+data.url_foto+'" /></center>';
		dlg.empty();
		dlg.html(chtml);
		$('#dialogo').dialog('open').dialog('center').dialog('setTitle','Vista Previa');
		
		
	}
        

});

/*	
	onDblClickRow: function(index,data){
		
		var dlg = $('#contenidodialogo');
		var chtml = '<center><h3>'+data.descripcion_categoria_premios+'</h3>';
		chtml = chtml+'<img src="'+data.url_foto+'" height=200 /></center>';
		dlg.empty();
		dlg.html(chtml);
		$('#dialogo').dialog('open').dialog('center').dialog('setTitle','Vista Previa');
		
		
	}


}	
*/
       $(function(){
            var dg = $('#dg');
            dg.datagrid('enableFilter');
            
        });
	
        var url;
        function newUser(){
            $('#dlg').dialog('open').dialog('center').dialog('setTitle','Nueva Foto');
			
            $('#fm').form('clear');
            $('#Estado').switchbutton('uncheck');
            $('#cajaimagen').empty();
            url = 'json/insert.php?CUAL=sp_fotos_premios';
        }
        
        
        
        function editUser(){
            var row = $('#dg').datagrid('getSelected');
			
            if (row){
                console.log({row});
                var eee = row.anulado;
                //alert(eee);
                $('#id_abm_premios').combogrid('setValue', row.id_abm_premios);
                if( row.anulado=='No')$('#Estado').switchbutton('uncheck');
                if( row.anulado=='Si')$('#Estado').switchbutton('check');
                if( row.foto_principal=='No')$('#fp').switchbutton('uncheck');
                if( row.foto_principal=='Si')$('#fp').switchbutton('check');
                $('#cajaimagen').empty();
                $('#cajaimagen').html('<img src="foto.php?catp=1&mini=2&url='+row.url_foto+'"  />');
                $('#dlg').dialog('open').dialog('center').dialog('setTitle','Editar Foto');
                $('#fm').form('load',row);
                url = 'json/editar.php?CUAL=sp_fotos_premios&id='+row.id_fotos_premios;
            }
        }
        
        
        function saveUser(){
            $('#fm').form('submit',{
                url: url,
                onSubmit: function(){
                    return $(this).form('validate');
                },
                success: function(result){
                    console.log(result);
                    var re = eval('('+result+')');
					
                    if (re.resultado){
                        alert("todo bien");
//                        $.messager.show({
//                            title: 'Mensaje',
//                            msg: re.resultado
//                        });
                        
                    } else {
        //nada
                    }
                    //$('#dlg').dialog('close');        // close the dialog
                    $('#dg').datagrid('reload');    // reload the user data
                     $('#dlg').dialog('close');
                }
            });
        }

		
		

        $('#Estado').switchbutton({
           
			onText: 'Si',
			offText: 'No',
            onChange: function(checked){
                
				if(checked==true)$('#anulado').val('Si');
				if(checked==false)$('#anulado').val('No');
            }
        });
		
        $('#fp').switchbutton({
           
			onText: 'Si',
			offText: 'No',
            onChange: function(checked){
                
				if(checked==true)$('#foto_principal').val('Si');
				if(checked==false)$('#foto_principal').val('No');
            }
        });
	
	    
    
            $('#subidor').form({
                success:function(data){
                    var re = jQuery.parseJSON(data);
                    console.log(re);
                    if(re.msg)alert(re.msg);
                    if(re.data.link){
                           $('#urlfoto').textbox('setValue', re.data.link);
                            $.messager.show({
                    title: 'Mensaje',
                    msg: 'Completado '+re.data.link
                        });
					$('#cajaimagen').empty();
					$('#cajaimagen').html('<img src="'+re.data.link+'" height="50px" />');
					};
					$.messager.progress('close');

					
                },
				onSubmit: function(){
 
						 $.messager.progress({
							title: 'Enviando Imagen',
							msg: 'Espere...',
						});
						

						}
            });
    
 
 $('#id_abm_premios').combogrid({
    url:'json/sp_ab_premios_json.php',
	idField:'id_abm_premios',
    textField:'nombre_premios',
	label: 'Premio:',
	labelPosition: 'top',
	required: true,
	prompt:'seleccione un premio...',
	columns:[[
        
        {field:'nombre_premios',title:'Premio',width:'60%'},
	{field:'puntos_premios',title:'Puntos',width:'40%'},
    ]]
});


			 

    </script>
</body>
</html>