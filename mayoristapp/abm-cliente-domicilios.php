<?php 
//error_reporting('E_ALL');
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];

function devuelve_array($sql,$connV){
    $hacer=mysqli_query($connV,$sql)or die("error ".mysqli_error($connV)." <pre>".$sql."</pre>");
    $vuelta=array();
    while($r=mysqli_fetch_assoc($hacer)){
        $vuelta[]=$r;
    }
    if(empty($vuelta)){
        $vuelta=null;
    }
    return $vuelta;
}

function completa_select($arrValores,$idValue=null,$idFiltro=null){
    // agrego un segundo parametro que seria un filtro 
    $select="";
    foreach($arrValores as $p){
        if(key_exists("idFiltro", $p)){
            $filtro=$p["idFiltro"];
        }else{
            $filtro="";
            
        }    
        if($filtro==$idFiltro && $idFiltro!=null){
            if($idValue && $idValue==$p["id"]){
                $select .='<option value="'.$p["id"].'" selected="selected">'.$p["valor"].'</option>';
            }else{
                $select .='<option value="'.$p["id"].'">'.$p["valor"].'</option>';
            }
        }else{
            if($idValue && $idValue==$p["id"]){
                $select .='<option value="'.$p["id"].'" selected="selected">'.$p["valor"].'</option>';
            }else{
                $select .='<option value="'.$p["id"].'">'.$p["valor"].'</option>';
            }
        }
    }
    return $select;
}
function completa_tabla($arrDomicilios){
   $tabla="<thead>";
   $tabla .="<tr>";
   $tabla .="<th>&nbsp</th>";
   $tabla .="<th>Domicilio</th>";
   $tabla .="<th>Zona</th>";
   
   $tabla .="</tr>";
   $tabla .="</thead>";
   $tabla .="<tbody>";
   foreach($arrDomicilios as $r){
       $tabla .="<tr>";
       $tabla .="<td>";
       $tabla .="<a class='editDomicilio' rel='{$r["id_cliente_domicilio"]}' ";
       $tabla .="title='Editar domicilio' ><i class='fas fa-edit fa-fw fa-2x'></i></a>";
       $tabla .="</td>";
       $tabla .="<td>";
       $tabla .=$r["Calle"]." ".$r["NroCalle"]." - ".$r["Depto"]." - ";
       $tabla .=$r["Provincia"].", ".$r["NombreDepartamento"].", ";
       $tabla .=$r["NombreDistrito"];
       $tabla .="</td><td>";
       $tabla .=$r["nombre_zona"];
       $tabla .="</td>";
       
       $tabla .="</tr>";
   }
   $tabla .="</tbody>";
   return $tabla;
}

/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom =0;

$idCliente=$_GET["id"];
//$idCliente=99645;
$sqlCli = "SELECT cd.id_cliente_domicilio,"
        . "cd.Calle,"
        . "cd.NroCalle,"
        . "cd.Dpto AS Depto,"
        . "cd.IDDistrito,"
        . "di.NombreDistrito,"
        . "cd.CodProvincia,"
        . "p.Provincia,"
        . "cd.IDDepartamento,"
        . "d.NombreDepartamento,"
        . "cd.id_zona,"
        . "z.nombre_zona,"
        . "cd.id_cliente,"
        . "cd.anulado,"
        . "cd.diasContacto,"
        . "cd.id_pais"
        . " FROM cliente_domicilio AS cd  "
        . " LEFT JOIN provincia AS p ON p.CodProvincia=cd.CodProvincia"
        . " LEFT JOIN departamento AS d ON d.IDDepartamento = cd.IDDepartamento"
        . " LEFT JOIN distrito AS di ON di.IDDistrito = cd.IDDistrito"
        . " LEFT JOIN erp_zona AS z ON z.id_zona=cd.id_zona"
        . " WHERE cd.id_cliente=".$idCliente
        . " AND cd.anulado='No'";

$sqlProvincia="SELECT CodProvincia AS id,Provincia AS valor "
        . "FROM provincia "
        . "WHERE provincia.id_pais=1 "
        . "AND provincia.Anulado='No'";
$sqlDepartamento="SELECT IDDepartamento AS id, NombreDepartamento AS valor "
        . "FROM departamento "
        . "WHERE departamento.Anulado='No'";
$sqlDistrito="SELECT IDDistrito AS id, NombreDistrito AS valor "
        . "FROM distrito "
        . "WHERE distrito.Anulado='No'";


$sqlZona="SELECT erp_zona.id_zona AS id, "
        . "erp_zona.nombre_zona AS valor , "
        . "erp_zona.codprovincia AS idFiltro "
        . "FROM erp_zona WHERE erp_zona.anulado='No'";

// datos del cliente
//echo " <pre>".$sqlCli."</pre>";
$hacer = mysqli_query($connV,$sqlCli)or die("error cliente:=> ".mysqli_error($connV)." <pre>".$sqlCli."</pre>");
$cli=array();
while($cc = mysqli_fetch_assoc($hacer)){
    $cli[]=$cc;
}


$lista_prov = completa_select( devuelve_array($sqlProvincia,$connV));
$lista_depto= completa_select( devuelve_array($sqlDepartamento,$connV));
$lista_dist= completa_select( devuelve_array($sqlDistrito,$connV));
$lista_zona = completa_select(devuelve_array($sqlZona,$connV));


$tabla_domicilios = completa_tabla($cli);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>domicilios del cliente | administraNET e-com </title>
     <?php require_once 'cabecera.php';?>
<!--    <link rel='stylesheet' type='text/css' media='screen' href='_css/basic.css'   />
    <script type='text/javascript' src='_lib/jquery.simplemodal.js'></script>-->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>

 /*
  * PARAMETROS
  * @returns {undefined}
  * ============================================================================
  */
 $.extend( $.fn.dataTable.defaults, {
    "language": {
        "emptyTable":     "No data available in table",
        "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
        "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
        "infoFiltered":   "(filtered from _MAX_ total entries)",
        "infoPostFix":    "",
        "thousands":      "",
        "lengthMenu":     "Ver _MENU_ entradas",
        "loadingRecords": "Loading...",
        "processing":     "Processing...",
        "search":         "Buscar:",
        "zeroRecords":    "No matching records found",
        "paginate": {
            "first":      "Primero",
            "last":       "Ultimo",
            "next":       "Siguiente",
            "previous":   "Anterior"
        },
        "aria": {
            "sortAscending":  ": activate to sort column ascending",
            "sortDescending": ": activate to sort column descending"
        }
    },
    "order": [[ 1, "desc" ]]
} );    
/*
     * Modal 
     * @param {type} cuit
     * @returns {Boolean}
     */
//    $.extend($.modal.defaults, {
//	onClose: function(){            
//            $.modal.close();
//            location.reload();
//    }}); 
/*
 * INICIO DE PAGINA
 * @returns {undefined}
 */

 $(document).ready(function(){
    $(document).ajaxStart(function(){
         $("#spinner").show();
       });
    $(document).ajaxComplete(function(){
      $("#spinner").hide();
    });
    $(document).ajaxError(function(){
        $("#spinner").hide();
    });
    /*
     * VALIDA FORMULARIO
     * @returns {undefined}
     */
    function validarFormulario(formulario){
        var textoError="",
            error=0;
        
        if(formulario==="frmAlta"){
            var calle=$("#calleCliente"),
                numero=$("#numeroCliente"),                
                provincia=$("#provinciaCliente"),
                departamento=$("#departamentoCliente"),
                distrito=$("#distritoCliente"),
                zona=$("#zonaCliente"),
                divCartel = $('#cartelNuevo');
        }else{
            var calle=$("#calleCliente"),
                numero=$("#numeroCliente"),                
                provincia=$("#provinciaCliente"),
                departamento=$("#departamentoCliente"),
                distrito=$("#distritoCliente"),
                zona=$("#zonaCliente"),
                divCartel = $('#cartelEdicion');
        }
        textoError+='<div id="alertas-formulario" class="alerta-error">';
        textoError+='<strong>';
        textoError+='<i class="fa fa-warning"></i> Atención! </strong><br>' ;
        //console.log("provincia:=>["+provincia.val() + "]depto:=>[" + departamento.val() +"]distrito:=>["+distrito.val()+ "]zona:=>["+zona.val()+"]");
        if(calle.val()===""){
            textoError +='<span class="texto-alerta">Debe completar la <strong>Calle</strong></span><br>';
                error++;
        }   
        if(numero.val()===""){
            textoError +='<span class="texto-alerta">Debe completar <strong> Numero de Calle</strong></span><br>';
            error++;
        }
        if(provincia.val()===""){
            textoError +='<span class="texto-alerta">Debe seleccionar<strong> Provincia</strong></span><br>';
            error++;
        }
        if(departamento.val()===""){
            textoError +='<span class="texto-alerta">Debe seleccionar<strong> Departamento </strong></span><br>';
            error++;
        }
        if(distrito.val()===""){
            textoError +='<span class="texto-alerta">Debe seleccionar <strong> Distrito</strong></span><br>';
            error++;
        }
        if(zona.val()===""){
            textoError +='<span class="texto-alerta">Debe seleccionar la <strong> Zona </strong></span><br>';
            error++;
        }
        if(error>0){
           // console.log(textoError);
            var divMensaje=document.createElement('div');
                divMensaje.innerHTML = textoError;
                
            swal({
                title: "Hecho!",
                content: divMensaje,
                icon: "success"

              })
            .then((value)=>{
//                                    location.href='listado-clientes.php';
                    //location.href='fin-comprobante.php';
                     return false;
            });    
//            divCartel.html(textoError);
//            divCartel.show();
            return false;
        }else{
            return true;
        }
    }
 
    $('#myTable').DataTable();  
    
    // edicion de domicilio llamada.
    
    $('#myTable tbody').on("click","td a.editDomicilio", function(){
        //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
        var idDomicilio = $(this).attr('rel');
        $.ajax({
                type: 'GET',
                url: 'relay-cliente-domicilio.php',
                data:{
                    "ajax" : "true",
                    "accion" : "traer",
                    "idDomicilio" : idDomicilio
                },
                success: function(response) {
                    var id=$('#idClienteDom'),
                        calle=$('#calleClienteEd'),
                        nroCalle=$('#numeroClienteEd'),
                        deptoN=$('#deptoClienteEd'),
                        hDesde=$("#horaDesdeEd"),
                        mDesde=$("#minutoDesdeEd"),
                        hHasta=$("#horaHastaEd"),
                        mHasta=$("#minutoHastaEd"),
                        tipoVisita=$("#visitaVendedorEd"),
                        pVisita=$("#intervaloVisitaEd"),
                        listaProv = $('#provinciaClienteEd'),
                        listaDpto = $('#departamentoClienteEd'),
                        listaDist = $('#distritoClienteEd'),
                        listaZona= $('#zonaClienteEd');
                    //console.log(response);
                    var vuelta=JSON.parse(response);
                    var datos= vuelta.dom;
                    var prov = vuelta.prov;
                    var depto = vuelta.dep;
                    var dist= vuelta.dist;
                    var zona= vuelta.zona;
                    console.log({datos});
                    //datos domicilio
                    id.val(datos.id_cliente_domicilio);
                    calle.val(datos.Calle);
                    nroCalle.val(datos.NroCalle);
                    deptoN.val(datos.Depto);
                    //las horas.
                    var arrHoraDesde=datos.hora_desde.split(":");
                    var arrHoraHasta=datos.hora_hasta.split(":");
                    hDesde.val(arrHoraDesde[0]);
                    mDesde.val(arrHoraDesde[1]);
                    hHasta.val(arrHoraHasta[0]);
                    mHasta.val(arrHoraHasta[1]);
                    
                    // el tipo de visita.
                   // tipoVisita.on("change",buscaPeriodoVisitaEd);
                    tipoVisita.val(datos.periodicidad_visita_vendedor);
                    
                    // visita
                    // ======================================================
                    
                    $.ajax({ 
                        type: 'GET', 
                        url: 'relay-cliente-domicilio.php', 
                        data: { traeVisita:1,
                                tipoVisita : datos.periodicidad_visita_vendedor}, 
                        dataType: 'json',
                        beforeSend: function(){
                            $('#spinner').show('fast');
                        },
                        success: function (data) { 
                            
                            var listaPeriodo = $('#intervaloVisitaEd'),
                             labelPeriodo =$('#labelIntervaloVisitaEd');    
                            console.log({data});
                            var items = [];                       
                            $.each( data.opc, function( key, val ) {
                               //console.log("val=>:"+$.json.values(val)); 
                                items.push( "<option value='" + val + "'>" + val + "</option>" );
                            });
                            var lista=items.join( "" );
                            console.log(lista);
                            labelPeriodo.html(data.titulo+' <em>*</em>:');  
                            listaPeriodo.empty();
                            listaPeriodo.append(lista);
                             pVisita.val(datos.visita_vendedor_valor);
                            //console.log(labelPeriodo.text());
                            console.log("haciendo el periodo de edicion");

                        },
                        complete: function(){
                            $('#spinner').hide('fast');
                        }
                    });
                    
                   
                    
                    
                    
                   
                    
                    
                    
                    
                    //console.log(datos);
                    /* provincia
                     * ======================================================*/
                     var items = [];
                     
                    $.each( prov, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                   
                        items.push( "<option value='" + key + "'>" + val + "</option>" );
                    });
                    var lista=items.join( "" );
                    //console.log(lista);
                    listaProv.html("");
                    listaProv.append(lista);
                    listaProv.val(datos.CodProvincia);
                    /* deparatmento
                     * ======================================================*/
                    items = [];
                    $.each( depto, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                   
                        items.push( "<option value='" + key + "'>" + val + "</option>" );
                    });
                    var lista=items.join( "" );
                    //console.log(lista);
                    listaDpto.html("");
                    listaDpto.append(lista);
                    listaDpto.val(datos.IDDepartamento);
                    
                    /* distrito
                     * ======================================================*/
                    items = [];
                    $.each( dist, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                   
                        items.push( "<option value='" + key + "'>" + val + "</option>" );
                    });
                    var lista=items.join( "" );
                    //console.log(lista);
                    listaDist.html("");
                    listaDist.append(lista);
                    listaDist.val(datos.IDDistrito);
                    
                    /* zona
                     * ======================================================*/
                    items = [];
                    $.each( zona, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                   
                        items.push( "<option value='" + key + "'>" + val + "</option>" );
                    });
                    var lista=items.join( "" );
                    //console.log(lista);
                    listaZona.html("");
                    listaZona.append(lista);
                    listaZona.val(datos.id_zona);
                    //cambiar cosas de la ventana modal.
                    //$('#modal-mod-domicilio').modal();
                    $('#cabeceraFormulario').hide();
                    $('#contiene-tabla').hide();
                    $('#modal-alta-domicilio').hide();
                    $('#modal-mod-domicilio').show();
                    $('#renglonBotones').hide()
                    
                    
                },
                error: function(x, e) {
                    var s = x.status, 
                    m = 'Ajax error: ' ; 
                    if (s === 0) {
                        m += 'Check your network connection.';
                    }
                    if (s === 404 || s === 500) {
                        m += s;
                    }
                    if (e === 'parsererror' || e === 'timeout') {
                        m += e;
                    }
                    //alert(m);
                    swal("Ooops!","ocurrio algo:"+m,"error");
                }
            });


    });
    
    // cambiar esta funcion... para ver como anda.
//    $("#spinner").bind("ajaxSend", function() {
//        $(this).show();
//    }).bind("ajaxStop", function() {
//        $(this).hide();
//    }).bind("ajaxError", function() {
//        $(this).hide();
//    });
            //activar y desactivar el boton de busqueda rapida.
    
    function resetFormulario(accion){
        if(accion==="alta"){
            var calle=$("#calleCliente"),
                numero=$("#numeroCliente"),
                hDesde=$("#horaDesde"),
                mDesde=$("#minutoDesde"),
                hHasta=$("#horaHasta"),
                mHasta=$("#minutoHasta"),
                tipoVisita=$("#visitaVendedor"),
                pVisita=$("#intervaloVisita"),
                depto=$("#deptoCliente"),                
                provincia=$("#provinciaCliente"),
                departamento=$("#departamentoCliente"),
                distrito=$("#distritoCliente"),
                zona=$("#zonaCliente");
            calle.val("");
            numero.val("");
            depto.val("");
            provincia.val("");
            departamento.val("");
            distrito.val("");
            zona.val("");
            hDesde.val("00");
            mDesde.val("00");
            hHasta.val("00");
            mHasta.val("00");
            tipoVisita.val("No");
            pVisita.val("No");
            
        }
    }
   
    
   
    $('#altaDomicilio').on("click",function(){
        //$('#modal-alta-domicilio').modal();
       
        $('#cabeceraFormulario').hide();
        $('#contiene-tabla').hide();
        $('#modal-alta-domicilio').show();
        $('#modal-mod-domicilio').hide();

        $('#renglonBotones').hide()
    });
    $("#botonVolver").on("click",function(){
        //alert("me fui!");
       
        
        location.href="listado-clientes.php";
    });
    
    $(".botonCerrar").on("click",function(){
        $('#cabeceraFormulario').show();
        $('#contiene-tabla').show();
        $('#modal-alta-domicilio').hide();
        $('#modal-mod-domicilio').hide();
        location.reload();
    });
    
    
    
    /*
     * ALTA DOMICILIO
     * @param {type} data
     * @returns {undefined}
     * =========================================================================
     */
        
    $('#guardaNuevo').on("click",function(){
        var valores = $('#frmAlta').serializeArray();
        var divCartel = $('#cartelNuevo');
        valores.push({name: 'accion', value: 'alta'});
        console.log(valores);
        if(validarFormulario("frmAlta")===false){
            return false;
        }
        $.ajax({
            type:"POST",
            url:"relay-cliente-domicilio.php",
            data:valores,
            success: function(response){
                console.log(response);
               // console.log("fallo "+response.cartel);
                var a=jQuery.parseJSON(response),
                        b="";
                var cartelito="";
//                            console.log("a:=>"+a.cartel);
                if(a.estado==="error"){
                    //alert("todo MAL para los pibes");
//                    cartelito+='<div id="alertas-formulario" class="alerta-error">';
//                    cartelito+='<strong>';
//                    cartelito+='<i class="fa fa-warning"></i> Atención! </strong><br>' ;
//                    cartelito+='<span class="texto-alerta">'+a.cartel+'</span>';
//                    cartelito+='</div>';
                    swal("Atención!",a.cartel,"warning");
//                    divCartel.html(cartelito);
//                    divCartel.show();

                }else{
                    //alert("tudo bien");
                    cartelito+='<div id="alertas-formulario" class="alerta-exito">';
                    cartelito+='<strong>';
                    cartelito+='<i class="fa fa-check-circle fa-lg"></i>' ;
                    cartelito+='<span class="texto-alerta">'+a.cartel+'</span></strong>';
                    cartelito+='</div>';
                    resetFormulario('alta');
//                    divCartel.html(cartelito);
//                    divCartel.show();
                    swal("Hecho!",a.cartel,"success");


    

                }
                // location.href="listado-clientes.php";




            }
        });
    });
        
        
     /*
     * MODIFIACION DE DOMICILIO
     * @returns {undefined}
     * =========================================================================
     */
    
    $('#guardaEdita').on("click", function(){
        var valores = $('#frmEdita').serializeArray();
        var divCartel = $('#cartelEdicion');
            valores.push({name: 'accion', value: 'editar'});
//           console.log(valores);
           $.ajax({
                type:"POST",
                url:"relay-cliente-domicilio.php",
                data:valores,
                success: function(response){
                    console.log(response);
                   // console.log("fallo "+response.cartel);
                    var a=jQuery.parseJSON(response);
                    var cartelito="";
    //                            console.log("a:=>"+a.cartel);
                    if(a.estado=="error"){
                        //alert("todo MAL para los pibes");
//                        cartelito+='<div id="alertas-formulario" class="alerta-error">';
//                        cartelito+='<strong>';
//                        cartelito+='<i class="fa fa-warning"></i> Atención! </strong><br>' ;
//                        cartelito+='<span class="texto-alerta">'+a.cartel+'</span>';
//                        cartelito+='</div>';
//                        divCartel.html(cartelito);
//                        divCartel.show();
                        
                        swal("Atención!",a.cartel,"warning");
                    }else{
                        //alert("tudo bien");
//                        cartelito+='<div id="alertas-formulario" class="alerta-exito">';
//                        cartelito+='<strong>';
//                        cartelito+='<i class="fa fa-check-circle fa-lg"></i>' ;
//                        cartelito+='<span class="texto-alerta">'+a.cartel+'</span></strong>';
//                        cartelito+='</div>';
//                        divCartel.html(cartelito);
//                        divCartel.show();
                        swal("Hecho!",a.cartel,"success");
                        
                    }


                }
            });
        
    });
    // funciones de cambio de provincias.
    /* cambio de provincia*/
    var buscaProvincia = function(){
        var idProvincia=$(this).val();
        var depto = $(this).attr("depto"),
            zona = $(this).attr("zona");    
        
        if(idProvincia!==""){
            var mando={
                "ajax":true,
                "accion":"departamento",
                "idProvincia":idProvincia
            };

            $.getJSON( "relay-cliente-domicilio.php",mando, function( data ) {
                //console.log("vuelta:"+JSON.toString(data));

                var items = [];                
                var tDepto=$("#"+depto);
                /*departamento del cliente*/    
                items.push( "<option value=''>- Departamento -</option>" );
                $.each( data, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                    items.push( "<option value='" + key + "'>" + val + "</option>" );
                });
                var lista=items.join( "" );
                //console.log(lista);
                tDepto.html("");
                tDepto.append(lista);
            });
            var mando={
                "ajax":true,    
                "accion":"zona",
                "idProvincia":idProvincia
            };

            $.getJSON( "relay-cliente-domicilio.php",mando, function( data ) {
                //console.log("vuelta:"+JSON.toString(data));

                var items = [];                
                var tZona=$("#"+zona);
                /*departamento del cliente*/    
                items.push( "<option value=''>- Zona -</option>" );
                $.each( data, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                    items.push( "<option value='" + key + "'>" + val + "</option>" );
                });
                var lista=items.join( "" );
                //console.log(lista);
                tZona.html("");
                tZona.append(lista);
            });
            

        }else{
            console.log("error: Debe seleccionar una provincia.");
        }
    };
    
    // cambio de depto traigo distritos.
    var buscaDistrito = function(){
        var idDepartamento=$(this).val();
        var distrito = $(this).attr("distrito");
        if(idDepartamento!==""){
            var mando={
                "accion":"distrito",
                "idDepartamento":idDepartamento
            };

            $.getJSON( "relay-cliente-rapido.php",mando, function( data ) {
                //console.log("vuelta:"+JSON.toString(data));

                var items = [];                
                var tDist=$("#"+distrito);
                 
                items.push( "<option value=''>- Distrito -</option>" );
                $.each( data, function( key, val ) {
                   //console.log("val=>:"+$.json.values(val)); 
                    items.push( "<option value='" + key + "'>" + val + "</option>" );
                });
                var lista=items.join( "" );
                //console.log(lista);
                tDist.html("");
                tDist.append(lista);
            });                              

        }else{
            console.log("error: Debe seleccionar un Departamento.");
        }
    };
    
    var buscaPeriodoVisita=function(){
        var idTipoVisita=$(this).val();
        var listaPeriodo = $('#intervaloVisita');
        var labelPeriodo =$('#labelIntervaloVisita');
        if(idTipoVisita!==""){               
        
            $.ajax({ 
                type: 'GET', 
                url: 'relay-cliente-domicilio.php', 
                data: { traeVisita:1,
                        tipoVisita : idTipoVisita}, 
                dataType: 'json',
                beforeSend: function(){
                    $('#spinner').show('fast');
                },
                success: function (data) { 
                    console.log({data});
                    var items = [];                       
                    $.each( data.opc, function( key, val ) {
                       //console.log("val=>:"+$.json.values(val)); 
                        items.push( "<option value='" + val + "'>" + val + "</option>" );
                    });
                    var lista=items.join( "" );
                    console.log(lista);
                    labelPeriodo.html(data.titulo+' <em>*</em>:');  
                    listaPeriodo.empty();
                    listaPeriodo.append(lista);
                    //console.log(labelPeriodo.text());
                          

                },
                complete: function(){
                    $('#spinner').hide('fast');
                }
            });
            

        }else{
            //console.log("error: Debe seleccionar un Departamento.");
            swal("Atención!","Debe seleccionar tipo de visita","warning");
        }
        
        
    };
    
   // EDICION
   
   var buscaPeriodoVisitaEd=function(){
       console.log("se activo la edicion de la visita.");
        var idTipoVisita=$(this).val();
        var listaPeriodo = $('#intervaloVisitaEd');
        var labelPeriodo =$('#labelIntervaloVisitaEd');
        if(idTipoVisita!==""){               
        
            $.ajax({ 
                type: 'GET', 
                url: 'relay-cliente-domicilio.php', 
                data: { traeVisita:1,
                        tipoVisita : idTipoVisita}, 
                dataType: 'json',
                beforeSend: function(){
                    $('#spinner').show('fast');
                },
                success: function (data) { 
                    console.log({data});
                    var items = [];                       
                    $.each( data.opc, function( key, val ) {
                       //console.log("val=>:"+$.json.values(val)); 
                        items.push( "<option value='" + val + "'>" + val + "</option>" );
                    });
                    var lista=items.join( "" );
                    console.log(lista);
                    labelPeriodo.html(data.titulo+' <em>*</em>:');  
                    listaPeriodo.empty();
                    listaPeriodo.append(lista);
                    //console.log(labelPeriodo.text());
                          

                },
                complete: function(){
                    $('#spinner').hide('fast');
                }
            });
            

        }else{
//            console.log("error: Debe seleccionar un Departamento.");
              swal("Atención!","Debe seleccionar tipo de visita","warning");
        }
        
        
    };
   
 
    
    
    
    
    $("#provinciaCliente").on("change",buscaProvincia);
    $("#provinciaClienteEd").on("change",buscaProvincia);   
    $("#departamentoCliente").on("change",buscaDistrito);
    $("#departamentoClienteEd").on("change",buscaDistrito);
    $("#visitaVendedor").on("change",buscaPeriodoVisita);
    $("#visitaVendedorEd").on("change",buscaPeriodoVisitaEd);
    //$('#visitaVendedor').on("change",buscaPeriodoVisita);
    
 });
</script>
</head>
<body>
    <div id="wrapper">
        <?php require_once $barra;?>
        <div id="content" > 
			<div class="paneles bg-white">   

				<div class="divFormularios" id="cabeceraFormulario">    
					<div id="titulo" class="formulario">
						<h1>Domicilios adicionales</h1> 
					</div>
				</div>

				<div id="spinner" class="spinner" style="display:none;">
					<img src="_img/logo-administranet-ecommerce.png"/>  
				</div>

				<div id="contiene-tabla" class="tabla-712px">
					<table class="display compact" cellspacing="1" id="myTable">
						<?php echo $tabla_domicilios;?>
					</table>
				</div>

				<div class="renglonBotones" id="renglonBotones" style="text-align: center; width: 97%; padding:1%;">
						<button id="botonVolver" class="botonNuevo grande botonVolver" type="button">
							<i class="fas fa-arrow-left fa-fw fa-lg"></i> Volver
						</button>

						<button class="botonNuevo azul grande"  title="Nuevo" name="altaDomicilio" type="button" id="altaDomicilio" ><i class="fas fa-plus  fa-fw fa-lg"></i> Nuevo</button> 
				</div>

				<div id="modal-alta-domicilio" style="display:none;">
					<div class="divFormularios">    
						<div id="titulo" class="formulario">
							<h1>Nuevo Domicilio</h1> 
						</div>
						
						<div class="cartelCliente" id="cartelNuevo"></div>  

						<form id="frmAlta" name="frmAlta" method="post" >
						
							<div class="renglonForm">
								<label for="calleCliente">Calle<em>*</em>
									<input type="text" id="calleCliente" name="calleCliente"  placeholder="Calle..." required="required">
								</label>
							</div>
							<div class="renglonForm">
								<label for="numeroCliente">Nro<em>*</em>
									<input type="text" id="numeroCliente" name="numeroCliente"  placeholder="Numero de calle..." required="required">
								</label>
							</div>
							<div class="renglonForm">
								<div class="bloque-renglon">
									<label >Horario Entrega <em>*</em></label>
									
									<div class="horarios">
										<select name="horaDesde" id="horaDesde" style="width:20%">
										<?php for($i="0";$i<24;$i++):?>
											<option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
										<?php endfor;?>    
										</select> 
											:
										<select name="minutoDesde" id="minutoDesde" style="width:20%">
											<option value="00">00</option>
											<option value="15">15</option>
											<option value="30">30</option>
											<option value="45">45</option>
											
										</select>
										A
										<select name="horaHasta" id="horaHasta" style="width:20%">
										<?php for($i="0";$i<24;$i++):?>
											<option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
										<?php endfor;?>    
										</select>
											:
										<select name="minutoHasta" id="minutoHasta" style="width:20%">
											<option value="00">00</option>
											<option value="15">15</option>
											<option value="30">30</option>
											<option value="45">45</option>
											
										</select>
										hs
									</div>
								</div>
							</div>
							<div class="renglonForm">
								<label for="visitaVendedor">Visitar:<em>*</em>
									
									<select name="visitaVendedor" id="visitaVendedor">
										<option value="No" selected>No</option>
										<option value="Semanal">Semanal</option>
										<option value="Quincenal">Quincenal</option>
										<option value="Mensual">Mensual</option>
									</select>
								</label>
							</div>
							<div class="renglonForm">
								<div class="bloque-renglon">
								<label for="intervaloVisita" id="labelIntervaloVisita">Cuando:<em>*</em></label>                         
									<select name="intervaloVisita" id="intervaloVisita">
										<option value="No" selected>No</option>                                    
									</select>
								</div>
							</div>
							
							
							<div class="renglonForm">    
								<label for="deptoCliente">Depto
									<input type="text" id="deptoCliente" name="deptoCliente"  placeholder="Numero o letra departamento..." >
								</label>
							</div>
							<div class="renglonForm">                
								<label for="provinciaCliente">Provincia<em>*</em>
									<select name="provinciaCliente" id="provinciaCliente" required="required" depto="departamentoCliente" zona="zonaCliente">
										<option value="">- Provincia -</option>
									<?php echo $lista_prov;?>   
									</select>
								</label>
							</div>
							<div class="renglonForm">
								<label for="departamentoCliente">Depto/Localidad<em>*</em>
									<select name="departamentoCliente" id="departamentoCliente" required="required" distrito="distritoCliente">
										<option value="">- Departamento -</option>
									<?php echo $lista_depto;   ?>
									</select>
								</label>
							</div>
							<div class="renglonForm">
								<label for="distrito">Distrito/Partido<em>*</em>
									<select name="distritoCliente" id="distritoCliente" required="required">
										<option value=""> - Distrito - </option>
									<?php echo $lista_dist;?>    
									</select>
								</label>
							</div>
							<div class="renglonForm">
								<label for="zonaCliente">Zona<em>*</em>
									<select name="zonaCliente" id="zonaCliente" required="required">
										<option value=""> - Zona - </option>
									<?php echo $lista_zona;   ?>
									</select>
								</label>
							</div>

							<input type="hidden" name="idCliente" id="idCliente" value="<?php echo $idCliente;?>">
							
							<div class="renglonForm renglonBotones" style="text-align: center; width: 97%; padding:1%;">
								<button id="botonVolver" class="botonCerrar botonNuevo grande botonVolver" type="button">
									<i class="fas fa-arrow-left fa-fw fa-lg"></i> Volver
								</button>
								<button id="botonVolver" class="botonCerrar botonNuevo grande botonCancelar" type="button" tabindex="16">
									<i class="fas fa-times-circle fa-fw fa-lg"></i> Cancelar
								</button>

								<button class="botonNuevo azul grande" type="button" id="guardaNuevo" ><i class="fas fa-check fa-fw fa-lg"></i> Guardar</button> 
							</div>

						</form>

					</div>
				</div>
				
				<div id="modal-mod-domicilio" style="display:none;position:relative">

					<div class="divFormularios">    
						<div id="titulo" class="formulario">
							<h1>Editar Domicilio</h1> 
						</div>   

						<div class="cartelCliente" id="cartelEdicion"></div>   

						<form id="frmEdita" name="frmEdita" method="post">
							<div class="renglonForm">
								<label for="calleCliente">Calle<em>*</em>
									<input type="text" id="calleClienteEd" name="calleClienteEd"  placeholder="Calle..." required="required">
								</label>
							</div>
							<div class="renglonForm">
								<label for="numeroCliente">Nro<em>*</em>
									<input type="text" id="numeroClienteEd" name="numeroClienteEd"  placeholder="Numero de calle..." required="required">
								</label>
							</div>
							<div class="renglonForm">
								<div class="bloque-renglon">  
								<label >Horario entrega <em>*</em></label>
								
								<div class="horarios">
									<select name="horaDesdeEd" id="horaDesdeEd" style="width:20%">
									<?php for($i="0";$i<24;$i++):?>
										<option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
									<?php endfor;?>    
									</select> 
										:
									<select name="minutoDesdeEd" id="minutoDesdeEd" style="width:20%">
										<option value="00">00</option>
										<option value="15">15</option>
										<option value="30">30</option>
										<option value="45">45</option>
										
									</select> 
								
								A 
									<select name="horaHastaEd" id="horaHastaEd" style="width:20%">
									<?php for($i="0";$i<24;$i++):?>
										<option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
									<?php endfor;?>    
									</select>
										:
									<select name="minutoHastaEd" id="minutoHastaEd" style="width:20%">
										<option value="00">00</option>
										<option value="15">15</option>
										<option value="30">30</option>
										<option value="45">45</option>
										
									</select>
									hs

										</div>
									</div>
								</div>
								<div class="renglonForm">
									<label for="visitaVendedorEd">Visitar:<em>*</em>
										
										<select name="visitaVendedorEd" id="visitaVendedorEd">
											<option value="No" selected>No</option>
											<option value="Semanal">Semanal</option>
											<option value="Quincenal">Quincenal</option>
											<option value="Mensual">Mensual</option>
										</select>
									</label>
								</div>
								<div class="renglonForm">
									<div class="bloque-renglon">
									<label for="intervaloVisitaEd" id="labelIntervaloVisitaEd">Cuando:<em>*</em>                        
									</label> 
									<select name="intervaloVisitaEd" id="intervaloVisitaEd">
											<option value="No" selected>No</option>                                    
										</select>
									</div>
								</div>        
									
								<div class="renglonForm">    
									<label for="deptoClienteEd">Depto
										<input type="text" id="deptoClienteEd" name="deptoClienteEd"  placeholder="Numero o letra departamento...">
									</label>
								</div>
								<div class="renglonForm">                  
									<label for="provinciaClienteEd">Provincia<em>*</em>
										<select name="provinciaClienteEd" id="provinciaClienteEd" required="required" depto="departamentoClienteEd" zona="zonaClienteEd">
										</select>
									</label>
								</div>
								<div class="renglonForm">
									<label for="departamentoClienteEd">Depto/Localidad<em>*</em>
										<select name="departamentoClienteEd" id="departamentoClienteEd" required="required" distrito="distritoClienteEd">
										</select>
									</label>
								</div>
								<div class="renglonForm">
									<label for="distritoClienteEd">Distrito/Partido<em>*</em>
										<select name="distritoClienteEd" id="distritoClienteEd" required="required">
										</select>
									</label>
								</div>
								<div class="renglonForm">
									<label for="zonaClienteEd">Zona<em>*</em>
										<select name="zonaClienteEd" id="zonaClienteEd" required="required"> 
										</select>
									</label>
								</div>
								<input type="hidden" name="idClienteDom" id="idClienteDom">
							</div>


		<!--                     
							<div class="renglonForm renglonBotones" style="text-align: center; width: 97%; padding:1%;">
								<button id="botonVolver" class="botonNuevo grande botonCancelar" type="button" tabindex="16">
									<i class="fas fa-times-circle fa-fw fa-lg"></i> Cancelar
								</button>

								<button class="botonNuevo azul grande" type="button" id="guardaEdita"  tabindex="17"><i class="fas fa-check fa-fw fa-lg"></i> Guardar</button> 
							</div> -->




							<div class="renglonForm renglonBotones" style="text-align: center; width: 97%; padding:1%;">
								<button id="botonVolver" class="botonCerrar botonNuevo grande botonVolver" type="button">
									<i class="fas fa-arrow-left fa-fw fa-lg"></i> Volver
								</button>
								<button id="botonVolver" class="botonCerrar botonNuevo grande botonCancelar" type="button">
									<i class="fas fa-times-circle fa-fw fa-lg"></i> Cancelar
								</button>

								<button class="botonNuevo azul grande" type="button" id="guardaEdita" ><i class="fas fa-check fa-fw fa-lg"></i> Guardar</button> 
							</div>
						
						</form>
					</div>	
				</div>
			</div>
            
        </div>
         <?php if($caminoDispo==""){ require_once 'footer.php';}?>   
    </div>

</body>
</html>

