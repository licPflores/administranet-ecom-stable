<?php
header('Content-type: text/html; charset=utf-8');
require_once 'sesion.inc.php';



if(isset($_REQUEST['ajax'])){
    if(is_object($_SESSION['cliente'])){
                        $objCliente = $_SESSION['cliente'];
                    }else{
                        $objCliente = $_SESSION['cliente'][0];
                    }    
    
    $consulta="";
    $campoReglaPrecio="";
    $sqlReglaPrecio="";
    $soyMovil=0;
    $usoRegla = $_SESSION["usaReglaPrecio"];
    $usaIdManual = $_SESSION["usa_id_manual"];
    $caminoDispo= $_SESSION['caminoDisp'];
    if($caminoDispo!=""){
        $soyMovil=1;
    }
    $listaPrecioCliente = $objCliente->listaPrecio;
    $descRenglon        = $objCliente->descRenglon;
    $codCliente = $objCliente->Codigo;
    
    $limDescReng    = $objVendedor->lim_desc_renglon;
            $precioNeto = 0;
            $importeIva = 0;
            $importeInterno = 0;
            $precioVenta = 0;
            $precioVentaF =0;
            if($_SESSION["utilizaEmbalaje"]=="Si"){
                $usaEmbalaje = "Si";
            }else{
                $usaEmbalaje = "No";
            }
            
          
            /*
             * IVA INCLUIDO
             */
            
            $ivaIncluido = $_SESSION["ivaIncluido"];
    
    $hasta=date('Y-m-d');
    $desde=date('Y-m-d', strtotime('-1 years'));        
    $filtro=" stock.CodigoCP=".$codCliente." AND stock.Fecha BETWEEN '".$desde."' AND '".$hasta."'";
    $limite="LIMIT 20";
        
    /* reglas de precio si tengo habilitado configuro su uso pero ademas en el caso
        de la lista de precios si no he elegido un cliente regla general        */
    if($usoRegla=="Si" && $codCliente!=null){
        $campoReglaPrecio="rp.tipo_calculo,rp.importe_regla,";
        $sqlReglaPrecio="LEFT JOIN reglas_precio AS rp ON  
                        (rp.id_articulo = articulo.IDArt 
                        AND rp.id_cliente={$codCliente} 
                        AND  ('".date('Y-m-d')."' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
                        AND rp.anulado='No' )";
    }
    
    
//    busco por campo ademas del estado si estuviere.
//    @numeroComp: buscar por el numero de pedido
//    @fechaDesde : desde el periodo
//    @fechaHasta : hasta del periodo
    $sqlArticulo = "SELECT
                        stock.IDArt,
                        articulo.id_manual,
                        SUM(stock.Cantidad) AS Cuantos,
                        articulo.Alicuota,
                        articulo.AlicuotaIB,
                        marca.NombreMarca AS Marca,
                        modelo.NombreModelo AS Modelo,                        
                        articulo.Precio1V,
                        articulo.Precio2V,
                        articulo.Precio3V,
                        articulo.Precio4V,
                        articulo.Precio5V,
                        articulo.PNOficial, 
                        articulo.IDSubRubro, 
                        articulo.CodigoSubRubro,
                        articulo.CodigoRubro,
                        articulo.CodigoArticuloT,
                        articulo.NombreArticulo,
                        articulo.PFOficial,
                        articulo.Precio1VI,
                        articulo.Precio2VI,
                        articulo.Precio3VI,
                        articulo.Precio4VI,
                        articulo.Precio5VI,
                        (articulo.Precio1VI-articulo.Precio1V) AS impIva1,
                        (articulo.Precio2VI-articulo.Precio2V) AS impIva2,
                        (articulo.Precio3VI-articulo.Precio3V) AS impIva3,
                        (articulo.Precio4VI-articulo.Precio4V) AS impIva4,
                        (articulo.Precio5VI-articulo.Precio5V) AS impIva5,
                        (articulo.PFOficial-articulo.PNOficial) AS impOf,
                        (articulo.Precio1V*articulo.impuesto_interno/100) AS imp_interno1,
                        (articulo.Precio2V*articulo.impuesto_interno/100) AS imp_interno2,
                        (articulo.Precio3V*articulo.impuesto_interno/100) AS imp_interno3,
                        (articulo.Precio4V*articulo.impuesto_interno/100) AS imp_interno4,
                        (articulo.Precio5V*articulo.impuesto_interno/100) AS imp_interno5,
                        (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf,
                        articulo.impuesto_interno,    
                        articulo.tipoIVA,
                        iva.Alicuota AS Alic, 
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
                        articulo.promocion_vigencia_desde,
                        articulo.promocion_vigencia_hasta,
                        articulo.CodigoProveedor,
                        articulo_prov.multiplicador_comp,
                        articulo_prov.cantidad_uni, 
                        unidmed.descrip_corta AS nombre_unimed,  
                        presentacion_abm.nombre_presentacion, 
                        articulo_prov.id_presentacionC,
                        {$campoReglaPrecio}
                        articulo_prov.id_unimed,
                        articulo.cantidad_promedio_bulto,
                        mart.tipo_unidad,
                       mart.descrip_corta AS uniArt
                        FROM stock                                                       
                            LEFT JOIN articulo ON articulo.IDArt = stock.IDArt
                            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
                            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
                            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
                            
                            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
                            {$sqlReglaPrecio}
                            WHERE 
                            {$filtro}
                            AND articulo.Discontinuo='No'                            
                            AND articulo.tipo_art='Articulo' 
                            
                            GROUP BY stock.IDArt    
                            ORDER BY SUM(stock.Cantidad) DESC
                            {$limite};";
    

    
    
    $hacer = mysqli_query($connV,$sqlArticulo) or die('No puedo consultar el consumo <pre>'.mysqli_error($connV).$sqlArticulo."</pre>");
    /*
     * listado WEeb y no web de los consumos 
     * =========================================================================
     */
    $articulos =array();
    while($a= mysqli_fetch_object($hacer)){
        $articulos[]=$a;
    }
    
    $tabla =  "<thead>\n";
    $tabla .=  "<tr>\n";  
    $tabla .=  "<th>#</th>\n";
    $tabla .=  "<th>Artículo</th>\n";
    $tabla .=  "<th>Cant</th>\n";
    $tabla .=  "<th class='dt-right'>Precio</th>\n";
    $tabla .=  "</tr>\n";
    $tabla .=  "</thead>\n";
    $tabla .=  "<tbody>\n";
    $promoLista = "no";
    $pos=0;
    foreach($articulos as $arti){
        $pos++;
        if($usaIdManual=="Si"){
            $idArtT = $arti->id_manual;
        }else{
            $idArtT = $arti->IDArt;
        }
        $idArt          = $arti->IDArt;
        $nombreArticulo = $arti->NombreArticulo;

        /*
         * LISTA DE PRECIOS
         */

        $precios = calculaPrecios($connV,$arti,$listaPrecioCliente,$descRenglon,$usoRegla,$codCliente);                
        /*
         * EMBALAJE
         */
        $bulto="";
        if($usaEmbalaje=="Si"){
            // tengo que hacer la busqueda de los valores para mostrar
            if($arti->nombre_presentacion!=""){
                $bulto = $arti->nombre_presentacion ." x ".number_format($arti->cantidad_uni,0);
            }
            if($arti->nombre_unimed!=""){
                $bulto .= " (".$arti->nombre_unimed.")";
            }
        }

        $clase = $precios["clase"];
        $nombreArticulo .=$precios["promoNombre"];
        $clasePrecio = $precios["clasePrecio"];
        $promo = $precios["promo"];
        if($ivaIncluido=='no'){
            $precioVenta = $precios["neto"];
            $precioVentaFinal = $precios["netoCalc"];
        }else{
            $precioNeto = $precios["neto"];
            $precioVenta = $precios["precioVenta"];
            $precioVentaFinal = $precios["precioFinal"];
        }

        $descFinal = $precios["descuento"];
//                formateo los precios a cuatro decimales..just in case...
        $precioVentaF = number_format($precioVenta,2,',','');
        $precioVentaFinalF = number_format($precioVentaFinal,2,',','');
        $precioNetoF = number_format($precioNeto,2,',','');

        $tabla .= "<tr>";

//        $tabla .= "<td {$clase}>{$arti->NombRub}</td>";
        $tabla .= "<td>{$pos}</td>";      
        $tabla .= "<td {$clase}><div class='art'>({$idArtT}) {$nombreArticulo} </div><div class='rubroSub'>{$arti->NombRub}, {$arti->NombSubRub}</div></td>";

        $tabla .= "<td class='importe {$clasePrecio}'>{$arti->Cuantos}</td>";
        $tabla .= "<td class='importe {$clasePrecio}' > $<span id='mi-art-precio{$arti->IDArt}'>{$precioVentaF}</span><br>";
        
        

        if($promo=='no'){
            /*
             * No tengo promocion
             */
            if($_SESSION['tipousuario']=='cliente'){
                /*
                 * Soy un cliente y tengo descuento pero no puedo tocarlo
                 * debo calcular el precio con el descuento
                 */



                $tabla .= "({$descFinal}%)\n";                         
                
            }else{
                /*
                 * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                 * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                 */
                if($descFinal==0){

                    $tabla .= "(0%)\n"; 
                }else{
                    $tabla .= "({$descFinal}%)\n"; 
                }
                
            }
        }else{
            /*
             * Hay promocion
             */
            $tabla .= "({$descFinal}%)\n";                         
            
        }
        $tabla .= "<br>$<span id='mi-art-precio{$arti->IDArt}'>{$precioVentaFinalF}</span>\n";
        $tabla .= "</td>\n";
        $tabla .= "</tr>\n";

    }
    $tabla .= "</tbody>\n";
    echo $tabla;

        
    
}

function calculaPrecios($connV,$arti=null,$listaPrecioCliente = null,$descRenglon=null,$usaReglaPrecio=null,$codCliente=null){
    /*
    * LISTA DE PRECIOS
    */
//                 echo "<pre>";
//                 print_r($arti);
//                 echo "<br>";
//                 print_r($listaPrecioCliente);
//                 echo "</pre>"; 
    $nombreArticulo="";
   switch($listaPrecioCliente){
           case 'Lista 1':
               $precioNeto     = $arti->Precio1V;
               $importeIva     = $arti->impIva1;
               $importeInterno = $arti->imp_interno1;
               $precioVenta    = $arti->Precio1VI;
               if($arti->promocion_lista1=="Si"){
                   $promoLista ="si";
               }else{
                   $promoLista ="no";
               }
               break;
           case 'Lista 2':
               $precioNeto     = $arti->Precio2V;
               $importeIva     = $arti->impIva2;
               $importeInterno = $arti->imp_interno2;
               $precioVenta    = $arti->Precio2VI;
               if($arti->promocion_lista2=="Si"){
                   $promoLista ="si";
               }else{
                   $promoLista ="no";
               }
               break;
           case 'Lista 3':
               $precioNeto     = $arti->Precio3V;
               $importeIva     = $arti->impIva3;
               $importeInterno = $arti->imp_interno3;
               $precioVenta    = $arti->Precio3VI;
//                        echo "<pre> ".$arti->IDArt." - ".$arti->promocion_lista3."</pre>";
               if($arti->promocion_lista3=="Si"){
                   $promoLista =   "si";
               }else{
                   $promoLista =   "no";
               }
               break;
           case 'Lista 4':
               $precioNeto     = $arti->Precio4V;
               $importeIva     = $arti->impIva4;
               $importeInterno = $arti->imp_interno4;
               $precioVenta    = $arti->Precio4VI;
               if($arti->promocion_lista4=="Si"){
                   $promoLista ="si";
               }else{
                   $promoLista ="no";
               }
               break;
           case 'Lista 5':
               $precioNeto     = $arti->Precio5V;
               $importeIva     = $arti->impIva5;
               $importeInterno = $arti->imp_interno5;
               $precioVenta    = $arti->Precio5VI;
               if($arti->promocion_lista5=="Si"){
                   $promoLista ="si";
               }else{
                   $promoLista ="no";
               }
               break;    
           case 'Lista Oficial':
               $precioNeto     = $arti->PNOficial;
               $importeIva     = $arti->impOf;
               $importeInterno = $arti->imp_internoOF;
               $precioVenta    = $arti->PFOficial;
               $promoLista     = "si";

               break;
       }

//                validar si se usa una promocion.
//                primero a por las fechas..
//                segundo a por los descuentos calculados...
//                calculo con reglas de precios
    /*
     * REGLAS DE PRECIO primero y si hay reglas no se hacen descuentos
     * las reglas se buscan solo si hay permiso de reglas.
     * 
     */
//                echo "<pre>";
//                print_r($arti);
//                echo "</pre>";

    $precioVentaFinal =$precioVenta;
    $precioNetoCalc = $precioNeto;

    $descFinal      = 0;
    $clase          = "";
    $clasePrecio    = "";

    $promoCant      = "";
    $promoPorc      = "";
    $promoTipo      = $arti->promocion_tipo;
    $promo          = "no";

    $aplicaPromo    = "no";
    $desc           = "si";
    $usoPromocion   = "Si";
    $encontreRegla  =0;
    /*
     * Si no hay cliente seleccionado no hace el descuento.
     */
    if($usaReglaPrecio=="Si"){
        /* Variables de Reglas 
         * ======================
         */
        $idArtR =$arti->IDArt;
        $codigoRubroR=$arti->CodigoRubro;
        $idSubRubroR=$arti->IDSubRubro;
        $codigoProveedorR=$arti->CodigoProveedor;
        $codClienteR=$codCliente;
        
        /* Reglas Particulares
         * ===================
         */
        
        if($arti->tipo_calculo){
            // regla plarticular
            //echo "particular";
            $hayRegla = "Si";
            $usoPromocion   = "No";
            $encontreRegla++;
            $tipoCalculo= $arti->tipo_calculo;
            $importeRegla = $arti->importe_regla;
        }
        /* Reglas Masivas
         * ======================== 
         */
        if($encontreRegla==0){
            //echo "regla MAsivas";
            // ir a buscar la funcion que recupera si hay alguna regla masiva.
            $idReglaMasiva = reglasPrecioMasivas($connV,$idArtR,$codigoProveedorR,$codigoRubroR,$idSubRubroR,$codClienteR);
//            echo "<pre>";
//            var_dump($this->reglasPrecioMasivas($idArtR,$codigoProveedorR,$codigoRubroR,$idSubRubroR,$codClienteR));
//            echo "<Br>";
//            var_dump($idReglaMasiva);
//            echo "</pre>";
            if($idReglaMasiva!=null){
                // hay regla masiva
                
                $sqlReglaM ="SELECT * FROM reglas_precio_masivas "
                        . "WHERE id_regla_precio_masivas ={$idReglaMasiva} ";
                $hacerRM= mysqli_query($connV,$sqlReglaM) or die("No puedo recuperar la Regla masiva encontrada ".mysqli_error($connV)."<pre>".$sqlReglaM."</pre>");        
                $rm = mysqli_fetch_assoc($hacerRM);
//                echo "<pre>";
//                print_r($rm);
//                echo "</pre>";
                $hayRegla = "Si";
                $encontreRegla++;
                $tipoCalculo= $rm["tipo_calculo"];
                $importeRegla = $rm["importe_regla"];
                
            }            
        }
        /*
         * Reglas Generales
         * =====================================================================
         */
        
        if($encontreRegla==0){
            //echo "reglas generales";
            // ir a buscar la funcion que recupera si hay alguna regla General.
            $idReglaGeneral = reglasPrecioGeneral($connV,$idArtR,$codigoProveedorR,$codigoRubroR,$idSubRubroR);
            if($idReglaGeneral!=null){
                // hay regla general
                
                $sqlReglaG ="SELECT * FROM reglas_precio_alta_art "
                          . "WHERE id_regla_precio_alta_art = {$idReglaGeneral}";
                $hacerRG= mysqli_query($connV,$sqlReglaG) or die("No puedo recuperar la Regla general encontrada ".mysqli_error($connV)."<pre>".$sqlReglaG."</pre>");        
                $rg = mysqli_fetch_assoc($hacerRG);
//                echo "<pre>";
//                print_r($rg);
//                echo "</pre>";
                $hayRegla = "Si";
                $encontreRegla++;
                $tipoCalculo= $rg["tipo_calculo"];
                $importeRegla = $rg["importe_regla"];
                $prioridad_regla = $rg["prioridad_regla"];
                
            }            
        }
        
        
        /** encontre alguna Regla y la tengo que usar.*/
        if($encontreRegla!=0){ 
//            echo "<pre>encontre regla</pre>";
            $usoPromocion="No";
//            $usoPromocion
            // vemos el tipo de regla si Descuento - Marcacion o Precio Fijo
//            echo "<pre> ImpoPromo=>";
//            print_r($importeRegla);
//            echo "<BR>Tipoc=>";
//            print_r($tipoCalculo);
//            echo "</PRE>";
            switch($tipoCalculo){
                case "Descuento":                            
                    //cargo descuento
                    // analizo la prioridad de la regla del cliente
                    
                    if(isset($prioridad_regla)&&$prioridad_regla != "Desc. Cliente"){
                        // aplico el descuento de menor valor.
                        
                        $descRenglon = $importeRegla;
                    }else{
                        // prioridad descuento de cliente
                        // aplico el descuento de menor valor.
                        if($descRenglon>$importeRegla){
                             $descRenglon = $importeRegla;
                        }
                    }
                    
                    
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descRenglon*$precioNeto/100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto         = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc 
                            + (($precioNetoCalc  * $arti->Alic) /100) 
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $descFinal = $descRenglon;
                    $promoCant          = "";
 //                            $promoPorc          = "";
                    $promo              = "no";
                    $cantidad           =   1;
//                    echo "<pre>";
//                    var_dump($descRenglon);
//                    var_dump($precioNetoNuevo);
//                    var_dump($descRenglonCalc);
//                    var_dump($precioNetoCalc);
//                    var_dump($importeIva);
//                    var_dump($importeInterno);
//                    var_dump($precioVenta);
//                    echo "</pre>";
                    break;
                case "Marcacion":
                    $descRenglon = $importeRegla;
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descRenglon*$precioNeto/100);
                    $precioNetoCalc     = $precioNetoNuevo + $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto         = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc 
                            + (($precioNetoCalc  * $arti->Alic) /100) 
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $descFinal = 0;
                    $promoCant          = "";
 //                            $promoPorc          = "";
                    $promo              = "no";
                    $cantidad           =   1;
                    $descFinal = 0;

                    // hago el aumento pero no muestro descuento
                    break;
                case "Precio Fijo":
                    $descuento = $importeRegla;
                    $precioNetoNuevo    = $descuento;
                    $descRenglonCalc    = ($descRenglon*$precioNeto/100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto         = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc 
                            + (($precioNetoCalc  * $arti->Alic) /100) 
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $descFinal = $descRenglon;
                    $promoCant          = "";
 //                            $promoPorc          = "";
                    $promo              = "no";
                    $cantidad           =   1;
                    $descFinal = 0;
                    //reemplazo el neto x este nuevo y cero descuento
                    break;
                case "Cantidad - Unidad":
                    
                    
                    $descRenglon = 0;
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descRenglon*$precioNeto/100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto         = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc 
                            + (($precioNetoCalc  * $arti->Alic) /100) 
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    
                    
                    $sqlPCant= "SELECT rp.promocion_por, rp.promocion_cant "
                                . "FROM reglas_precio AS rp"
                                . "WHERE rp.id_articulo ={$idartR}  AND " 
                                . "rp.tipo_calculo = 'Cantidad - Unidad' AND "                                 
                                . "rp.id_cliente ={$codClienteR} ";
                                
                    $hacerPcant = mysqli_query(connV,$sqlPCant) or die("No puedo recuperar la promocion cantidad de las reglas ".mysqli_query(connV)."<pre>".$sqlPCant."</pre>");                    
                    $arrPcant = mysqli_fetch_assoc($hacerPcant);
                    $promoCant = $arrPcant["promocion_cant"];
                    $promo      = "si";
                    $cantidad   = number_format($promoCant);
                    break;
            }
        }else{
            // no hay reglas ni una entonces reviso promociones
            $usoPromocion="Si";
        }
    }    
    /* NO HAY REGLA o no utiliza regla de precios.
     * USO PROMOCION
     * ============================================
     */
    if($usoPromocion=="Si"){

           /*
            * Articulo en promocion
            * =========================================================
            * coloco los datos de la promocion para saber si se aplica y que descuentos
            * la promocion se aplica cuando se compra la cantidad, 
            * **/
//                    echo "<pre>";
//                        var_dump($arti->promocion == 'Si' && $promoLista =="si");
//                    echo "</pre>";
           if($arti->promocion == 'Si' && $promoLista =="si"){
               /*
                * Hay promocion cargada
                */
               $promoCant = $arti->promocion_cant;
               $promoPorc = $arti->promocion_por;
               $promoTipo =$arti->promocion_tipo;

               /*
                * Evaluo si la promocion que podria aplicar tiene un porcentaje
                * que sea mayor al descuento del renglon del cliente, si no
                * dejo el descuento del cliente. 
                */
//                        echo "<pre>";
//                        print_r( $descRenglon);
////                        
//                        echo "</pre>";
//                        echo "<pre>";
//                        print_r($promoPorc);
//                        echo "</pre>";
//                        
               if($descRenglon > 0){
                   /*
                    * Hay descuento por renglon
                    */
//                            echo "<pre>";
//                            var_dump($descRenglon > $promoPorc);
//                            echo "</pre>";
                    if($promoTipo!='Cantidad - Intervalo'){
                   
                        if($descRenglon > $promoPorc){
                        /*
                         * el descuento x renglon es mayor que la promocion
                         * la desactivo
                         */
                           $descFinal = $descRenglon; 
                           $aplicaPromo = "no";
                        }else{
                            /*
                             * el descuento x renglon es menor uso la promocion
                             */
                            $descFinal = $promoPorc;
                            $aplicaPromo = "si";
                        }
                    }else{
                        /* la promocion es cantidad intervalo tengo que aplicar promocion pero 
                         * tengo que evaluar en el momento de comprar y ahi ver el descuento.
                         * 
                         */
                        $descFinal = $descRenglon; 
                        $aplicaPromo = "si";
                        $promoCant=1;
                    }
               }else{
                   /*
                    * No hay descuento x renglon uso la promocion
                    */
                    if($promoTipo!='Cantidad - Intervalo'){
                        $descFinal = $promoPorc;
                        $aplicaPromo = "si";
                    }else{
                        /* debo usar la promocion pero el descuento es cero porque no se cual
                         */
                        $descFinal = 0;
                        $aplicaPromo = "si";
                        $promoCant=1;
                    }
               }

               if($arti->promocion_vigencia_desde==null || $arti->promocion_vigencia_hasta==null){
                   /*
                    * vigencias en Nulo esta siempre en promocion.
                    */

                   $desc = 'no'; // desactivo el descuento
//                        $idArt .='-(P)';
                   $nombreArticulo .='-(promocion)';
                   $clase          = 'class="promocion"';
                   $clasePrecio    = "promocion";

                   /*
                    * Promocion por cantidad
                    */
                   if($promoCant==0){
                       /*
                       * No aplico la promocion por cantidad
                       */
//                            $precioNetoNuevo    = $precioNeto - ($precioNeto * $promoCant /100);
                       $precioNetoNuevo    = $precioNeto;
                       $precioNetoCalc     = $precioNeto - ($precioNeto * $descFinal /100);

                       $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                       $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                       $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                       $precioVentaFinal = $precioNetoCalc 
                                           + (($precioNetoCalc  * $arti->Alic) /100) 
                                           + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                       $precioNeto         = $precioNetoNuevo;
                       $promoCant          = "";
//                            $promoPorc          = "";
                       $promo              = "no";
                       $cantidad           =   1;
                   }else{
                       /*
                        * coloco la cantidad de articulos que se usa p promocion
                        * pero si por el porcentaje no se aplica..no lo uso
                        */
                       if($aplicaPromo=="si"){
                           $promo      = "si";
                           $precioNetoNuevo    = $precioNeto;
                           $descRenglonCalc    = ($descFinal*$precioNeto/100);
                           $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                           $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                           $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                           $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                           $precioVentaFinal = $precioNetoCalc 
                               + (($precioNetoCalc  * $arti->Alic) /100) 
                               + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                           $precioNeto         = $precioNetoNuevo;
                           $cantidad   = number_format($promoCant);

                       }else{
                           $promo    = "no";
                           $cantidad = number_format(1);
                       }

                   }
               }else{
                   /*
                    * Evaluo la vigencia de la promocion tiene un intervalo
                    */
                  //echo var_dump($arti->promocion_vigencia_desde!=null);
                   $fd     = explode('-',$arti->promocion_vigencia_desde);
                   $fh     = explode('-',$arti->promocion_vigencia_hasta);

                   if($fh[0]>2038){
                       $fh[0]=2037;
                       }
//                            $desde  = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
//                            $hasta  = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
                   $desde = new DateTime ( $arti->promocion_vigencia_desde ); 
                   $hasta = new DateTime ( $arti->promocion_vigencia_hasta ); 

                   $hoy = new DateTime(date('Y-m-d'));

                   if($hoy>=$desde && $hoy<=$hasta){
                       /*
                        * Promocion Valida de intervalo
                        */
//                            $idArt .='-(P)';
                       $desc = 'no'; // desactivo el descuento
                       $nombreArticulo .='-(promocion)';
                       $clase          = 'class="promocion"';
                       $clasePrecio    = "promocion";
                       /*
                        * Compre la promociones con la cantidad
                        */
                       if($promoCant==0){
                           /*
                            * No aplico la promocion por cantidad
                            */
//                              $precioNetoNuevo    = $precioNeto - ($precioNeto * $promoPorc /100);

                           $precioNetoNuevo    = $precioNeto;
                           $descRenglonCalc    = ($descFinal*$precioNeto/100);
                           $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                           $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                           $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                           $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                           $precioVentaFinal = $precioNetoCalc 
                               + (($precioNetoCalc  * $arti->Alic) /100) 
                               + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                           $precioNeto         = $precioNetoNuevo;
                           //$precioNetoCalc     = $precioNeto;
                           $promoCant          = "";
//                                $promoPorc          = "";
                           $promo              = "no";
                           $cantidad           = 1;
                       }else{
                           /*
                            * tengo la cantidad que entra en la promocion
                            * pero si no la uso por el porcentaje no la activo
                            */
                           if($aplicaPromo=="si"){
                               $promo      = "si";
                               $cantidad   = number_format($promoCant);
                               $precioNetoNuevo    = $precioNeto;
                                $descRenglonCalc    = ($descFinal*$precioNeto/100);
                                $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                                $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                                $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                                $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                                $precioVentaFinal = $precioNetoCalc 
                                    + (($precioNetoCalc  * $arti->Alic) /100) 
                                    + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                                $precioNeto         = $precioNetoNuevo;
                           }else{
                               $promo      = "no";
                               $cantidad   = 1;
                           }
                       }
                       // debo calcular la promocion.
                   }else{
                       /// NO ALCANCE PROMOCION CONSULTO X DESCUENTO CLIENTE
                       if($descRenglon > 0){
                        /*
                         * el descuento 
                         * la desactivo
                         */
                            $promo      = "no";
                            $cantidad   = 1;
                            $descFinal = $descRenglon;
                        }else{
                            /*
                             * el descuento x renglon es menor uso la promocion
                             */
                            $promo      = "no";
                            $cantidad   = 1;
                            $descFinal = 0;
                        }
                       
                       

                   }
//                        la promocion no esta alcanzada por el periodo

               }
           }else{
               /*
                * No existe promocion asi que evaluo si aplico el descuento
                * x renglon del articulo.
                */
               if($descRenglon>0){
                   /*
                    * Debo recalcular el precio de acuerdo al descuento
                    */

                   $descFinal = $descRenglon;
                   $precioNetoNuevo    = $precioNeto;
                   $descRenglonCalc    = ($descFinal*$precioNeto/100);
                   $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                   $importeIva         = ($precioNetoNuevo  * $arti->Alic) /100;
                   $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                   $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                   $precioNeto         = $precioNetoNuevo;
                   $precioVentaFinal = $precioNetoCalc 
                           + (($precioNetoCalc  * $arti->Alic) /100) 
                           + ($precioNetoCalc * ($arti->impuesto_interno / 100));


               }
               $cantidad = 1;
               $promo ="no";
           }
       }
        if(isset($idCliente) && $idCliente == 1){
            $precioNeto=0;
            $precioNetoCalc = 0;
            $precioVenta=0;
            $descFinal=0;
            $precioVentaFinal=0;
        }
    
    /*
     *  IVA INCLUIDO
     */
    if($_SESSION["ivaIncluido"]=='no'){
        $precioVenta = $precioNeto;
        $precioVentaFinal = $precioNetoCalc;
    }
    $precios = array(
                    "idart"     =>$arti->IDArt,
                    "neto"          => $precioNeto,
                    "netoCalc"      => $precioNetoCalc,
                    "precioVenta"   => $precioVenta,
                    "descuento"     => $descFinal,
                    "precioFinal"   => $precioVentaFinal,
                    "cantidad"      => $cantidad,
                    "promoNombre"   => $nombreArticulo,
                    "clase"         => $clase,
                    "clasePrecio"   => $clasePrecio,
                    "importeIva"    => $importeIva,
                    "importeInterno"    => $importeInterno,
                    "promo"          => $promo
                    );

    return $precios;
        
}


function reglasPrecioMasivas($connV,$idArt=null,$codigoProveedor=null,$codigoRubro=null,$idSubRubro=null,$codCliente=null){
            
/*  ''''''''''''''''
    'Reglas Masivas'
    ''''''''''''''''
 */

   $varR=null;
   $fecha=date("Y-m-d");

//    'Existen reglas de alta
    $sqlRegla = "SELECT * FROM reglas_precio_masivas WHERE Anulado = 'No'";
    $hacer=mysqli_query($connV,$sqlRegla) or die('No puedo recuperar reglas de precio '.mysqli_error($connV).' <pre>'.$sqlRegla.'</pre>');
    $arrReglas=array();
    while($rr= mysqli_fetch_assoc($hacer)){
        $arrReglas[]=$rr;
    }
    
    if(empty($arrReglas)){
        // sin reglas masivas me vuelvo si o si
        return $varR;
    }
    
    

//    '''''''''''
//    '#Vigencia'
//    '''''''''''
//    fechita = Split(CStr(Principal.Fecha), "/")
//    Fecha3 = fechita(2) & "-" & fechita(1) & "-" & fechita(0)
    
//    'Inicializo
//    $VarR = 0;
    
//    'Obtengo datos del articulo
//    rs_r.Open "SELECT CodigoProveedor, CodigoRubro, IDSubRubro FROM articulo " & _
//              "WHERE articulo.IDART = " & IDArt & " ", conn, adOpenDynamic, adLockOptimistic
//
//    If Not IsNull(rs_r.Fields!CodigoProveedor) Then
//        CodProv = rs_r.Fields!CodigoProveedor
//    End If
//    
//     If Not IsNull(rs_r.Fields!CodigoRubro) Then
//        CodRubro = rs_r.Fields!CodigoRubro
//    End If
//    
//    If Not IsNull(rs_r.Fields!IDSubRubro) Then
//        IDSubRubro = rs_r.Fields!IDSubRubro
//    End If
//    
//    rs_r.Close

/*''''''''''''
//' X CLIENTE'
//''''''''''''*/

    If($codigoProveedor!=null){

//        '5Rubro
//        ================================
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_rubro = " & CodRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_sub_rubro) ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla="SELECT rpm.id_regla_precio_masivas " 
                      . "FROM reglas_precio_masivas AS rpm "
                      . "WHERE rpm.Anulado = 'No' AND "
                      . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                      . "rpm.id_cliente = {$codCliente} AND "
                      . "rpm.id_rubro = {$codigoRubro} AND "
                      . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_sub_rubro)";
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-Rubro".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro = $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del rubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }
            
//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close

//        '4SubRubro
//        =================================================              
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_sub_rubro = " & IDSubRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_rubro) LIMIT 1 ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                      . "FROM reglas_precio_masivas AS rpm "
                      . "WHERE rpm.Anulado = 'No' AND "
                      . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                      . "rpm.id_cliente = {$codCliente} AND "
                      . "rpm.id_sub_rubro = {$idSubRubro} AND "
                      . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_rubro) LIMIT 1 ";
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-SubRubro".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro= $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del Subrubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }
                      
//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close

//        '3Proveedor
//         ========================================================================             
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_proveedor = " & CodProv & " AND " & _
//                      "isnull(id_rubro) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                      ."FROM reglas_precio_masivas AS rpm "
                      ."WHERE rpm.Anulado = 'No' AND "
                      ." '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                      ."rpm.id_cliente = {$codCliente} AND "
                      ."rpm.id_proveedor ={$codigoProveedor} AND "
                      ."ISNULL(rpm.id_rubro) AND ISNULL(rpm.id_sub_rubro)";    
            
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-Proveedor".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro = $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del proveedor devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }
                      
//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close

//        '2Proveedor Rubro
//          ========================================================================  
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_proveedor = " & CodProv & " AND " & _
//                      "id_rubro = " & CodRubro & " AND " & _
//                      "isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla="SELECT rpm.id_regla_precio_masivas "
                      ."FROM reglas_precio_masivas AS rpm "
                      ."WHERE rpm.Anulado = 'No' AND "
                      ." '{$fecha}' BETWEEN rpm.vigencia_desde AND rpm.vigencia_hasta AND "
                      ."rpm.id_cliente = {$codCliente} AND "
                      ."rpm.id_proveedor ={$codigoProveedor} AND "
                      ."rpm.id_rubro ={$codigoRubro}  AND "
                      ."ISNULL(rpm.id_sub_rubro)";
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-Proveedor-Rubro".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro = $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del cliente proveedor rubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }          

//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close

//        '1Proveedor SubRubro
//        =====================================================================
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_proveedor = " & CodProv & " AND " & _
//                      "id_sub_rubro = " & IDSubRubro & " AND " & _
//                      "isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                      ."FROM reglas_precio_masivas AS rpm "
                      ."WHERE rpm.Anulado = 'No' AND "
                      ." '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                      ."rpm.id_cliente ={$codCliente}  AND "
                      ."rpm.id_proveedor ={$codigoProveedor}  AND "
                      ."rpm.id_sub_rubro ={$idSubRubro} AND "
                      ."ISNULL(rpm.id_rubro)";
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-Proveedor-SubRubro".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro = $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del cliente proveedor Subrubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }          
//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close

    }else{

//        '5Rubro
//        ============================================================================    
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_rubro = " & CodRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
                    
            $sqlRegla= "SELECT rpm.id_regla_precio_masivas "
                      ."FROM reglas_precio_masivas AS rpm "
                      ."WHERE rpm.Anulado = 'No' AND "
                      ." '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                      ."rpm.id_cliente ={$codCliente} AND "
                      ."rpm.id_rubro ={$codigoRubro} AND "
                      ."ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_sub_rubro)";       
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-Rubro Sin Proveedor".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro = $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del cliente rubro sin proveedor devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }      
//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close

//        '4SubRubro
//        ==========================================================================    
//            rs_r.Open "SELECT id_regla_precio_masivas " & _
//                      "FROM reglas_precio_masivas " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
//                      "id_sub_rubro = " & IDSubRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas " 
                      ."FROM reglas_precio_masivas AS rpm " 
                      ."WHERE rpm.Anulado = 'No' AND " 
                      ." '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND " 
                      ."rpm.id_cliente ={$codCliente}  AND " 
                      ."rpm.id_sub_rubro ={$idSubRubro} AND " 
                      ."ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_rubro)";
                    
//            If rs_r.RecordCount = 1 Then
//                VarR = rs_r.Fields!id_regla_precio_masivas
//            End If
//
//            rs_r.Close
            $hacerRpm = mysqli_query($connV,$sqlRegla) or die("no pude buscar regla Cliente-SubRubro Sin Proveedor".mysqli_error($connV)."<pre>".$sqlRegla."</pre>");          
            $arrRrubro=array();
            while($rrubro = mysqli_fetch_assoc($hacerRpm)){
                $arrRrubro = $rrubro;
            }
            if(!empty($arrRrubro)){
                // habia regla del cliente subrubro sin proveedor devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
//                return $varR;
            }          

    } 

    /*'Resultado'''''''
    RPrecioM = VarR '
    '''''''''''''''''*/
    return $varR;
}

function reglasPrecioGeneral($connV,$idArt=null,$codigoProveedor=null,$codigoRubro=null,$idSubRubro=null){
//    ''''''''''''''''''''''''''''
//    'Reglas Masivas - Generales'
//    ''''''''''''''''''''''''''''    
    $varR=null;
    $fecha=date("Y-m-d");

//    'Existen reglas de alta
//    Dim rs_r As New ADODB.Recordset
//    rs_r.Open "SELECT * FROM reglas_precio_alta_art WHERE Anulado = 'No' ", conn, adOpenDynamic, adLockOptimistic
//
//    If rs_r.RecordCount = 0 Then
//        rs_r.Close
//        Exit Function
//    End If
//
//    rs_r.Close
    $sqlG="SELECT id_regla_precio_alta_art FROM reglas_precio_alta_art WHERE Anulado='No' LIMIT 1";
    $hacerG= mysqli_query($connV,$sqlG) or die("No puedo recuper reglas precio alta art ".mysqli_error($connV)."<PRE>".$sqlG."</PRE>");
    $hayR= mysqli_fetch_array($hacerG);
    if(empty($hayR)){
       // no hay reglas asi que me vuelvo sin nada
        return $varR;
        
    }


//          '''''''''''''''
//          ' No X CLIENTE'
//          '''''''''''''''

    If($codigoProveedor!=null){

//        '5Rubro
//        ======================================================    
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_rubro = " & CodRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_sub_rubro) ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art " 
                        . "FROM reglas_precio_alta_art AS rpma " 
                        . "WHERE rpma.Anulado = 'No' AND "
                        . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                        . "rpma.id_rubro = {$codigoRubro} AND "
                        . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_sub_rubro)";
                    
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art por Rubro ".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo por Proveedo y Rubro.
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }


//        '4SubRubro
//        '==========================================================    
//
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_sub_rubro = " & IDSubRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_rubro) LIMIT 1 ", conn, adOpenDynamic, adLockOptimistic
                
            $sqlRegla="SELECT rpma.id_regla_precio_alta_art " 
                      . "FROM reglas_precio_alta_art AS rpma " 
                      . "WHERE rpma.Anulado = 'No' AND " 
                      . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND " 
                      . "rpma.id_sub_rubro = {$idSubRubro} AND " 
                      . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_rubro) LIMIT 1 ";    
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art por SubRubro ".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo por Proveedo y Subrubro.
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }
            

//        '3Proveedor
//        ==========================================================================
        
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_proveedor = " & CodProv & " AND " & _
//                      "isnull(id_rubro) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
                    
            $sqlRegla="SELECT rpma.id_regla_precio_alta_art " 
                      . "FROM reglas_precio_alta_art AS rpma " 
                      . "WHERE rpma.Anulado = 'No' AND " 
                      . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND " 
                      . "rpma.id_proveedor = {$codigoProveedor} AND " 
                      . "ISNULL(rpma.id_rubro) AND ISNULL(rpma.id_sub_rubro)";
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor ".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo por Proveedo .
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }                        
           

//        '2Proveedor Rubro
//        ===========================================================================    
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_proveedor = " & CodProv & " AND " & _
//                      "id_rubro = " & CodRubro & " AND " & _
//                      "isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
                    
            $sqlRegla="SELECT rpma.id_regla_precio_alta_art " 
                      . "FROM reglas_precio_alta_art AS rpma " 
                      . "WHERE rpma.Anulado = 'No' AND " 
                      . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND " 
                      . "rpma.id_proveedor = {$codigoProveedor} AND " 
                      . "rpma.id_rubro = {$codigoRubro} AND " 
                      . "isnull(rpma.id_sub_rubro)  ";
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor y Rubro juntos".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo por Proveedor y rubro juntos .
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }                
//        '1Proveedor SubRubro
//        =======================================================================
        
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_proveedor = " & CodProv & " AND " & _
//                      "id_sub_rubro = " & IDSubRubro & " AND " & _
//                      "isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla="SELECT rpma.id_regla_precio_alta_art " 
                      . "FROM reglas_precio_alta_art AS rpma " 
                      . "WHERE rpma.Anulado = 'No' AND " 
                      . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND " 
                      . "rpma.id_proveedor = {$codigoProveedor} AND " 
                      . "rpma.id_sub_rubro = {$idSubRubro} AND " 
                      . "ISNULL(rpma.id_rubro) ";
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor y SubRubro juntos".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo por Proveedor y Subrubro juntos .
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }         

    }else{

//        '5Rubro
//        =========================================================================    
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_rubro = " & CodRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla="SELECT rpma.id_regla_precio_alta_art " 
                      . "FROM reglas_precio_alta_art  AS rpma"
                      . " WHERE rpma.Anulado = 'No' AND "
                      . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                      . "rpma.id_rubro ={$codigoRubro} AND "
                      . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_sub_rubro)";
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art SIN Proveedor y por Rubro".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo Sin Proveedor y por Rubro .
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }  

//        '4SubRubro
//        ===========================================================================
            
//            rs_r.Open "SELECT id_regla_precio_alta_art " & _
//                      "FROM reglas_precio_alta_art " & _
//                      "WHERE Anulado = 'No' AND " & _
//                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
//                      "id_sub_rubro = " & IDSubRubro & " AND " & _
//                      "isnull(id_proveedor) AND isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla="SELECT rpma.id_regla_precio_alta_art " 
                      . "FROM reglas_precio_alta_art AS rpma "
                      . "WHERE rpma.Anulado = 'No' AND "
                      . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                      . "rpma.id_sub_rubro ={$idSubRubro} AND "
                      . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_rubro)";
            $hacerR= mysqli_query($connV,$sqlRegla) or die("No puedo recuperar la lista precio alta art SIN Proveedor y por SubRubro".mysqli_error($connV)."<pre>".$sqlRegla." </pre>");
            $arrRegla = array();
            while($ff = mysqli_fetch_assoc($hacerR)){
                $arrRegla=$ff;
            }
            if(!empty($arrRegla)){
                // hay regla general de articulo Sin Proveedor y por Rubro .
                $varR = $arrRegla["id_regla_precio_alta_art"];
//                return $varR;
            }
            

    }

//    'Resultado'''''''
//    RPrecioG = VarR '
//    '''''''''''''''''    
        return $varR;
    }
 
