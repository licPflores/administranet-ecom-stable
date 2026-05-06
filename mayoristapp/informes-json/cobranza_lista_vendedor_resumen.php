<?php

require_once '../sesion.inc.php';

function trae_sql($codViajante,$total,$desde,$hasta){
    $desdeT= date('d/m/Y', strtotime($desde));
    $hastaT= date('d/m/Y', strtotime($hasta));     
    $where="";  
    if($codViajante!='todos'){
        $where  = " AND  cuentacliente.CodViajante={$codViajante} ";
    }

    if($total==0){
        // por mes
    
        $periodo="CONCAT(YEAR(cuentacliente.Fecha),DATE_FORMAT(cuentacliente.Fecha,'%m')) AS periodo,"
                . "YEAR(cuentacliente.Fecha) AS aaaa ,"
                . "MONTH(cuentacliente.Fecha) AS m, ";
        $groupBy="periodo";
    }else{
        // totalizado
        $periodo="'{$desdeT} al {$hastaT}' AS periodo,"
                . "YEAR(cuentacliente.Fecha) AS aaaa ,"
                . "MONTH(cuentacliente.Fecha) AS m,";
        $groupBy="cuentacliente.CodViajante";
    }
    $sql="SELECT
            {$periodo}
            SUM(IF(cuentacliente.TipoComprobante='REC',cuentacliente.TotalEfectivoP,cuentacliente.ImporteVenta)) as totalEfectivo, 
             SUM(cuentacliente.TotalEfectivoD) as totalDolar,
             SUM(cuentacliente.TotalCheque) as totalCheque,
             SUM(cuentacliente.total_trans) as totalTransferencia,
             SUM(cuentacliente.total_percep) AS totalPercep,
             SUM(IF(cuentacliente.TipoComprobante='REC',cuentacliente.ImporteCobro,cuentacliente.ImporteVenta)) as total
        FROM cuentacliente 
        WHERE 
        (cuentacliente.TipoComprobante = 'REC' OR 
                    cuentacliente.TipoComprobante = 'FA' OR 
                    cuentacliente.TipoComprobante = 'FB' OR 
                    cuentacliente.TipoComprobante = 'FM' OR 
                    cuentacliente.TipoComprobante = 'FE' OR 
                    cuentacliente.TipoComprobante = 'FC'
                  ) 
        AND (cuentacliente.Fecha BETWEEN '{$desde}' AND '{$hasta}')          
            {$where} 
            AND (cuentacliente.CodigoMovimiento <> 0) 
            AND (cuentacliente.Anulado='No')
            
            
            AND (cuentacliente.CondVenta ='Contado' OR 
                 cuentacliente.CondVenta ='-' ) 

GROUP BY {$groupBy}
ORDER BY cuentacliente.Fecha ASC";
  return $sql;  
    
}

function hacer_array($connV,$sql,$tipo){
    $meses=array(1=>"Enero",
        2=>"Febrero",
        3=>"Marzo",
        4=>"Abril",
        5=>"Mayo",
        6=>"Junio",
        7=>"Julio",
        8=>"Agosto",
        9=>"Septiembre",
        10=>"Octubre",
        11=>"Noviembre",
        12=>"Diciembre"
        );
    $array=array("columnas"=>
                array("Periodo",
                    "Efectivo",
                    "Dólares",
                    "Cheques",
                    "Transferencias","Percepciones","Total")                   
        );
    $hacer=mysqli_query($connV,$sql) or die("No pude recuperar cobranzas".mysqli_error($connV)."<pre>".$sql."</pre>");
    if($hacer){
        while($r= mysqli_fetch_assoc($hacer)){
            if($tipo==0){
                $array["renglon"][]=array(
                                        "periodo"=>$meses[$r["m"]]." ".$r["aaaa"],
                                        "ordenPeriodo"=>$r["periodo"],
                                        "efectivo"=>$r["totalEfectivo"],
                                        "dolar"=>$r["totalDolar"],
                                        "cheque"=>$r["totalCheque"],
                                        "transferencia"=>$r["totalTransferencia"],
                                        "percepcion"=>$r["totalPercep"],
                                        "total"=>$r["total"]

                );
            }
            if($tipo==1){
                //totalizado no busco el nombre del mes. 
                $array["renglon"][]=array(
                                        "periodo"=>$r["periodo"],
                                        "ordenPeriodo"=>1,
                                        "efectivo"=>$r["totalEfectivo"],
                                        "dolar"=>$r["totalDolar"],
                                        "cheque"=>$r["totalCheque"],
                                        "transferencia"=>$r["totalTransferencia"],
                                        "percepcion"=>$r["totalPercep"],
                                        "total"=>$r["total"]
                );
            }
        }
    }
    if(!$hacer){
        $array= array();
    }
    return $array;
    
}

function armar_html($array){
    // armo el html para hacer echo 
    $html ='<thead>';
    $html .='<tr><th>#</th>';
    foreach($array["columnas"] AS $col){
        if($col!=='Periodo'){
            $html .='<th class="dt-right">'.$col.'</th>';
        }
        if($col=='Periodo'){
            $html .='<th>'.$col.'</th>';
        }
    }
    $html .='</tr>';
    $html .='</head>';
    if(isset($array["renglon"])){
        // cargo entonces subtotales al pie y en los renglones.
        $pie = array("pie"=>"Total Gral",
                     "efectivo"=>0,
                     "dolar"=>0,
                     "cheque"=>0,
                     "transf"=>0,
                     "percep"=>0,
                     "total"=>0
            );
            $html .="<tbody>";
        foreach($array["renglon"] AS $k=>$rr){
            $html .='<tr><td></td>';
            $html .='<td  data-order="'.$rr["ordenPeriodo"].'">'.$rr["periodo"].'</td>';
            $html .='<td class="dt-right" data-order="'.number_format($rr["efectivo"],0,'','').'">$'.number_format($rr["efectivo"],2,',','.' ).'</td>';
            $html .='<td class="dt-right" data-order="'.number_format($rr["dolar"],0,'','').'">$'.number_format($rr["dolar"],2,',','.' ).'</td>';
            $html .='<td class="dt-right" data-order="'.number_format($rr["cheque"],0,'','').'">$'.number_format($rr["cheque"],2,',','.' ).'</td>';
            $html .='<td class="dt-right" data-order="'.number_format($rr["transferencia"],0,'','').'">$'.number_format($rr["transferencia"],2,',','.' ).'</td>';            
            $html .='<td class="dt-right" data-order="'.number_format($rr["percepcion"],0,'','').'">$'.number_format($rr["percepcion"],2,',','.' ).'</td>';
            $html .='<td class="dt-right" data-order="'.number_format($rr["total"],0,'','').'">$'.number_format($rr["total"],2,',','.' ).'</td>';
            $html .='</tr>';
            
            $pie["efectivo"] +=$rr["efectivo"];
            $pie["dolar"] +=$rr["dolar"];
            $pie["cheque"] +=$rr["cheque"];
            $pie["transf"] +=$rr["transferencia"];
            $pie["percep"] +=$rr["percepcion"];
            $pie["total"] +=$rr["total"];
            
        }
        // armo el pie si es que hay renglon.
        $html .='</tbody>';
        $html .='<tfoot>';
        $html .='<tr>';
        $html .='<th colspan="2">'.$pie["pie"].'</th>';
        $html .='<th class="dt-right">$'.number_format($pie["efectivo"],2,',','.').'</th>';
        $html .='<th class="dt-right">$'.number_format($pie["dolar"],2,',','.').'</th>';
        $html .='<th class="dt-right">$'.number_format($pie["cheque"],2,',','.').'</th>';
        $html .='<th class="dt-right">$'.number_format($pie["transf"],2,',','.').'</th>';
        $html .='<th class="dt-right">$'.number_format($pie["percep"],2,',','.').'</th>';
        $html .='<th class="dt-right">$'.number_format($pie["total"],2,',','.').'</th>';
        $html .='</tr>';
        $html .='</tfoot>';
    }
    return $html;
}


if(isset($_REQUEST["traeCobranza"])&&$_REQUEST["traeCobranza"]==1){
    // parametros.
    // cod vendedor
    $codViajante=$_REQUEST["codViajante"];
    $desde=$_REQUEST["fechaDesde"];
    $hasta=$_REQUEST["fechaHasta"];
    $tipo=$_REQUEST["tipo"];
    $sql= trae_sql($codViajante, $tipo, $desde, $hasta);
    //echo $sql;
    $arrDatos=hacer_array($connV,$sql,$tipo);
    if(!empty($arrDatos)){
        $htmlVuelta=armar_html($arrDatos);
        $jsonGrafico = $arrDatos;
        $vuelta = array('estado'=>'ok','tablaHtml'=>$htmlVuelta,'jsonGrafico'=>$jsonGrafico);
    }
    if(empty($arrDatos)){
        $vuelta = array('estado'=>'vacio','tablaHtml'=>array(),'jsonGrafico'=>array());
    }
    
    header('Content-Type: application/json');
    print json_encode($vuelta);
    
    
    
}