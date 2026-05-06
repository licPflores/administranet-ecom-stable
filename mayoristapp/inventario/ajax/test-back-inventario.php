<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soy una prueba de inventario.</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <button type="button" onclick="guardarMovimiento()"> Mandar Contada</button>
</body>
</html>
<script>
function guardarMovimiento(){
    let varIdArticulo,varFecha,varTipoCuenta,varCantidadContada,varCantidadMinimaContada,varSaldoDeposito,varIdDeposito,varUsaLote;
    // $arrParam['fecha'];
    // $arrParam['idArticulo'];
    // $arrParam['cantidadContada'];
    // $arrParam['cantidadMinimaContada'];  
    // $arrParam['saldoDeposito'];
    // $arrParam['idDeposito'];
    // $arrParam['tipoCuenta]
    // $arrParam['usaLote'];
    // $arrParam['idSaldoLote'];
    // $arrParam['idLote'];
    // $arrParam['saldoLote'];
    varIdArticulo = 69;
    varFecha='11/07/2023';
    varTipoCuenta='Display';//'Unidad/Display/Bulto'
    varCantidadContada=5;
    varCantidadMinimaContada= 250;
    varSaldoDeposito = -3000;
    varIdDeposito =1;
    varUsaLote='No';
    $.ajax({
        type: 'GET',
            url: 'stock-backend.php',
            data: {
                altaMovimiento: 1,
                idArticulo:varIdArticulo,
                fecha:varFecha,
                presentacion: varTipoCuenta,
                cantidadContada: varCantidadContada,
                cantidadMinimaContada:varCantidadMinimaContada,
                saldoDeposito: varSaldoDeposito,
                idDeposito: varIdDeposito,
                usaLote: varUsaLote
            },
            dataType: 'json',
            success: function(data) {
                console.log('alta de movimiento::',data);
                alert('volvi pero nose como me fue.');

            }
       
    });
}


</script>