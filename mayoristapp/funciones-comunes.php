<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Funciones que se pueden usar en varios lados
 */

/*
 * Function: CalculaPreciosTodos()
 * voy a calcular el precio del articulo para poder ser llamado en varios lados 
 * tener todo en un solo lugar para que se pueda mantener.
 * @art:= objeto u array del articulo a calcular
 * @cliente = cliente sobre el cual calcular el precio
 * return @precios:= array() que contiene el precio neto, el precio final sin descuento
 * el precio con descuento y el descuento en si.
 * - verifica promociones,
 * - verifica descuentos al renglon
 * - verifica reglas de precio por articulo / cliente.
 */
function calculaPrecioTodos($connV,$arti=null,$cliente=null){
//    echo "<pre>";
//    print_r($arti);
//    echo "</pre>";
    
    if($cliente){
        $idCliente=$cliente->Codigo;
        $descRenglon=$cliente->descRenglon;
        $codCliente=$cliente->Codigo;
        $listaPrecioCliente = $cliente->listaPrecio;
        $promoLista     = strtolower(str_replace(' ','',$cliente->listaPrecio));
        $consultaLista  = "AND articulo.promocion_{$promoLista} ='Si'";
        $usaReglaPrecio="Si";

    }else{
        //localizar la lista de precios del consumidor final
        $descRenglon=0;
        $usaReglaPrecio='No';
        $hayRegla="No";
        $listaPrecioCliente = "Lista 1";
        $promoLista     = "lista 1";
        $consultaLista  = "AND articulo.promocion_lista1 ='Si'";
        $idCliente=1;

    }
    
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
//                print_r($usaReglaPrecio);
//                echo "</pre>";
    $nombreArticulo= $arti->NombreArticulo;
    $precioVentaFinal =$precioVenta;
    $precioNetoCalc = $precioNeto;

    $descFinal      = 0;
    $clase          = "";
    $clasePrecio    = "";

    $promoCant      = "";
    $promoPorc      = "";
    $promoTipo      = $arti->promocion_tipo;
    $promo          = "no";
    $aplicoRegla    ="no";
    $cualRegla      ="";
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
            $idReglaMasiva = reglasPrecioMasivasTodos($connV,$idArtR,$codigoProveedorR,$codigoRubroR,$idSubRubroR,$codClienteR);
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
            $idReglaGeneral = reglasPrecioGeneralTodos($connV,$idArtR,$codigoProveedorR,$codigoRubroR,$idSubRubroR);
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
                    
                    if($prioridad_regla != "Desc. Cliente"){
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
                                
                    $hacerPcant = mysqli_query($connV,$sqlPCant) or die("No puedo recuperar la promocion cantidad de las reglas ".mysqli_error($connV)."<pre>".$sqlPCant."</pre>");                    
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
               $aplicaPromo="no";
               /*
                * Evaluo si la promocion que podria aplicar tiene un porcentaje
                * que sea mayor al descuento del renglon del cliente, si no
                * dejo el descuento del cliente. 
                */
               
               /* PROMOCION PERIODO  PARA TODAS LAS PROMOS EXCEPTO CANT-INTERVALO */
               /*===============================================================
                *  */
               $hayVigencia=vigencia_promo($arti->promocion_vigencia_desde,$arti->promocion_vigencia_hasta);
               
//               echo "y la vigencia? {".$hayVigencia."}";
               
               if($hayVigencia=="si"){
                   $aplicaPromo="si";
               }
               
               // calculo promociones.
               if ($aplicaPromo == "si") {
                    switch ($promoTipo) {
                        case 'Cantidad - Intervalo':
                            // no hago nada porque ni siquiera se si esta vigente.
//                            echo "adentro cantidad intervalo";
                            $promo = "si";
                            $descFinal = 0;
                            $cantidad = 1;

                            break;
                        case 'Importe descuento':
                            if ($descRenglon > $promoPorc) {
                                /*
                                 * el descuento x renglon es mayor que la promocion
                                 * la desactivo
                                 */
                                $descFinal = $descRenglon;
                                $promo = "no";
                            } else {
                                /*
                                 * el descuento x renglon es menor uso la promocion
                                 */
                                $descFinal = $promoPorc;
                                $promo = "si";
                            }

                            $precioNetoNuevo = $precioNeto;
                            $precioNetoCalc = $precioNeto - ($precioNeto * $descFinal / 100);

                            $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                            $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                            $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                            $precioNeto = $precioNetoNuevo;
                            $promoCant = "";
                            $cantidad = 1;



                            break;
                        case 'Cantidad':
                            // % descuento por la compra de X unidades
                            if ($descRenglon > $promoPorc) {
                                /*
                                 * el descuento x renglon es mayor que la promocion
                                 * la desactivo
                                 */
                                $descFinal = $descRenglon;
                                $promo = "no";
                            } else {
                                /*
                                 * el descuento x renglon es menor uso la promocion
                                 */
                                $descFinal = $promoPorc;
                                $promo = "si";
                            }

                            $cantidad = $promoCant;

                            break;
                        case 'Cantidad - Unidad':

                            // 2 x 1 gratis
                            $promo = "si";
                            // cantidad Gratis
                            $descFinal = $promoPorc;
                            // cantidad a comprar
                            $cantidad = $promoCant;

                            break;
                    }
                }
                
                // articulo en promocion pero fuera de intervalo
                // aplico descuento del cliente
                if($aplicaPromo=="no"){
//                    echo "hay descuento del cliente no promocion".$aplicaPromo." des c".$descRenglon;
                        /*
                         * el descuento 
                         * la desactivo
                         */
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
                            
                            $descFinal = $descRenglon;
                }
               
            }
           
           
           
           if($arti->promocion == 'No' || $promoLista =="no"){
//                echo "hay articulo sin promocion";
               // SIN PRoMOCION DESCUENTO X CLIENTE
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
//        if(isset($idCliente) && $idCliente == 1){
//            $precioNeto=0;
//            $precioNetoCalc = 0;
//            $precioVenta=0;
//            $descFinal=0;
//            $precioVentaFinal=0;
//        }
    
    /*
     *  IVA INCLUIDO
     */
    if($_SESSION["ivaIncluido"]=='no'){
//        $precioVenta = $precioNeto;
//        $precioVentaFinal = $precioNetoCalc;
    }
    $precios = array(
                    "idart"     =>$arti->IDArt,
                "neto"          => $precioNeto,
                "netoCalc"      => $precioNetoCalc,
                "precioVenta"   => $precioVenta,
                "descuento"     => $descFinal,                    
                "precioFinal"   => $precioVentaFinal,                
                "promoNombre"   => $nombreArticulo,
                "clase"         => $clase,
                "clasePrecio"   => $clasePrecio,
                "importeIva"    => $importeIva,        
                "importeInterno"    => $importeInterno,
                "promo"          => $promo,
                "descCli"       => $descRenglon,
                "montoDescuento"=> $precioNeto-$precioNetoCalc,                
                "cantidad" => round($cantidad,0),                
                "promoTipo"   => $promoTipo,
                "usoRegla"    => $aplicoRegla,
                "queRegla"    => $cualRegla,
                "importeIvaViejo"    => $importeIva,        
                "ivaAlic"       =>$arti->Alic,
                "impIvaFinal"  => $precioVentaFinal-$precioNetoCalc
                    );

    return $precios;
}

/*funciones para hacer el calculo de precio al mostrar el articulo en info*/

function reglasPrecioGeneralTodos($connV,$idArt=null,$codigoProveedor=null,$codigoRubro=null,$idSubRubro=null){
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
    $hayR= mysqli_fetch_assoc($hacerG);
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


function reglasPrecioMasivasTodos($connV,$idArt=null,$codigoProveedor=null,$codigoRubro=null,$idSubRubro=null,$codCliente=null){
            
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

function vigencia_promo($desde,$hasta){
    // hay un rango valido 
//    echo "y las vigencias???<br>";
//    echo var_dump($desde);
//    echo "<br>";
//    echo var_dump($hasta);
//    echo "<pre>";
    $vigencia="no";
    if($desde!==null && $hasta!==null){
        $fd     = explode('-',$desde);
        $fh     = explode('-',$hasta);

        if($fh[0]>2038){
            $fh[0]=2037;
            }
//                            $desde  = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
//                            $hasta  = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
        $desde = new DateTime ( $desde ); 
        $hasta = new DateTime ( $hasta ); 
        
        
        $hoy = new DateTime(date('Y-m-d'));
//        echo "y las nueasvas???<br><pre>";
//        echo var_dump($desde);
//        echo "<br>";
//        echo var_dump($hasta);
//        echo "<br>";
//        echo var_dump($hoy);
//        echo "<br>";
//        echo var_dump($hoy>=$desde && $hoy<=$hasta);
//        echo "</pre>";
        // VIGENTE
        if($hoy>=$desde && $hoy<=$hasta){
            //echo "no entras";
            $vigencia="si";
        }
    }
    // vigencia infinita
    if($desde==null && $hasta==null){
        $vigencia="si";
    }
    
// inicio infinito pero con fin
    if($desde==null && $hasta!==null){
      
        $fh     = explode('-',$hasta);

        if($fh[0]>2038){
            $fh[0]=2037;
            }

        $hasta = new DateTime ( $hasta ); 

        $hoy = new DateTime(date('Y-m-d'));
        
        if($hoy<=$hasta){
        
            $vigencia="si";
        }
        
    }
    
    // inicio desde peor sin fin o fin nulo
    if($desde!==null && $hasta==null){
      
        $fd     = explode('-',$desde);
        $desde = new DateTime ( $desde ); 
        $hoy = new DateTime(date('Y-m-d'));
        
        if($hoy>=$desde){        
            $vigencia="si";
        }
        
    }
   // echo  "que deuvelvo.{$vigencia}<br>";
    
    return $vigencia;
}

function detalle_promo($tipoPromo,$descuento,$cantidad,$idArt,$connV){
    $detalle="";
    switch($tipoPromo){
        case 'Cantidad - Intervalo':
            $detalle=detalle_promo_intervalo($idArt,$connV);
            break;
        case 'Importe descuento':
            $detalle ="<p>". round($descuento, 0)."% OFF.</p>";
            break;
        case 'Cantidad':
            $detalle ="<p>Por la compra de <strong>".round($cantidad,0)."</strong> un, <strong>".round($descuento,0)."%</strong> OFF.</p>";
            break;
        case 'Cantidad - Unidad':
            $detalle="<p>Llevando <strong>".round($cantidad,0)."</strong> un , <strong>".round($descuento,0)."</strong> un gratis (".round($cantidad,0). " x ". round($descuento,0).").</p>";
            break;
                          
    }
    return $detalle;
}

function detalle_promo_intervalo($idArt,$connV){

    $hoy=date('Y-m-d');
        $link = $connV;
        //$db=mysqli_select_db($base, $link);
        //require_once 'sesion.inc.php';
        $sqlIntervalo = "SELECT pint.* "
                . "FROM articulo_promo_intervalo AS pint "
                . "WHERE "                
                . "pint.id_articulo = {$idArt} And pint.anulado = 'No'"
                . " AND '".$hoy."' BETWEEN pint.vigencia_desde AND pint.vigencia_hasta ORDER BY pint.desde_cantidad ASC";
        $hacerInt = mysqli_query($link,$sqlIntervalo) OR die("no pude recuperar la promocion." . mysqli_error($link) . "<pre>" . $sqlIntervalo . "</pre>");
        
//        echo "<pre>";
//        print_r($sqlIntervalo);
//        echo "</pre>";
         $detallito="<span>Por la compra de: <br>";
        while($pi = mysqli_fetch_assoc($hacerInt)){
            $detallito .="<p>  <strong>".round($pi["desde_cantidad"],0).'</strong> a <strong>'.round($pi["hasta_cantidad"],0) .'</strong> un, <strong>'.round($pi["monto_descuento"],0).'%</strong> OFF</p>';  
        }
        $detallito .="</span>";
        //mysqli_close($link);
       
        
        return $detallito;
}
function vigencia_promo_detalle($desde,$hasta){
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    setlocale(LC_TIME, 'spanish');
    if($desde!==null && $hasta!==null){
        $hastaM=strtotime('+3 month');
        //echo "hasta{".print_r(strtotime($hasta))."} hastam{ ".print_r($hastaM)."}";
        if(strtotime($hasta)<$hastaM){
             $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime($hasta)));
        }else{
             $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime('+3 month')));
        }
        $desdeTxt=utf8_encode(strftime("%A, %d de %B del %Y",strtotime('now')));
       
        
    }

    if($desde==null && $hasta==null){
         $desdeTxt=utf8_encode(strftime("%A, %d de %B del %Y",strtotime('now')));
        $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime('+3 month')));
     }
    if($desde==null && $hasta!==null){
        $hastaM=strtotime('+3 month');
        //echo "hasta{".print_r(strtotime($hasta))."} hastam{ ".print_r($hastaM)."}";
        if(strtotime($hasta)<$hastaM){
             $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime($hasta)));
        }else{
             $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime('+3 month')));
        }
        $desdeTxt=utf8_encode(strftime("%A, %d de %B del %Y",strtotime('now')));
//        $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime($hasta)));
    }
    if($desde!==null && $hasta==null){
        $desdeTxt=utf8_encode(strftime("%A, %d de %B del %Y",strtotime($desde)));
        $hastaTxt= utf8_encode(strftime("%A, %d de %B del %Y",strtotime('+3 month')));
    }
    
    
       
    
    
// inicio infinito pero con fin
    
        
    $textoFecha="<p>Válida del ".$desdeTxt." al ".$hastaTxt."</p>";
    return $textoFecha;
}