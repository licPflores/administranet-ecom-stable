SELECT
            cli.Codigo AS cod,CONCAT(cli.nombre_cliente,' (Cod: ',cli.Codigo,')')  As nom,arti.IDArt AS cod2,arti.NombreArticulo  As nom2,
            
            
            
            DAY(stock.Fecha)as dia,
            WEEKOFYEAR(stock.Fecha) as semana,
            MONTH(stock.Fecha) as mes2,
            DATE_FORMAT(stock.Fecha,'%m') as mes,
            YEAR(stock.Fecha) AS aa,
  
            DATE_FORMAT(
            STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),
            'Monday'),'%X%V %W'),'%d/%m') AS PrimerDiaSemana,  
            DATE_FORMAT(
            STR_TO_DATE(CONCAT(YEARWEEK(stock.Fecha),
            'Saturday'),'%X%V %W'),'%d/%m') AS UltimoDiaSemana,   
             SUM( IF(
                stock.TipoComp ='Venta' OR
                stock.TipoComp ='Venta TPV' OR                 
                stock.TipoComp ='ND Anul NC'
            ,IF(CAST(Stock.Detalle AS DECIMAL)=0,
    		CEIL(stock.Cantidad /cantidad_promedio_bulto),
            CAST(Stock.Detalle AS DECIMAL)),(IF(CAST(Stock.Detalle AS DECIMAL)=0,
    		CEIL(stock.Cantidad /cantidad_promedio_bulto),
            CAST(Stock.Detalle AS DECIMAL))) * -1)) AS total 
            FROM stock
                LEFT JOIN cuentacliente AS cc ON (cc.CodigoMovimiento= stock.CodigoMovimiento) 
                LEFT JOIN articulo AS arti ON arti.IDArt = stock.IDArt               
                LEFT JOIN rubro AS ru ON ru.CodigoRubro = arti.CodigoRubro
                LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=ru.id_categoria
                LEFT JOIN subrubro AS srub ON srub.IDSubRubro = arti.IDSubRubro
                LEFT JOIN marca ON marca.CodMarca=arti.CodigoMarca
                LEFT JOIN proveedor AS prov ON prov.Codigo = arti.CodigoProveedor
                LEFT JOIN cliente AS cli ON (cli.Codigo= stock.CodigoCP)
                LEFT JOIN viajantes AS vend ON (vend.CodViajante= cli.CodViajante)
                LEFT JOIN erp_zona AS zonas ON (zonas.id_zona=cli.id_zona)
                LEFT JOIN punto_venta AS ppv ON ( ppv.id_punto_venta=cc.id_pv)
                LEFT JOIN tipo_cliente AS tpcli ON(tpcli.IDTipoCliente=cli.TipoCliente)
           WHERE
                ( (stock.Fecha BETWEEN '2020-09-01' AND '2020-10-31') )
               
               AND stock.anulado='No'
               AND stock.visualiza_ensamble='No'
               
                AND (stock.TipoComp = 'Venta' 
                    OR stock.TipoComp = 'Venta TPV' 
                    OR stock.TipoComp = 'Devol - Cliente' 
                    OR stock.TipoComp = 'ND Anul NC'                   
                    )
                  AND cli.Codigo IN (21013) AND arti.IDArt IN (20641)    

            GROUP BY semana,mes,cli.Codigo ORDER BY cli.nombre_cliente ASC,  stock.Fecha ASC