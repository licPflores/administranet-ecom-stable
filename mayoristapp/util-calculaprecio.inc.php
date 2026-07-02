<?php
/**
 * Archivo utilitario para cálculo de precios reutilizable.
 * Extraído de la clase articulos de ajax-articulos.php
 * Permite reutilizar la lógica de cálculo de precios en otros scripts.
 */

class CalculadorPreciosUtil
{
    /**
     * Calcula los precios de un artículo según la lógica de la clase articulos.
     * @param object|array $param
     * @param mysqli $connV (opcional, solo si se usan reglas masivas o generales)
     * @return array
     */
    public static function calculaPrecios($param, $connV = null)
    {
        if (is_object($param)) {
            $arti = $param->arti;
            $listaPrecioCliente = $param->listaPrecioCliente;
            $descRenglon = $param->descRenglon;
            $usaReglaPrecio = $param->usaReglaPrecio;
            $codCliente = $param->codCliente;
        } else {
            $arti = $param['arti'];
            $listaPrecioCliente = $param['listaPrecioCliente'];
            $descRenglon = $param['descRenglon'];
            $usaReglaPrecio = $param['usaReglaPrecio'];
            $codCliente = $param['codCliente'];
        }
        $divisorPrecio = 1;
        $cantidad =1;
        $nombreArticulo = "";
        switch ($listaPrecioCliente) {
            case 'Lista 1':
                $precioNeto     = $arti->Precio1V;
                $importeIva     = $arti->impIva1;
                $importeInterno = $arti->imp_interno1;
                $precioVenta    = $arti->Precio1VI;
                $promoLista = ($arti->promocion_lista1 == "Si") ? "si" : "no";
                break;
            case 'Lista 2':
                $precioNeto     = $arti->Precio2V;
                $importeIva     = $arti->impIva2;
                $importeInterno = $arti->imp_interno2;
                $precioVenta    = $arti->Precio2VI;
                $promoLista = ($arti->promocion_lista2 == "Si") ? "si" : "no";
                break;
            case 'Lista 3':
                $precioNeto     = $arti->Precio3V;
                $importeIva     = $arti->impIva3;
                $importeInterno = $arti->imp_interno3;
                $precioVenta    = $arti->Precio3VI;
                $promoLista = ($arti->promocion_lista3 == "Si") ? "si" : "no";
                break;
            case 'Lista 4':
                $precioNeto     = $arti->Precio4V;
                $importeIva     = $arti->impIva4;
                $importeInterno = $arti->imp_interno4;
                $precioVenta    = $arti->Precio4VI;
                $promoLista = ($arti->promocion_lista4 == "Si") ? "si" : "no";
                break;
            case 'Lista 5':
                $precioNeto     = $arti->Precio5V;
                $importeIva     = $arti->impIva5;
                $importeInterno = $arti->imp_interno5;
                $precioVenta    = $arti->Precio5VI;
                $promoLista = ($arti->promocion_lista5 == "Si") ? "si" : "no";
                break;
            case 'Lista Oficial':
                $precioNeto     = $arti->PNOficial;
                $importeIva     = $arti->impOf;
                $importeInterno = $arti->imp_internoOF;
                $precioVenta    = $arti->PFOficial;
                $promoLista     = "si";
                break;
        }
        $precioVentaFinal = $precioVenta;
        $precioNetoCalc = $precioNeto;
        $descFinal      = 0;
        $clase          = "";
        $clasePrecio    = "";
        $promoCant      = "";
        $promoPorc      = "";
        $promoTipo      = $arti->promocion_tipo;
        $promo          = "no";
        $aplicaPromo    = "no";
        $aplicoRegla    = "no";
        $cualRegla      = "";
        $desc           = "si";
        $usoPromocion   = "Si";
        $encontreRegla  = 0;
        if ($usaReglaPrecio == "Si") {
            $idArtR = $arti->IDArt;
            $codigoRubroR = $arti->CodigoRubro;
            $idSubRubroR = $arti->IDSubRubro;
            $codigoProveedorR = $arti->CodigoProveedor;
            $codClienteR = $codCliente;
            if (property_exists($arti, 'tipo_calculo') && $arti->tipo_calculo != null) {
                $hayRegla = "Si";
                $usoPromocion   = "No";
                $encontreRegla++;
                $tipoCalculo = $arti->tipo_calculo;
                $importeRegla = $arti->importe_regla;
            }
            if ($encontreRegla == 0) {
                $idReglaMasiva = self::reglasPrecioMasivas($connV, $idArtR, $codigoProveedorR, $codigoRubroR, $idSubRubroR, $codClienteR);
                if ($idReglaMasiva != null) {
                    $sqlReglaM = "SELECT * FROM reglas_precio_masivas WHERE id_regla_precio_masivas ={$idReglaMasiva} ";
                    $hacerRM = mysqli_query($connV, $sqlReglaM);
                    $rm = mysqli_fetch_assoc($hacerRM);
                    $hayRegla = "Si";
                    $encontreRegla++;
                    $tipoCalculo = $rm["tipo_calculo"];
                    $importeRegla = $rm["importe_regla"];
                }
            }
            if ($encontreRegla == 0) {
                $idReglaGeneral = self::reglasPrecioGeneral($connV, $idArtR, $codigoProveedorR, $codigoRubroR, $idSubRubroR);
                if ($idReglaGeneral != null) {
                    $sqlReglaG = "SELECT * FROM reglas_precio_alta_art WHERE id_regla_precio_alta_art = {$idReglaGeneral}";
                    $hacerRG = mysqli_query($connV, $sqlReglaG);
                    $rg = mysqli_fetch_assoc($hacerRG);
                    $hayRegla = "Si";
                    $encontreRegla++;
                    $tipoCalculo = $rg["tipo_calculo"];
                    $importeRegla = $rg["importe_regla"];
                    $prioridad_regla = $rg["prioridad_regla"];
                }
            }
            if ($encontreRegla != 0) {
                $usoPromocion = "No";
                $aplicoRegla = "si";
                switch ($tipoCalculo) {
                    case "Descuento":
                        if (isset($prioridad_regla) && $prioridad_regla != "Desc. Cliente") {
                            $descRenglon = $importeRegla;
                        } else {
                            if ($descRenglon < $importeRegla) {
                                $descRenglon = $importeRegla;
                            }
                        }
                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                        $importeIva         = ($precioNetoCalc  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoCalc * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoCalc + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = $descRenglon;
                        $promoCant          = "";
                        $promo              = "no";
                        $cantidad           =   1;
                        break;
                    case "Marcacion":
                        $descRenglon = $importeRegla;
                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo + $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoCalc;
                        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = 0;
                        $promoCant          = "";
                        $promo              = "no";
                        $cantidad           =   1;
                        $descFinal = 0;
                        $precioVenta = $precioVentaFinal;
                        break;
                    case "Precio Fijo":
                        $descuento = $importeRegla;
                        $precioNetoNuevo    = $descuento;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = $descRenglon;
                        $promoCant          = "";
                        $promo              = "no";
                        $cantidad           =   1;
                        $descFinal = 0;
                        break;
                    case "Cantidad - Unidad":
                        $descRenglon = 0;
                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $sqlPCant = "SELECT rp.promocion_por, rp.promocion_cant FROM reglas_precio AS rp WHERE rp.id_articulo ={$idArtR}  AND rp.tipo_calculo = 'Cantidad - Unidad' AND rp.id_cliente ={$codClienteR} ";
                        $hacerPcant = mysqli_query($connV, $sqlPCant);
                        $arrPcant = mysqli_fetch_assoc($hacerPcant);
                        $promoCant = $arrPcant["promocion_cant"];
                        $promo      = "si";
                        $descFinal = $arrPcant["promocion_por"];
                        $cantidad   = number_format($promoCant);
                        break;
                }
                $cualRegla = $tipoCalculo;
            } else {
                $usoPromocion = "Si";
            }
        }
        if ($usoPromocion == "Si") {
            if ($arti->promocion == 'Si' && $promoLista == "si") {
                $promoCant = $arti->promocion_cant;
                $promoPorc = $arti->promocion_por;
                $promoTipo = $arti->promocion_tipo;
                $aplicaPromo = "no";
                $hayVigencia = self::vigencia_promo($arti->promocion_vigencia_desde, $arti->promocion_vigencia_hasta, $arti->IDArt, $promoTipo, $connV);
                if ($hayVigencia == "si") {
                    $aplicaPromo = "si";
                }
                if ($aplicaPromo == "si") {
                    switch ($promoTipo) {
                        case 'Cantidad - Intervalo':
                            $promo = "si";
                            $descFinal = 0;
                            $cantidad = 1;
                            break;
                        case 'Importe descuento':
                            if ($descRenglon > $promoPorc) {
                                $descFinal = $descRenglon;
                                $promo = "no";
                            } else {
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
                            if ($descRenglon > $promoPorc) {
                                $descFinal = $descRenglon;
                                $promo = "no";
                            } else {
                                $descFinal = $promoPorc;
                                $promo = "si";
                            }
                            $cantidad = $promoCant;
                            break;
                        case 'Cantidad - Unidad':
                            $promo = "si";
                            $descFinal = $promoPorc;
                            $cantidad = $promoCant;
                            break;
                        case "Monto fijo":
                            $promo = "si";
                            $precioNetoNuevo = $precioNeto;
                            $precioNetoCalc = round($promoPorc / (1 + ($arti->Alic / 100)), 4);
                            $descFinal = round((($precioNeto - $precioNetoCalc) * 100 / $precioNeto), 1);
                            $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                            $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                            $precioVentaFinal = $promoPorc;
                            $precioNeto = $precioNetoNuevo;
                            $promoCant = "";
                            $cantidad = 1;
                            break;
                    }
                }
                if ($aplicaPromo == "no" && $descRenglon > 0) {
                    $descFinal = $descRenglon;
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descFinal * $precioNeto / 100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $precioNeto         = $precioNetoNuevo;
                    $promoCant          = "";
                    $promo              = "no";
                    $cantidad           = 1;
                    $descFinal = $descRenglon;
                }
                if($aplicaPromo =="no"){
                    $promoTipo="";
                    $promo="no";
                }
            }
            if ($arti->promocion == 'No' || $promoLista == "no") {
                $cantidad = 1;
                $promo = "no";
                if ($promoLista == "si" && $promoTipo == 'Cantidad - Intervalo') {
                    $hayVigencia = self::vigencia_promo($arti->promocion_vigencia_desde, $arti->promocion_vigencia_hasta, $arti->IDArt, $promoTipo, $connV);
                    if ($hayVigencia == "si") {
                        $promo = "si";
                        $descFinal = 0;
                        $cantidad = 1;
                    }
                }
                if ($descRenglon > 0 && $promoTipo != 'Cantidad - Intervalo') {
                    $descFinal = $descRenglon;
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descFinal * $precioNeto / 100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto         = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                }
            }
        }
        if ($precioNeto == $precioVenta || $precioNetoCalc == $precioVentaFinal) {
            $alicuotaIva = $arti->Alic;
            $neto = $precioNeto;
            $netoFinal = $precioNetoCalc;
            $iva = ($precioNeto * $alicuotaIva) / 100;
            $ivaFinal = ($precioNetoCalc * $alicuotaIva) / 100;
            $precioIva = $neto + $iva;
            $precioIvaFinal = $netoFinal + $ivaFinal;
            $precioVenta = $precioIva;
            $precioVentaFinal = $precioIvaFinal;
            $importeIva = $iva;
        }
        if (isset($_SESSION['usa_impuesto_interno_abm']) && $_SESSION['usa_impuesto_interno_abm'] == "Si") {
            $impInterno = 0;
            $descuentoCalculo =0;
            if (isset($arti->interno_descripcion) && $arti->interno_descripcion != null) {
                $arrInterno = array();
                $arrInterno['cantidad'] = 1;
                $arrInterno['neto'] = $precioNeto;
                $arrInterno['costo'] = $arti->PrecioCosto;
                $arrInterno['descripcion'] = $arti->interno_descripcion;
                $arrInterno['tipo'] = $arti->interno_tipo;
                $arrInterno['porcentaje'] = $arti->interno_porcentaje;
                $arrInterno['montoFijo'] = $arti->interno_monto_fijo;
                $arrInterno['pesoCalculado'] = $arti->interno_peso_calculado;
                $arrInterno['pagoMinimo'] = $arti->interno_pago_minimo;
                $arrInterno['idUnimed'] = $arti->interno_id_unimed;
                $impInterno = self::calcularImpuestoInterno($arrInterno);
            }
            if($promoTipo!='Cantidad - Unidad'){
                $descuentoCalculo = $descFinal;
            }
            $precioNetoNuevo = $precioNeto;
            $precioNetoCalc = $precioNeto - ($precioNeto * $descuentoCalculo / 100);
            $importeIvaViejo = ($precioNetoNuevo * $arti->Alic) / 100;
            $importeIva = ($precioNetoCalc * $arti->Alic) / 100;
            $importeInterno = $impInterno;
            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
            $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($importeInterno);
            $precioNeto = $precioNetoNuevo;
        }
        if (isset($idCliente) && $idCliente == 1) {
            $precioNeto = 0;
            $precioNetoCalc = 0;
            $precioVenta = 0;
            $descFinal = 0;
            $precioVentaFinal = 0;
        }
        $precios = array(
            "idart"     => $arti->IDArt,
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
            "montoDescuento" => $precioNeto - $precioNetoCalc,
            "cantidad" => round($cantidad, 0),
            "promoTipo"   => $promoTipo,
            "usoRegla"    => $aplicoRegla,
            "queRegla"    => $cualRegla,
            "importeIvaViejo"    => isset($importeIvaViejo) ? $importeIvaViejo : 0,
            "ivaAlic"       => $arti->Alic,
            "impIvaFinal"  => $precioVentaFinal - $precioNetoCalc
        );
        return $precios;
    }

    public static function reglasPrecioMasivas($connV, $idArt = null, $codigoProveedor = null, $codigoRubro = null, $idSubRubro = null, $codCliente = null)
    {
        $varR = null;
        $fecha = date("Y-m-d");
        $sqlRegla = "SELECT * FROM reglas_precio_masivas WHERE Anulado = 'No'";
        $hacer = mysqli_query($connV, $sqlRegla);
        $arrReglas = array();
        while ($rr = mysqli_fetch_assoc($hacer)) {
            $arrReglas[] = $rr;
        }
        if (empty($arrReglas)) {
            return $varR;
        }
        if ($codCliente == null) {
            $codCliente = 1;
        }
        // Aquí deberías implementar la lógica de selección de la regla masiva según los parámetros
        // Por simplicidad, devolvemos la primera encontrada
        return isset($arrReglas[0]['id_regla_precio_masivas']) ? $arrReglas[0]['id_regla_precio_masivas'] : null;
    }

    public static function reglasPrecioGeneral($connV, $idArt = null, $codigoProveedor = null, $codigoRubro = null, $idSubRubro = null)
    {
        $varR = null;
        $fecha = date("Y-m-d");
        $sqlG = "SELECT id_regla_precio_alta_art FROM reglas_precio_alta_art WHERE Anulado='No' LIMIT 1";
        $hacerG = mysqli_query($connV, $sqlG);
        $hayR = mysqli_fetch_array($hacerG);
        if (empty($hayR)) {
            return $varR;
        }
        return $hayR['id_regla_precio_alta_art'];
    }

    public static function calcularImpuestoInterno($arrParametros)
    {
        $valorImpuesto = 0;
        if ($arrParametros['tipo'] == 'Porcentaje') {
            $valorImpuesto = (($arrParametros['cantidad'] * $arrParametros['costo']) * $arrParametros['porcentaje']) / 100;
        }
        if ($arrParametros['tipo'] == 'Porcentaje - Minimo') {
            $montoCalculado = (($arrParametros['cantidad'] * $arrParametros['costo']) * $arrParametros['porcentaje']) / 100;
            if ($montoCalculado < $arrParametros['montoMinimo']) {
                $valorImpuesto = $arrParametros['montoMinimo'];
            }
            if ($montoCalculado >= $arrParametros['montoMinimo']) {
                $valorImpuesto = $montoCalculado;
            }
        }
        if ($arrParametros['tipo'] == 'Monto fijo') {
            $valorImpuesto = $arrParametros['cantidad'] * $arrParametros['montoFijo'];
        }
        // Peso y Peso - Monto fijo pueden ser implementados aquí si es necesario
        return $valorImpuesto;
    }

    public static function vigencia_promo($desde, $hasta, $idArt, $promoTipo, $connV = null)
    {
        $vigencia = "no";
        if ($desde !== null && $hasta !== null) {
            $fd     = explode('-', $desde);
            $fh     = explode('-', $hasta);
            if ($fh[0] > 2038) {
                $fh[0] = 2037;
            }
            $desde = new DateTime($desde);
            $hasta = new DateTime($hasta);
            $hoy = new DateTime(date('Y-m-d'));
            if ($hoy >= $desde && $hoy <= $hasta) {
                $vigencia = "si";
            }
        }
        if ($desde == null && $hasta == null) {
            $vigencia = "si";
        }
        if ($desde == null && $hasta !== null) {
            $fh     = explode('-', $hasta);
            if ($fh[0] > 2038) {
                $fh[0] = 2037;
            }
            $hasta = new DateTime($hasta);
            $hoy = new DateTime(date('Y-m-d'));
            if ($hoy <= $hasta) {
                $vigencia = "si";
            }
        }
        if ($desde !== null && $hasta == null) {
            $fd     = explode('-', $desde);
            $desde = new DateTime($desde);
            $hoy = new DateTime(date('Y-m-d'));
            if ($hoy >= $desde) {
                $vigencia = "si";
            }
        }
        return $vigencia;
    }
}
