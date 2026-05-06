<?php
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();
        $sqlPedido="SELECT 
                            comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            comp_ped.Fecha,
                            comp_ped.NroComprobante,
                            comp_ped.SubTotalDesc,
                            comp_ped.IVA1,
                            comp_ped.IVA2,
                            comp_ped.Exento,
                            comp_ped.CondVenta,
                            comp_ped.FechaEntrega,
                            comp_ped.FormaEntrega,
                            comp_ped.Estado,
                            comp_ped.Anulado,
                            (comp_ped.IVA1+
                            comp_ped.IVA2)AS IVA,
                            (comp_ped.SubTotalDesc+
                            comp_ped.IVA1+
                            comp_ped.IVA2) AS Total
                            
                    FROM 
                        comp_ped
           
                    WHERE 
                    
                    comp_ped.TipoComprobante ='PED'
                    AND comp_ped.`Codigo`=".$_SESSION['idcliente']." 
                     
                    ORDER BY comp_ped.id_comp_ped";
        $hacerPed = mysql_query($sqlPedido) or die('No puedo recuperar el pedido'.mysql_error().'<br>'.$sqlPedido);
//        echo $sqlPedido.'<br>';
        while($pedido = mysql_fetch_object($hacerPed)){
            $pedidos[] = $pedido;
        }
?>
