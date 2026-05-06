<?php
require_once 'sesion.inc.php';
if(isset($_REQUEST['ajax'])){
   $categoria=null;
   $rubro=null;
   $subrubro=null;
   $marca=null;
   $modelo=null;
   $filtro="";
   
   
   if(isset($_REQUEST['categoria'])&&$_REQUEST['categoria']!=""){
        $categoria = $_REQUEST['categoria'];
    }
    if(isset($_REQUEST['rubro'])&&$_REQUEST['rubro']!=""){
        $rubro = $_REQUEST['rubro'];
    }
    if(isset($_REQUEST['subrubro'])&&$_REQUEST['subrubro']!=""){
        $subRubro = $_REQUEST['subrubro'];
    }
    if(isset($_REQUEST['marca'])&& $_REQUEST['marca']!=""){
        $marca = $_REQUEST['marca'];
    }
    if(isset($_REQUEST['modelo'])&& $_REQUEST['modelo']!=""){
        $modelo = $_REQUEST['modelo'];
    }
    
    $caminoDispo="";
    $soyMovil=0;
//    echo "<pre>";
//    print_r($_SESSION);
//    echo "</pre>";
    if(isset($_SESSION["caminoDisp"])){
        $caminoDispo=$_SESSION["caminoDisp"];
        $soyMovil=1;
    }

    $consultaLista  = "";
    $consultaListaP = "";
    // condiciones
    if(!empty($objCliente)){
        $promoLista     = strtolower(str_replace(' ','',$objCliente->listaPrecio));
        $consultaLista  = "AND articulo.promocion_{$promoLista} ='Si'";
        $consultaListaP = "AND ai.{$promoLista}='Si'";
    }
        //listado de promociones validas
    
    // filtros
    
    if($categoria){
            $filtro .=" AND rubro.id_categoria=".$categoria;
        }
        if($rubro){
            $filtro .= " AND articulo.CodigoRubro=".$rubro;
        }
        if($subrubro){
            $filtro .= " AND articulo.IdSubrubro=".$subrubro; 
        }
        if($marca){
            $filtro .= " AND articulo.CodigoMarca=".$marca;
        }
        if($modelo){
            $filtro .= " AND articulo.CodigoModelo=".$modelo;
        }
    
    
    $sqlPromo="SELECT 
                    articulo.id_manual,
                    articulo.tipo_art,
                    articulo.Alicuota,
                    articulo.AlicuotaIB,
                    marca.NombreMarca AS Marca,
                    modelo.NombreModelo AS Modelo,
                    articulo.IDArt,
                    articulo.IDSubRubro, 
                    articulo.CodigoSubRubro,
                    articulo.CodigoRubro,                        
                    articulo.CodigoArticuloT,
                    articulo.NombreArticulo,                        
                    iva.Alicuota AS Alic, 
                    cat.nombre_categoria AS NombCategoria,
                    rubro.NombreRubro AS NombRub, 
                    subrubro.NombreSubRubro AS NombSubRub,
                    articulo.promocion,
                    articulo.promocion_por,
                    articulo.promocion_cant,
                    articulo.promocion_alcance,
                    articulo.promocion_tipo,
                    articulo.promocion_listaoficial,
                    articulo.promocion_lista1,
                    articulo.promocion_lista2,
                    articulo.promocion_lista3,
                    articulo.promocion_lista4,
                    articulo.promocion_lista5,
                    articulo.promocion_destacado_web,
                    DATE_FORMAT(articulo.promocion_vigencia_desde,'%d/%m/%Y') AS promocion_vigencia_desde,
                    DATE_FORMAT(articulo.promocion_vigencia_hasta,'%d/%m/%Y') AS promocion_vigencia_hasta 
                    FROM articulo 
                        LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                        LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                        LEFT JOIN rubro ON Rubro.CodigoRubro = articulo.CodigoRubro
                        LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria
                        LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                        LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
                        WHERE 
                        articulo.Discontinuo='No'
                        # Legacy ecommerce: articulo.ecommerce='Si'
                        AND articulo.promocion='Si'
                        {$consultaLista}
                        AND articulo.promocion_vigencia_desde<='".date('Y-m-d')."' 
                        AND articulo.promocion_vigencia_hasta>='".date('Y-m-d')."' 
                        {$filtro}    

                      ORDER BY 
                       articulo.promocion_por DESC,
                          cat.nombre_categoria,
                          rubro.NombreRubro,
                          subrubro.NombreSubRubro,
                          articulo.NombreArticulo;";
                        
    $hacerPromo = mysqli_query($connV,$sqlPromo) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlPromo);
//        echo '<pre>'.$sqlPromo.'</pre>';
    $promos=array();
    while($promo = mysqli_fetch_assoc($hacerPromo)){
        $promos[] = $promo;
    }
        //buscar los articulos con las promociones por cantidad, traigo todos
        // y recupero los valores por cada articulo.
        
    $arrPromoIntervalo = array();              
    $sqlPi="SELECT "
            . "ai.id_articulo,ai.desde_cantidad,ai.hasta_cantidad,ai.monto_descuento "
            . "FROM articulo_promo_intervalo AS ai "
            . "WHERE ai.anulado='No'"
            . " {$consultaListaP}"
            . " AND ai.vigencia_desde<='".date('Y-m-d')."'" 
            . " AND ai.vigencia_hasta>='".date('Y-m-d')."'";
//        echo '<pre>'.$sqlPi.'</pre>';        
    $hacerAi = mysqli_query($connV,$sqlPi) or die("No puedo recuperar las promociones por intervalo <pre>".$sqlPi."</pre>".mysqli_error($connV));
    while($ai=  mysqli_fetch_assoc($hacerAi)){
        $arrPromoIntervalo[$ai["id_articulo"]][] = $ai;
    }
    
    //echo $sqlPedido;
    $hacer = mysqli_query($connV,$sqlPromo) or die('No puedo consultar el pedido '.$sqlPedido);
    
    
//    if($soyMovil==0){
    /* SOY WEB **/
       
    if($hacer){             
            
            
        if(count($promos)==0){
            $muestro = "0";
//            $muestro .='<thead>';
//            $muestro .='            <tr>';                       
//            $muestro .='                <th>&nbsp;</th>';
//            $muestro .='            </tr>';
//            $muestro .='</thead>';
//            $muestro .='<tbody>';
//            $muestro .='<tr>';
//            $muestro .='<td>';
//            $muestro .='No se encontaron resultados';
//            $muestro .='</td>';
//            $muestro .='</tr>';
//            $muestro .='</tbody>';
        }else{
            $muestro = "";
            $muestro .='<thead>';
            $muestro .='    <tr>';
            $muestro .='        <th>Categoría</th>';
            $muestro .='        <th>Rubro</th>';
            $muestro .='        <th>Sub Rubro</th>';
            $muestro .='        <th>Codigo</th>';
            $muestro .='        <th>Articulo</th>';
            $muestro .='        <th>Promoción</th>';
            $muestro .='        <th>Vigencia</th>';                      
            $muestro .='    </tr>';
            $muestro .='</thead>';
            $muestro .='<tbody>';
            foreach($promos as $promo){
                $muestro .='<tr>';
                $muestro .='    <td>'.$promo["NombCategoria"].'</td>';
                $muestro .='    <td>'.$promo["NombRub"].'</td>';
                $muestro .='    <td>'.$promo["NombSubRub"].'</td>';
                $muestro .='    <td>'.$promo["IDArt"].'</td>';

                $muestro .='    <td>';
                $muestro .='        <a id="mi-art-nombre'.$promo["IDArt"].'" rel="'.$promo["IDArt"].'" title="Descripcion de la promocion" class="desc-articulo" >';
                $muestro .='                '.$promo["NombreArticulo"];
                $muestro .='        </a>';
                $muestro .='    </td>';
                $muestro .='    <td>';                                

                $textoPromo ='';
                if($promo["promocion_tipo"]=="Importe descuento"){
                    $textoPromo .="<p><i class='fa fa-gift fa-lg'></i> <strong>".number_format($promo["promocion_por"],0) ."%</strong> OFF</p> ";
                }
                // promo por cantidad.
                if($promo["promocion_tipo"]=="Cantidad - Intervalo"){
                    $arrPromo=array();
                    if(key_exists($promo["IDArt"], $arrPromoIntervalo)){
                        $arrPromo=$arrPromoIntervalo[$promo["IDArt"]];
                    }
                    if(!empty($arrPromo)){
                        foreach($arrPromo as $ii){

                            $textoPromo .= "<p> <i class='fa fa-gift fa-lg'></i>  ". number_format($ii["monto_descuento"],0) ."% OFF por la compra de <strong>" . number_format($ii["desde_cantidad"],null) ."</strong> o más unidades.</p>";
                        }
                    }
                }
                if($promo["promocion_tipo"]=="Cantidad"){
                    $textoPromo .="<p><i class='fa fa-gift fa-lg'></i> ". number_format($promo["promocion_por"],null) ."% de descuento ,";
                    $textoPromo .= " por la compra de <strong>" . number_format($promo["promocion_cant"],null) ."</strong> o más unidades.</p>";
                }
                $muestro .=$textoPromo;                            
                $muestro .='</td>';                  
                $muestro .='<td>'.$promo["promocion_vigencia_hasta"].'</td>';                                                                     
                $muestro .='</tr>';
            }
               
        }
        $muestro .='</tbody>';
    }
        
        echo $muestro;
}
    
    
    
        
//}
