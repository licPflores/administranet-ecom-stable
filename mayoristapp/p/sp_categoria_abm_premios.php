<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Premios</title>
<?php include "javascript.php";?>
</head>
<body>
 

    <table id="dg" style="width:100%;min-width: 320px;">

    </table>
    <div id="toolbar">
        <a href="javascript:void(0)" class="easyui-linkbutton"  plain="true" onclick="newUser()"><i class="fas fa-plus fa-lg fa-fw"></i>  Nuevo</a>
        <a href="javascript:void(0)" class="easyui-linkbutton"  plain="true" onclick="editUser()"><i class="fas fa-edit fa-lg fa-fw"></i>  Editar</a>
<!--<a href="sp_fotos_premios.php" class="easyui-linkbutton" iconCls="icon-large-smartart" plain="true" style="width:90px">Fotos</a>-->
<a href="sp_ab_premios.php" class="easyui-linkbutton"  plain="true" style="width:90px"><i class="fas fa-gift fa-lg fa-fw"></i>  Premios</a>
		</div>
       <script>
$('#dg').datagrid({
	singleSelect: true,
	idField: 'id_categoria_abm_premios',
	height: 'auto',
	title: 'Categorías de premios',
    url:'json/sp_categoria_abm_premios_json.php',
    toolbar: '#toolbar',
	pagination: true,
    rownumbers: true,
    columns:[[
        {field:'descripcion_categoria_premios',title:'Categoría'},
        {field:'url_foto',title:'Foto',width:'30%'},
        {field:'anulado',title:'Anulado',width:'5%',align:'center'}
    ]],
	onDblClickRow: function(index,data){
		
		var dlg = $('#contenidodialogo');
		var chtml = '<center><h3>'+data.descripcion_categoria_premios+'</h3>';
		chtml = chtml+'<img src="'+data.url_foto+'" height=200 /></center>';
		dlg.empty();
		dlg.html(chtml);
		$('#dialogo').dialog('open').dialog('center').dialog('setTitle','Vista Previa');
		
		
	}
});

/*	
	$.fn.datebox.defaults.formatter = function(date){
	var y = date.getFullYear();
	var m = date.getMonth()+1;
	var d = date.getDate();
	//alert(date);
	return d+'/'+m+'/'+y;

}	*/
	</script>
	<div id="dialogo" class="easyui-dialog" style="width:50%" data-options="closed:true,modal:true,border:'thin'">
	<div id="contenidodialogo"></div>
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="javascript:$('#dialogo').dialog('close')" style="width:90px"><i class="fas fa-times fa-lg fa-fw"></i> Cerrar</a>
	</div>
        <div id="dlg" class="easyui-dialog" style="width:80%" data-options="closed:true,modal:true,border:'thin',buttons:'#dlg-buttons'">
            <form id="fm" method="post"style="margin:0;padding:20px 50px">
                <!--<h3>Categoría</h3>-->
                <div style="margin-bottom:10px">
                    <input name="descripcion_categoria_premios" class="easyui-textbox" required="true" label="Categoría:" style="width:100%">
                </div>




                <div style="margin-bottom:10px">
                    <input type=hidden  name="anulado" id="anulado"  >
                    <input class="easyui-switchbutton" label="Anulado:" id="Estado">
                </div>
                <div style="margin-bottom:10px">
                    <input name="url_foto" id="urlfoto" class="easyui-textbox" required="true" label="Url:" style="width:100%">
                    <div id="cajaimagen" style="padding-left: 12%;padding-top:10px;"></div>		
                </div>
            </form>
            <fieldset>
                <form action="subidor.php" id="subidor" method="post" enctype="multipart/form-data" style="margin:0;padding:20px 50px">
                    <input name="archivo" class="easyui-filebox c2" label="Foto:" style="width:80%"></input>
                    <button type="submit" class="easyui-linkbutton"> <i class='fas fa-cloud-upload-alt fa-lg fa-fw'></i> Subir foto</button>
                </form>
            </fieldset>
        </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="saveUser()" style="width:90px"><i class="fas fa-check fa-lg fa-fw"></i> Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton"  onclick="javascript:$('#dlg').dialog('close')" style="width:90px"><i class="fas fa-times fa-lg fa-fw"></i> Cerrar</a>
    </div>
    <script type="text/javascript">
        var url;
        function newUser(){
            $('#dlg').dialog('open').dialog('center').dialog('setTitle','Nueva categoría');
			
            $('#fm').form('clear');
			$('#Estado').switchbutton('uncheck');
			$('#cajaimagen').empty();
            url = 'json/insert.php?CUAL=sp_categoria_abm_premios';
        }
        function editUser(){
            var row = $('#dg').datagrid('getSelected');
			
            if (row){
				var eee = row.anulado;
				//alert(eee);
				if( row.anulado=='No')$('#Estado').switchbutton('uncheck');
				if( row.anulado=='Si')$('#Estado').switchbutton('check');
				
				$('#dlg').dialog('open').dialog('center').dialog('setTitle','Editar Categoria');
				$('#fm').form('load',row);


			
			
			
			
			
                url = 'json/editar.php?CUAL=sp_categoria_abm_premios&id='+row.id_categoria_abm_premios;
            }
        }
        function saveUser(){
            $('#fm').form('submit',{
                url: url,
                onSubmit: function(){
                    return $(this).form('validate');
                },
                success: function(result){
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

		
		

        $('#Estado').switchbutton({
           
			onText: 'Si',
			offText: 'No',
            onChange: function(checked){
                
				if(checked==true)$('#anulado').val('Si');
				if(checked==false)$('#anulado').val('No');
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
    
    

    </script>
</body>
</html>