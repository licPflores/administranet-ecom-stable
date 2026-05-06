<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* mas vendidos segun algun filtrito*/
require_once 'sesion.inc.php';
// recibo parametros
if(isset($_REQUEST['ajax'])){
    
}


function trae_mas_vendidos($connV,$categoria=null,$rubro=null,$subrubro=null,$tipoCliente=null){
    $condTipoCliente = "";
        if(is_object($objCliente)){
            $tipoCliente = $objCliente->TipoCliente;
            $condTipoCliente = " AND articulo_tipo_cliente.id_tipo_cliente =" .$tipoCliente;
            $condRubro = " AND articulo_tipo_cliente.id_tipo_cliente =" .$tipoCliente;
        }else{
            $condTipoCliente = "";
            $condRubro="";
        }
        // busco los rubros que puedan haaber
        $sqlTipoArt = "SELECT DISTINCT(articulo.CodigoRubro) as idRubro,rubro.NombreRubro
                        FROM articulo_tipo_cliente                
                        LEFT JOIN articulo ON articulo.IDArt = articulo_tipo_cliente.id_articulo
                        LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                        WHERE  
                        NOT ISNULL(articulo.CodigoRubro) 
                         ".$condRubro."
                        ORDER BY idRubro ASC";
        $rubros = array();
        $objArticulosTop = array();
        //unset($rubros);
        //echo "<pre>";
        //print_r($sqlTipoArt);
        //echo "</pre>";
        $hacerR = mysqli_query($conexionT,$sqlTipoArt) or die("No puedo recuperar los rubros".mysqli_error());
        while($r = mysqli_fetch_assoc($hacerR)){
            $rubros[] = $r;
        }
       
//        echo "<pre>";
//        print_r($rubros);
//        echo "</pre>";
        // voy a dar vueltas por cada rubro.
        foreach($rubros as $rr){
             //print_r($rr);
            $sqlTop = "SELECT 
                                COUNT(stock.IDArt) AS Cuantos,
                                articulo.id_manual,
                                articulo.IDArt,
                                articulo.IDSubRubro, 
                                articulo.CodigoSubRubro,
                                articulo.CodigoRubro,
                                articulo.Moneda,
                                articulo.CodigoArticuloT,
                                articulo.NombreArticulo,
                                articulo.impuesto_interno,
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
                                articulo.promocion_vigencia_desde,
                                articulo.promocion_vigencia_hasta,                        
                                articulo.tipoIVA,
                                iva.Alicuota AS Alic, 
                                rubro.NombreRubro AS NombRub, 
                                subrubro.NombreSubRubro AS NombSubRub,
                                articulo.Precio1V,
                                articulo.Precio2V,
                                articulo.Precio3V,
                                articulo.Precio4V,
                                articulo.Precio5V,
                                articulo.PNOficial, 
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
                                (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf

                                FROM articulo_tipo_cliente
                                    LEFT JOIN stock ON (stock.IDArt = articulo_tipo_cliente.id_articulo 
                                    AND stock.Anulado = 'No')
                                    LEFT JOIN articulo ON (articulo.IDArt = articulo_tipo_cliente.id_articulo)
                                    LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                                    LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                                    LEFT JOIN rubro ON Rubro.CodigoRubro = articulo.CodigoRubro
                                    LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                                    LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
                                WHERE 
                                    articulo.Discontinuo='No'
                                    AND articulo.tipo_art='Articulo' 
                                    ".$condTipoCliente."
                                    AND articulo.CodigoRubro = ".$rr["idRubro"]."    
                                    AND stock.TipoComp IN('Venta','Venta TPV')
                                GROUP BY stock.IDArt
                                ORDER BY  cuantos DESC ,articulo.CodigoRubro ASC 
                                LIMIT 4;";
            $hacerTop = mysqli_query($conexionT,$sqlTop) or die('No puedo recuperar los articulos mas vendidos'.mysqli_error($conexionT)."<pre>".$sqlTop."</pre>");


            while($artTop = mysqli_fetch_object($hacerTop)){
                $objArticulosTop[$rr["idRubro"]][] = $artTop;
            }
        }
    return $arrMas;
}
function trae_promociones($connV,$categoria=null,$rubro=null,$subrubro=null,$tipoCliente=null){
    $sqlArticulo = "SELECT 
                         articulo.id_manual,
                         marca.NombreMarca AS Marca,
                         modelo.NombreModelo AS Modelo,
                         articulo.IDArt,
                         articulo.IDSubRubro, 
                         articulo.CodigoSubRubro,
                         articulo.CodigoRubro,
                         articulo.Moneda,
                         articulo.CodigoArticuloT,
                         articulo.NombreArticulo,
                         articulo.impuesto_interno,
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
                         articulo.promocion_vigencia_desde,
                         articulo.promocion_vigencia_hasta,                        
                         articulo.tipoIVA,
                         iva.Alicuota AS Alic, 
                         rubro.NombreRubro AS NombRub, 
                         subrubro.NombreSubRubro AS NombSubRub,
                         articulo.Precio1V,
                         articulo.Precio2V,
                         articulo.Precio3V,
                         articulo.Precio4V,
                         articulo.Precio5V,
                         articulo.PNOficial, 
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
                         (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf

                         FROM articulo 
                             LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                             LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                             LEFT JOIN rubro ON Rubro.CodigoRubro = articulo.CodigoRubro
                             LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                             LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
                         WHERE 
                             articulo.Discontinuo='No'
                             AND articulo.tipo_art='Articulo' 
                             {$consultaLista}
                             AND articulo.promocion_destacado_web ='Si'
                             AND articulo.promocion_vigencia_desde<='".date('Y-m-d')."' 
                             AND articulo.promocion_vigencia_hasta>='".date('Y-m-d')."'    

                           ORDER BY articulo.NombreArticulo LIMIT 30;";
//                             print_r($sqlArticulo);

     $hacer = mysqli_query($conexionT,$sqlArticulo) or die('No puedo recuperar los articulos en promocion'.mysqli_error($conexionT).$sqlArticulo);
     $objArticulos = array();
     while($art = mysqli_fetch_object($hacer)){
         $objArticulos[] = $art;
     }
   
    return $arrPromos;
}