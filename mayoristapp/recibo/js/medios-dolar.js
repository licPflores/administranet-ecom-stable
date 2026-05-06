

    function trae_coti_dolar() {
        //var coti = $('#cotiDolarCobro');
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeCotiDolar: 1
            },
            success: function(data) {
                console.log(data);

                if (data.msg === "ok") {
                    // coti.numberbox('setValue', data.cotizacion);
                    varCotizacion = data.cotizacion;

                } else {
                    // error 
                    // coti.numberbox('setValue', 1);
                }

            }
        });
    }

    function dolar_a_peso() {
        let dolar, coti,pesos,continuar 
        dolar = $('#dolarCobro').numberbox('getValue');
        console.log("dolar" + dolar);
        continuar=false;
        coti = varCotizacion; // variable global
        console.log("cotizacion" + coti);
        pesos = dolar * coti;
        console.log("pesos dolar" + pesos);
        //$('#dolarApeso').numberbox('setValue',pesos);
        if (pesos !== 0 && pesos != "") {
            $('#dolarApeso').numberbox('setValue', pesos);
            console.warn('dolar_a_peso::',pesos);
           
            valida_dolar();
            console.warn('valor continuar::',continuar);
            $('#dolar-ok').linkbutton('enable');
            $('#dolar-cancel').linkbutton('enable');
           
        }

    }



    function valida_dolar() {
        console.warn('valida_dolar===');
        var pesosDolar =  $('#dolarApeso').numberbox('getValue');
        var vuelta; 
        
        let saldoRecibo = varTotalSaldo;
        let saldoFuturo = saldoRecibo -(parseFloat(pesosDolar));
        console.warn('Saldo Futuro',saldoFuturo,saldoRecibo,pesosDolar);
        // saldo en cero aviso
        
        
        if(saldoFuturo==0){
            Swal.fire({
                    title: '',
                    html: 'El monto es <strong>igual</strong> al Saldo del recibo. <br>Si carga otros medios, seran ingresados como <strong>RECIBO A CUENTA</strong>',
                    icon: 'warning'
                    });
        } 
        // dinero a cuenta
        if(saldoFuturo<0){
            
                Swal.fire({
                    title: '',
                    html: 'El monto <strong>supera</strong> el total del recibo.<br>Se generará un Recibo a cuenta por la diferencia $' + saldoFuturo*-1 ,
                    icon: 'warning'
                    
                    });
                
        }

        
    }
