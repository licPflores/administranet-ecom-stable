
// cambiar libreria de graficos y ver que se puede hacer. visible.
        // sacar todo esto hacia un archivo js de informes gerenciales.
        // google.load('visualization', '1', null); // No 'packages' section.

        // function drawChart(dataG, optionsG, tipo, quien) {
        //     var wrap = new google.visualization.ChartWrapper();
        //     wrap.setChartType(tipo);
        //     wrap.setContainerId(quien);
        //     wrap.setDataTable(dataG);
        //     wrap.setOptions(optionsG);
        //     wrap.draw();
        // }


        

        

        $.extend($.fn.dataTable.defaults, {
            "language": {
                "emptyTable": "No data available in table",
                "info": "Viendo _START_ de _END_ de _TOTAL_ resultados",
                "infoEmpty": "Viendo 0 de 0 de 0 resultados",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "infoPostFix": "",

                "lengthMenu": "Ver _MENU_ entradas",
                "loadingRecords": "Loading...",
                "processing": "Processing...",
                "search": "Buscar:",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
            "searching": true,
            "ordering": true,
            "paging": true,
            "responsive": false
        });

        $(document).ready(function() {
            
            $('#parametrosInformes').on('click', function() {
                // console.log('hago click en la busqueda avanzada---------');
                var divAvanzado = $(".panelesBloqueInforme");
                if ($(this).hasClass('fa-angle-down') === true) {
                    // console.log('tenglo clase angle down----');
                    $(this).removeClass('fa-angle-down').addClass('fa-angle-up');
                    divAvanzado.show();
                } else {
                    // console.log('tenglo clase angle UP----');
                    $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
                    divAvanzado.hide();
                }

            });

            // funcion para las comas
            var buttonCommonExcel = {
                exportOptions: {
                    columns: 'th:not(:first-child)',

                    modifier: {
                        order: 'index'
                    },

                    orthogonal: {
                        display: ':null'
                    }


                    //            format: {
                    //                body: function ( data, row, column, node ) {
                    //                    // Strip $ from salary column to make it numeric
                    ////                    return column === 5 ?
                    ////                        data.replace( /[$,]/g, '' ) :
                    ////                        data;
                    //                    console.log({node});
                    ////                    console.log({row}); 
                    ////                    console.log({column});
                    //                        //data.replace( /[$,]/g, '' ); 
                    //                        console.log("data sin peso formateado: "+data.text());
                    ////                        console.log($.fn.dataTable.render.number(',', '', 2, '').display(data));
                    //                    if(!isNaN(data)){
                    //                       // console.log("data formateado: "+dinero.format(data));
                    //                        data =data.replace( /[$,]/g, '' ); 
                    //                        
                    //                        //data=dinero.format(data);
                    //                        
                    //                        //data=numerito.format(data);
                    //                        console.log("data formateado: "+data);
                    //                        
                    //                        //b=a.replace(/ /g, "");
                    ////                        data = '$ '+data;
                    ////                        console.log("data sin el espacio del pesos"+data);
                    //                        
                    //                    }
                    //                    return data;
                    //                },
                    //                footer: function ( data, row, column, node ) {
                    //                    
                    //                    if(!isNaN(data)){
                    //                       // console.log("data formateado: "+dinero.format(data));
                    //                        data=numerito.format(data);
                    //                        
                    //                    }
                    //                    return data;
                    //                }
                    //            }
                }
            };


            // aca atacch a los eventos del spinner funcionando.
            //      $("#spinner").bind("ajaxSend", function() {
            //            $(this).show();
            //        }).bind("ajaxStop", function() {
            //            $(this).hide();
            //        }).bind("ajaxError", function() {
            //            $(this).hide();
            //        });

            // para que se borren lo que tienen adentro las fechas   
            // $('#fechaDesde').val('dd/mm/aaaa');
            //$('#fechaHasta').val('dd/mm/aaaa');
            // * al cambiar el tipo de filtro     
            $('#filtrarPor').change(function() {
                
                var filtro = $(this).val(),
                    botonAgregar  = $('addFiltro');
                    listado = $('#seleccionFiltro');

                    // console.log('filtrar por',filtro);

                if (filtro == "") {
                  
                    Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar un Filtro",
                        });
                    return false;
                }
                

                $.ajax({
                    type: 'GET',
                    url: 'relay-ventas-netas-gerencia.php',
                    data: {
                        "ajax": "true",
                        "tabla": filtro,
                        "queInforme": "seleccion"


                    },
                    success: function(response) {
                        // habilito para escribir.
                        listado.prop('disabled',false);                    
                        var listaVuelta = jQuery.parseJSON(response);
                        listado.val("");
                                            // console.log(listaVuelta);
                        //                    listado.empty();
                        //                    listado.html('<option value="">- todos -</option>');
                        //                    listado.append(response);
                        $("#seleccionFiltro").autocomplete({
                           // source: listaVuelta,
                           //source: function(request, response) {
                            source: function(request, response) {
                                var results = $.ui.autocomplete.filter(listaVuelta, request.term);
                        
                                response(results.slice(0, 10));
                            
                        },
                            minLength: 2,
                            select: function(event, ui) {
                                event.preventDefault();
                                                
                                $(this).attr("alt", ui.item.value);
                                $(this).val(ui.item.label);
                                

                                
                            }
                            // Agregar clase al elemento seleccionado del Autocomplete
                            
                        });
                        listado.focus();
                    },
                    error: function(x, e) {
                        var s = x.status,
                            m = 'Ajax error: ';
                        if (s === 0) {
                            m += 'Check your network connection.' + x.status + e;
                        }
                        if (s === 404 || s === 500) {
                            m += s;
                        }
                        if (e === 'parsererror' || e === 'timeout') {
                            m += e;
                        }
                        alert(m);
                    }
                });
            });

            

            // BOTONES DISPARADOR DE BUSQUEDAS
            var textoGenerar = '<i class="fas fa-check fa-lg fa-fw"></i> Generar';
            var textoEspere = '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere...';

           // * VENTAS DE GERENCIA ACCION DE BUSCAR
           $('#botonBuscar').click(function() {
            var botonBuscar = $('#botonBuscar');

            botonBuscar.attr('disabled', true);
            botonBuscar.html(textoEspere);
            


            var contienesVentas = $('#myTableVentas'),
                panelVentas = $('#contiene-tabla'),
                nombreVentas = "#myTableVentas",
                contienesVentasRubro = $('#myTableVentasRubro'),
                nombreVentasRubro = "#myTableVentasRubro",
                fechaDesde = $('#fechaDesde').val(),
                fechaHasta = $('#fechaHasta').val(),
                fechaDesdeDos = $('#fechaDesdeDos').val(),
                fechaHastaDos = $('#fechaHastaDos').val(),
                rangoDoble = 0,
                opRango = $('#tipoOperacion').val(),
                datasetGrafico = "",
                tipoResumen = $('#campoPeriodo').val(),
                tipo = $('#verInforme').val(),
                tipoDisplayBulto = $('#verInformeDisplayBulto').val(),
                listarPor = $('#agrupoPor').val(),
                filtrarPor = $('#filtroSelec').val(),
                decimales = $('#decimales').val(),
                artEnsambleVenta = $('#artEnsamblados').val(),
                simbDer = "",
                simbIzq = "",
                tituloInforme= 'Ventas Gerenciales',
                vuelta = 0,
                grafico = 0,
                puntoVenta = $('#pvSelec').val(),
                verGrafico = $('#aceptaGrafico');


            //console.log("idusuario:{"+idUsuario+"} empresa:{"+empresa+"} idempresa:{"+idEmpresa+"}");	

            /* valido los filtros al menos el del punto de venta no puede
             * venir vacio
             * */


            if (tipo == "") {
                // alert("Debe seleccionar tipo de información ");
                Toast.fire({
                    icon: "warning",
                    text: "Debe seleccionar tipo de información",
                    });

                return false;
                
            }

            if (listarPor == "") {
                // alert("Debe seleccionar un campo por el cual listar el informe");
                Toast.fire({
                    icon: "warning",
                    text: "Debe seleccionar un campo por el cual listar el informe",
                    });
                return false;
               
            }
            if (tipoResumen == "") {
                // alert("Debe seleccionar un período de resumen");
                Toast.fire({
                    icon: "warning",
                    text: "Debe seleccionar un período de resumen",
                    });
                return false;
                
            }

            // validacion de fechas segun operacion.
            if (fechaDesde == "") {
                // alert("fecha DESDE rango primario inválida.\n Coloque una fecha correcta.");
                Toast.fire({
                    icon: "warning",
                    text: "fecha DESDE rango primario inválida.\n Coloque una fecha correcta.",
                    });
                return false;
                
            }
            if (fechaHasta == "") {
                // alert("fecha HASTA rango primario inválida.\n Coloque una fecha correcta.");
                Toast.fire({
                    icon: "warning",
                    text: "fecha HASTA rango primario inválida.\n Coloque una fecha correcta.",
                    });
                return false;
                
            }

            var numDesde, numHasta, numDesdeDos, numHastaDos;
            var fDesde = new Date(fechaDesde);
            var fHasta = new Date(fechaHasta);

            numDesde = fDesde.getTime();
            numHasta = fHasta.getTime();
            //            console.log("------contiene fechas------");
            //            console.log(numDesde);
            //            console.log(numHasta);
            if (numDesde > numHasta) {
                // alert("Rango primario inválido, fecha DESDE superior a fecha HASTA");
                Toast.fire({
                    icon: "warning",
                    text: "Rango primario inválido, fecha DESDE superior a fecha HASTA",
                    });
                return false;
            }
            //            console.log("-------operacion Rango---------");
            //            console.log(opRango);
            // si es suma solo rango uno.
            if (opRango === "sumag" || opRango === "resta") {
                // controlar el rango dos
                rangoDoble = 1;
                if (fechaDesdeDos === "") {
                    // alert("Fecha DESDE rango Secundario inválida.\n Coloque una fecha correcta");
                    Toast.fire({
                    icon: "warning",
                    text: "Fecha DESDE rango Secundario inválida.\n Coloque una fecha correcta",
                    });
                    return false;
                    
                }

                if (fechaHastaDos === "") {
                    // alert("Fecha HASTA rango Secundario inválida.\n Coloque una fecha correcta");
                    Toast.fire({
                    icon: "warning",
                    text: "Fecha HASTA rango Secundario inválida.\n Coloque una fecha correcta",
                    });
                    return false;
                    
                }

                var fDesdeDos = new Date(fechaDesdeDos);
                var fHastaDos = new Date(fechaHastaDos);
                numDesdeDos = fDesdeDos.getTime();
                numHastaDos = fHastaDos.getTime();
                // valido que el rango secundario estae bien desde y hesta.
                if (numDesdeDos > numHastaDos) {

                    // alert("Rango secundario inválido, fecha DESDE superior a fecha HASTA");
                    Toast.fire({
                    icon: "warning",
                    text: "Rango secundario inválido, fecha DESDE superior a fecha HASTA",
                    });
                    return false;

                }
                // el desde del periodo secundario es menor que el hasta del periodo primario.
                // por lo que el rango secunadrio no quedo consecutivo.

                if (numDesdeDos < numHasta) {
                    // alert("Rango secundario incorrecto, debe colocar un rango superior al rango primario.\n");
                    Toast.fire({
                    icon: "warning",
                    text: "Rango secundario incorrecto, debe colocar un rango superior al rango primario",
                    });
                    return false;
                }

                //                console.log("numDesdeDos::"+numDesdeDos+"");
                //                console.log("numHastaDos::"+numHastaDos+"");
            }
            //            console.log("que tienen las fechas");
            //            console.log({fechaDesde});
            //            console.log({fechaHasta});
            //            console.log({fechaDesdeDos});
            //            console.log({fechaHastaDos});
            //            


            // verifico si aplico el rango doble de fecha.
            //            if(fechaDesdeDos!=="" && fechaHastaDos!==""){
            //                rangoDoble = 1;                
            //              
            //            }

            if (puntoVenta == "") {
                // alert("Debe seleccionar un punto de venta");
                Toast.fire({
                    icon: "warning",
                    text: "Debe seleccionar un punto de venta",
                    });
                return false;
                
            }

            if (decimales === "") {
                // alert("Debe seleccionar decimales");
                Toast.fire({
                    icon: "warning",
                    text: "Debe seleccionar decimales",
                    });
                return false;
            }

            /*
             * VENTAS NETAS GERENCIALES
             * ========================
             * Nota mental: no se puede hacer colspan con los th para listar
             * las columnas si no da error de orden,
             */
            /*
             * configuro el simbolo a mostrar.
             */
            var usoSimb = "no";
            switch (tipo) {
                case "un":
                    simbDer = "";
                    simbIzq = "";
                    break;
                case "monto":
                    simbDer = "";
                    simbIzq = "$";
                    usoSimb = "si";
                    break;
                case "peso":
                    simbDer = "";
                    simbIzq = "";
                    break;
            }

            // armar el titulo del informe
            tituloInforme += ' por '+listarPor+' valores en ' + tipo+ ' por  '+ tipoResumen; 

            $("#spinner").hide();
            $.ajax({
                type: 'GET',
                url: 'relay-ventas-netas-gerencia.php',
                data: {
                    "ajax": "true",

                    "fechaDesde": fechaDesde,
                    "fechaHasta": fechaHasta,
                    "fechaDesdeDos": fechaDesdeDos,
                    "fechaHastaDos": fechaHastaDos,
                    "tipoResumen": tipoResumen,
                    "tipo": tipo,
                    "tipoDisplayBulto":tipoDisplayBulto,
                    "listarPor": listarPor,
                    "filtrarPor": filtrarPor,
                    "puntoVenta": puntoVenta,
                    "grafico": grafico,
                    "rangoDoble": rangoDoble,
                    "opRango": opRango,
                    "decimales": decimales,
                    "artEnsambVenta": artEnsambleVenta,
                    "queInforme": "vt",
                    "queSalida": "html"

                },
                
                success: function(response) {
                    botonBuscar.attr('disabled', false);
                    botonBuscar.html(textoGenerar);
                    var fecha = new Date();
                    var fechaFormateada = fecha.getFullYear() +'-'+
                                    ("0" + (fecha.getMonth() + 1)).slice(-2) +'-'+
                                    ("0" + fecha.getDate()).slice(-2) +'-'+
                                    ("0" + fecha.getHours()).slice(-2) +
                                    ("0" + fecha.getMinutes()).slice(-2) +
                                    ("0" + fecha.getSeconds()).slice(-2);
                    
                    var tituloExport = "";
                    if (response === "vacio") {
                        var trCampos = "<tr><td>No se encontaron resultados</td></tr>";
                        contienesVentasRubro.find("tbody").empty();
                        contienesVentasRubro.find("tbody").append(trCampos);

                    } else {

                        // console.log(response);
                        //var pepe = jQuery.parseJSON(response);

                        try {
                            var pepe = jQuery.parseJSON(response);
                        } catch (e) {
                            console.log(e instanceof SyntaxError); // true
                            console.log(e.message); // "missing ; before statement"
                            console.log(e.name); // "SyntaxError"
                            console.log(e.fileName); // "Scratchpad/1"
                            console.log(e.lineNumber); // 1
                            console.log(e.columnNumber); // 4
                            console.log(e.stack);
                            // "@Scratchpad/1:2:3\n"
                            var trCampos = "<tr><td>Ocurrio un problema</td></tr>";
                            contienesVentasRubro.find("tbody").empty();
                            contienesVentasRubro.find("tbody").append(trCampos);
                            return false;

                        }

                        //console.log(pepe);
                        // decimales en porcentaje
                        if (decimales === "No") {
                            var redondeo = 0;
                        } else {
                            var redondeo = parseInt(decimales);
                        }

                        /**
                         * TABLA HTML 
                         * */
                        if ($.fn.dataTable.isDataTable(nombreVentasRubro)) {
                            contienesVentasRubro.DataTable().destroy();
                        }
                        contienesVentasRubro.find("thead").empty();
                        contienesVentasRubro.find("tbody").empty();
                        contienesVentasRubro.find("tfoot").empty();

                        // largo de pagina...
                        var largoUno, largoDos, largoTres;
                        largoUno = "9";
                        largoDos = "18";
                        largoTres = "27";
                        // titulos de las tablas

                        var trTitulo = "<tr>",
                            trCabecera = "<tr><th></th>",
                            trTituloLista = "";

                        //                            console.log(pepe.titulos);
                        // ordenar bien los titulos porque me los toma mal u obtener la fecha con el mes en dos digitos.
                        $.each(pepe.titulos, function(pos, titulo) {
                            //                            console.log('-----------------titulos---------------');
                            //                            console.log({pos,titulo});
                            if (pos == 0) {
                                trTituloLista = "<th>" + titulo.titulo + "</th>";
                                tituloExport += titulo.titulo;
                            } else {
                                if (titulo.rowspan === 1 && titulo.span === 2) {
                                    tituloExport += " " + titulo.titulo;
                                }

                                if (titulo.rowspan > 1) {


                                    trTitulo = trTitulo + "<th colspan='" + titulo.span + "' rowspan='" + titulo.rowspan + "'>" + titulo.titulo + "</th>";
                                    trTitulo = trTitulo + "<th  colspan='" + titulo.span + "' rowspan='2'>" + titulo.titulo + "(%)</th>";
                                } else {
                                    trTitulo = trTitulo + "<th  colspan='" + titulo.span + "'>" + titulo.titulo + "</th>";

                                }

                            }

                            // cabeceras
                        });
                        //                        console.log("------titulo final----------");
                        //                        console.log({tituloExport});
                        trTitulo = trTitulo + "</tr>";
                        contienesVentasRubro.find("thead").append(trTitulo);

                        // coloco el listar aca
                        //console.log(trTituloLista);
                        trCabecera = trCabecera + trTituloLista;
                        //console.log(pepe.cabeceras);
                        var itotal = 2;
                        $.each(pepe.cabeceras, function(pos, cabeza) {

                            trCabecera = trCabecera + "<th class='dt-right'>" + cabeza + "</th>";
                            itotal++;

                        });
                        //                        console.log("itotal"+itotal);
                        //                        console.log("opRango"+opRango);
                        contienesVentasRubro.find("thead").append(trCabecera);
                        // renglones 
                        var cuantasCeldas = 0;
                        var cuantosReng = 0;
                        var totalGeneral = 0;
                        var totalValorA = 0;
                        var totalValorB = 0;
                        var cuantasColumnas = 0;
                        var arrColumnas = [];
                        // sumo el total de renglones distintos de cero por columna.
                        var arrCuantos = [];
                        // calculo el total general
                        // ver de que capaz no haga falta.
                        $.each(pepe.data, function(pos, renglones) {
                            $.each(renglones, function(posi, celda) {

                                if (posi == "subt") {
                                    totalGeneral += parseFloat(celda);
                                    //                                    console.log("celda:=> " + celda);
                                    //                                    console.log("celda pfloat:=>"+parseFloat(celda));
                                    //                                     console.log("TotalGeneralParcial:=> " + totalGeneral);
                                }
                                //cuantasCeldas++;
                            });
                        });
                        //                        console.log("TotalGeneralfianl:=> " + totalGeneral);
                        // pasar los subtotales como un footer para poder operar por pagina 
                        //  y mostrar los valores negativos y positov.s
                        // dibujo cada columna agrego porcentaje
                        $.each(pepe.data, function(pos, renglones) {
                            var trCampos = "<tr>";
                            var celdaSubTotal = 0;
                            var valorA = 0;
                            var valorB = 0;

                            var valorPorcDif = 0;
                            cuantosReng++;
                            trCampos = trCampos + "<td></td>";
                            cuantasColumnas = 0;
                            $.each(renglones, function(posi, celda) {
                                cuantasColumnas++;
                                cuantasCeldas++;
                                //                                  console.log("Posi=>"+posi+" ArrCuantos[posi]=>"+arrCuantos[posi]+"-ArrColumnas[posi]=>"+arrColumnas[posi]+" -parsefloat celda=>"+celda);
                                if (posi != 0) {
                                    // resta o diferencia
                                    // ahora el tema es que sea agrupado o resta hago un calculo.
                                    if (opRango != "suma" && posi == 1) {
                                        valorA = parseFloat(celda);
                                    }
                                    if (opRango != "suma" && posi == 2) {
                                        valorB = parseFloat(celda);
                                    }
                                    //                                    console.log("valorA="+valorA);
                                    //                                    console.log("valorB="+valorB);
                                    //                                    if(posi=="subt"&&opRango!="suma"){
                                    //                                        console.log("totalValor{"+(valorB+valorA)+"}");
                                    //                                    }
                                    // sumatoria de las columnas.
                                    //console.log("Posi=>"+posi+" ArrCuantos[posi]=>"+arrCuantos[posi]+"-ArrColumnas[posi]=>"+arrColumnas[posi]+" -parsefloat celda=>"+parseFloat(celda));
                                    if (arrColumnas[posi] === undefined) {
                                        if (parseFloat(celda) !== 0) {
                                            arrCuantos[posi] = 1;
                                        } else {
                                            arrCuantos[posi] = 0;
                                        }
                                        arrColumnas[posi] = parseFloat(celda);
                                    } else {

                                        if (parseFloat(celda) !== 0) {
                                            arrCuantos[posi] = arrCuantos[posi] + 1;
                                        }
                                        arrColumnas[posi] = arrColumnas[posi] + parseFloat(celda);

                                    }
                                    if (usoSimb == "si") {
                                        trCampos = trCampos + "<td class='dt-right dt-nowrap' data-order='" + celda +"'>" + dinero.format(celda) + "</td>\n";
                                    }
                                    if (usoSimb == "no") {
                                        trCampos = trCampos + "<td class='dt-right dt-nowrap' data-order='" + celda +"'>" + numerito.format(celda) + "</td>\n";
                                    }
                                } else {
                                    // es texto
                                    //                                    trCampos =  trCampos + "<td class='dt-left'><div class='corto'>"+celda+"</div></td>\n";
                                    trCampos = trCampos + "<td data-order='" + celda + "' class='dt-left'>" + celda + "</td>";
                                }
                                //                                console.log("----linea--- y celda -----");
                                //                                console.log({posi});
                                if (posi == "subt") {

                                    //celdaSubTotal += valorB 
                                    totalValorA += valorA;
                                    totalValorB += valorB;
                                    //                                    if(opRango=="resta"){
                                    //                                        if(valorA==0){
                                    //                                            valorPorcDif=100;
                                    //                                        }else{
                                    //                                            valorPorcDif=parseFloat(celda)*100/valorA;
                                    //                                        }
                                    //                                    }
                                    //                                console.log("--que op rango soy?---  voy por linea.--");
                                    // console.log({opRango});
                                    if (opRango != "suma") {
                                        //                                        console.log("opRango=> "+opRango);
                                        //                                        console.log("valor A=> "+valorA);
                                        //                                        console.log("celda => "+valorB);
                                        // aca ver si es negativo.

                                        //                                        if(valorA<0){
                                        //                                            console.log("Es un valor negativo lo bajo?"+valorA);
                                        //                                        }
                                        // si es suma agrupada y resta pero si no hay valor B


                                        // tengo los dos valores
                                        if (opRango === "resta") {
                                            //                                            console.log("-------LINEA-------------dentro del operango calculo de porcentaje.:"+opRango+"-------------------------");
                                            //                                            console.log({opRango,valorA,valorB});

                                            // si los dos valores estan en cero entonces no hay porcentaje o sea CERO
                                            if (valorA == 0 && valorB == 0) {
                                                //                                                console.log("-- valor A y valor B CERO O NEGATIVO porcentaje en cero----");
                                                valorPorcDif = 0;
                                            }

                                            // valor A con negativo 
                                            if (valorA < 0 && valorB > 0) {
                                                //                                                console.log("--- valorA NEGATIVOy valor B bien porcentaje 100% calulado de utilidad-----");
                                                valorPorcDif = (((valorB - valorA) * 100) / valorA);
                                            }
                                            if (valorA == 0 && valorB > 0) {
                                                //                                                console.log("--- valorA CERO  valor B bien porcentaje 100% de utilidad-----");
                                                valorPorcDif = 100;
                                            }

                                            // A mayor que cero y B igual a cero 
                                            if (valorA > 0 && valorB == 0) {
                                                //                                                console.log("Valor A mayor a cero y B igual a cero, -100 no vendi nada----");
                                                valorPorcDif = -100;
                                            }
                                            // valor A mayor que cero valorB Negativo
                                            if (valorA > 0 && valorB < 0) {
                                                //                                                console.log("Valor A mayor a cero y B negativo, -100 no vendi nada------");
                                                valorPorcDif = -100;
                                            }
                                            // valor A y B con algun valor hago la cuenta.
                                            if (valorA > 0 && valorB > 0) {
                                                //                                                console.log("valor A y B con numeros positivos hago la resta si da negativo no importa. ------")
                                                valorPorcDif = (((valorB - valorA) * 100) / valorA);
                                            }

                                            //                                            console.log({valorPorcDif});

                                        } else {

                                            celdaSubTotal += celda;
                                        }

                                    } else {
                                        celdaSubTotal += celda;
                                    }

                                }
                                cuantasCeldas++;

                            });
                            /* cambiar cuando es diferncia  el porcentaje que toma el % respecto del valor del primer campo, 
                             * quizas haya ya que traerlo*/
                            //console.log("opRango:=>"+opRango);
                            if (opRango !== "suma" && opRango !== "sumag") {
                                //                                console.log("-soy la diferencia en el total general -");
                                //                                console.log({valorPorcDif});
                                //valorPorcDif =(valorPorcDif/100);
                                //                                console.log({valorPorcDif});
                                trCampos = trCampos + "<td  class='dt-right dt-nowrap'>" + valorPorcDif + "</td>";
                            } else {
                                // console.log("-----dentro del total general----");
                                // console.log({celdaSubTotal,totalGeneral});
                                var totalGral = 0;
                                if (totalGeneral !== 0) {
                                    totalGral = parseFloat(celdaSubTotal * 100 / totalGeneral);
                                }
                                //                                console.log({totalGral});
                                //totalGral =(totalGral/100)*100;
                                trCampos = trCampos + "<td name='porciento' class='dt-right dt-nowrap' data-order='" + totalGral +"'>" + numerito.format(totalGral) + "</td>";
                            }
                            cuantasCeldas++;
                            trCampos = trCampos + "</tr>\n";
                            contienesVentasRubro.find("tbody").append(trCampos);
                        });

                        arrColumnas['porc'] = 100;
                        arrCuantos['porc'] = "";

                        var totalCampos = cuantasColumnas - 2;
                        // linea final con subtotal

                        var lineaPie, lineaTabla, trTotal, trTotalTabla;
                        trTotal = "<tr>";
                        lineaPie = "<th class='dt-left dt-nowrap' colspan='2'>Total Ventas</th>\n";
                        // lineaTabla = "<td hidden='true'></td>\n<td>Total Ventas</td>\n";
                        // trTotalTabla = "<tr style='display:none;'>";
                        // subtotales por columna
                        //                       console.log("totalValorA "+totalValorA);
                        //                       console.log("totalGeneral "+totalGeneral);
                        var valorSubtotalCol = 0;
                        for (var po in arrColumnas) {
                            if (opRango != "suma" && opRango != "sumag") {
                                //                                console.log("-----opRango PIE DIREFENCIA------");
                                if (po == "porc") {
                                    // evaluo la division
                                    //                                    console.log("valor es igual o mayor que cero?"+totalValorA);
                                    //                                    console.log("----PIE porcentaje-----");
                                    //                                    console.log({totalValorA,totalValorB});
                                    if (totalValorA <= 0 && totalValorB > 0) {
                                        //                                        console.log("total informe valor A Nada y valor B positivo");
                                        valorSubtotalCol = 100;
                                    }

                                    if (totalValorA > 0 && totalValorB == 0) {
                                        //                                        console.log("total informe valor A positivo y con valor y valor B cero .");
                                        valorSubtotalCol = 0;
                                    }

                                    if (totalValorA > 0 && totalValorB < 0) {
                                        //                                        console.log("total informe valor A positivo y con valor y valor B negativo.");
                                        valorSubtotalCol = (((totalValorB - totalValorA) * 100) / totalValorA);
                                    }

                                    if (totalValorA > 0 && totalValorB > 0) {
                                        //                                       console.log('total informa valor A positivo y valor B positivo. calucllo');
                                        valorSubtotalCol = (((totalValorB - totalValorA) * 100) / totalValorA);
                                    }



                                    //                                        else{
                                    //                                        valorSubtotalCol=(totalValorB/totalValorA;
                                    //                                    }

                                    //                                    if(valorSubtotalCol<1){
                                    //                                         valorSubtotalCol = ((1-valorSubtotalCol) *100 );
                                    //                                    }else{
                                    //                                        if(valorSubtotalCol==1){
                                    //                                                valorSubtotalCol = 0;
                                    //                                            }else{
                                    //                                                valorSubtotalCol = (1-valorSubtotalCol )*100 *-1;
                                    //                                            }
                                    //                                    }

                                    //valorSubtotalCol =(valorSubtotalCol/100);

                                    lineaPie += "<th  class='dt-right dt-nowrap'>" + numerito.format(valorSubtotalCol) + "</th>\n";
                                    // lineaTabla += "<td  class='dt-right dt-nowrap'>" + numerito.format(valorSubtotalCol) + "</td>\n";


                                } else {
                                    if (usoSimb == "si") {
                                        lineaPie += "<th  class='dt-right dt-nowrap' >" + dinero.format(arrColumnas[po]) + "</th>\n";
                                        // lineaTabla += "<td  class='dt-right dt-nowrap' >" + dinero.format(arrColumnas[po]) + "</td>\n";
                                    }
                                    if (usoSimb == "no") {
                                        lineaPie += "<th class='dt-right dt-nowrap' >" + numerito.format(arrColumnas[po]) + "</th>\n";
                                        // lineaTabla += "<td class='dt-right dt-nowrap' >" + numerito.format(arrColumnas[po]) + "</td>\n";
                                    }
                                }
                            } else {
                                if (po == "porc") {
                                    //var porc=precise_round(arrColumnas[po],redondeo);
                                    //                                    var porc=porcientos.format(arrColumnas[po]/100);
                                    //                                    console.log("porcentaje final?");
                                    //                                    console.log(arrColumnas[po]);
                                    //                                    console.log(arrColumnas[po]/100);
                                    //var porc=(arrColumnas[po]/100);
                                    var porc = arrColumnas[po];
                                    lineaPie += "<th  class='dt-right dt-nowrap'>" + numerito.format(porc) + "</th>\n";
                                    // lineaTabla += "<td  class='dt-right dt-nowrap'>" + numerito.format(porc) + "</td>\n";
                                } else {
                                    if (usoSimb == "si") {
                                        lineaPie += "<th  class='dt-right dt-nowrap'>" + dinero.format(arrColumnas[po]) + "</th>\n";
                                        // lineaTabla += "<td  class='dt-right dt-nowrap'>" + dinero.format(arrColumnas[po]) + "</td>\n";
                                    }
                                    if (usoSimb == "no") {
                                        lineaPie += "<th  class='dt-right dt-nowrap'>" + numerito.format(arrColumnas[po]) + "</th>\n";
                                        // lineaTabla += "<td  class='dt-right dt-nowrap'>" + numerito.format(arrColumnas[po]) + "</td>\n";
                                    }


                                }
                            }
                        }
                        trTotal += lineaPie;
                        // trTotalTabla += lineaTabla;
                        trTotal += "</tr>\n";
                        // trTotalTabla += "</tr>\n";
                        lineaPie = "";
                        // lineaTabla = "";
                        //                        console.log(arrColumnas);
                        // notas de credito
                        /*
                         *  si no viene el array de notas de credito saco los dos renglones de nc y d total gral.
                         */
                        if(pepe.impNC !==undefined){ 
                            trTotal += "<tr><th colspan='2'>Total NC por importe <br> Total Descuentos </th>";
                            var totalNC = 0;
                            var textoTotGeneral = "";
                            var valorTotal =0;
                            var valorTotalA=0;
                            var valorTotalB=0;
                            var valorNcA =0;
                            var valorNcB =0;
//                            console.log(pepe.impNC);
                            $.each(pepe.impNC,function(pos,renglones){
//                                console.log("arrColumns["+pos+"]:=>";
//                                console.log("arrColums[pos]: "+arrColumnas[pos]);
//                                console.log("pos: "+pos);
////                                console.log("totalNC: "+totalNC);
//                                console.log(renglones);
//                                console.log("opRango: "+ opRango);
                                totalNC = totalNC  + parseFloat(renglones);
                                
                                valorTotal = parseFloat(arrColumnas[pos]) + parseFloat(renglones) ;
                                if(pos==1){
                                    valorTotalA=parseFloat(arrColumnas[pos])+ parseFloat(renglones);
                                    valorNcA = parseFloat(renglones);
                                }else{
                                    valorTotalB=parseFloat(arrColumnas[pos])+ parseFloat(renglones) ;
                                    valorNcB = parseFloat(renglones);
                                }
//                                console.log("valorNCA: "+ valorNcA);
//                                console.log("valorNCB: "+ valorNcB);
                                
//                                if(opRango!="resta"){
//                                    totalNC = totalNC  + parseFloat(renglones);
//                                }
                                if(usoSimb=="si"){
                                    trTotal += "<th data-order='"+renglones+"' class='dt-right dt-nowrap'>"+dinero.format(renglones)+"</td>";
                                    textoTotGeneral += "<th data-order='"+valorTotal+"' class='dt-right dt-nowrap'>"+dinero.format(valorTotal)+"</th>";
                                }
                                if(usoSimb=="no"){
                                    trTotal += "<th data-order='"+renglones+"' class='dt-right dt-nowrap'>"+numerito.format(renglones)+"</th>";
                                    textoTotGeneral += "<th data-order='"+valorTotal+"' class='dt-right dt-nowrap'>"+numerito.format(valorTotal)+"</th>";
                                }
                            });
                            
                            if(opRango=="resta"){
//                                console.log("valorNCA:=>"+valorNcA+" ValorNCB=>"+valorNcB);
                                totalNC = valorNcA -valorNcB;
                                // si el resultado es negativo, debe ir positivo si el resultado es positivo va negativo.
//                                console.log("totalNC: "+ totalNC);
                                totalNC = (totalNC *-1);
                            }
                            
                            if(totalNC >0){
                                if(usoSimb=="si"){
                                    trTotal +="<th  data-order='"+totalNC+"' class='dt-right dt-nowrap'>"+dinero.format(totalNC)+"</th>";
                                }
                                if(usoSimb=="no"){
                                    trTotal +="<th  data-order='"+totalNC+"' class='dt-right dt-nowrap'>"+numerito.format(totalNC)+"</th>";
                                }
                            }else{
                                if(usoSimb=="si"){
                                    trTotal +="<th data-order='"+totalNC+"' class='dt-right dt-nowrap'>"+dinero.format(totalNC)+"</th>";
                                }
                                if(usoSimb=="no"){
                                    trTotal +="<th data-order='"+totalNC+"' class='dt-right dt-nowrap'>"+numerito.format(totalNC)+"</th>";
                                }
                            }
                            trTotal += "<th></th></tr>";

    //                        total general

                            if(usoSimb=="si"){
                                trTotal += "<tr><th colspan='2'>Total General </th>"+textoTotGeneral+"<th data-order='"+totalGeneral+"' class='dt-right dt-nowrap'>"+dinero.format(totalGeneral+totalNC)+"</th>";
                            }
                            if(usoSimb=="no"){
                                trTotal += "<tr><th colspan='2'>Total General </th>"+textoTotGeneral+"<th data-order='"+totalGeneral+"' class='dt-right dt-nowrap'>"+numerito.format(totalGeneral+totalNC)+"</th>";
                            }
                            
                            if(opRango!="suma"){
//                                console.log(valorTotalB);
//                                console.log(valorTotalA);
                                var indice = valorTotalB/valorTotalA;
//                                console.log(indice);
                                var porciento =0;
                                if(indice<1){
                                    porciento = (1-indice)*100;
                                }else{
                                    if(indice==1){
                                        porciento=0;
                                    }else{
                                        porciento=((1-indice)*100)*-1;
                                    }
                                }
                                porciento=porcientos.format(porciento/100);
//                                console.log("porciento::"+porciento);
                                trTotal +="<th data-order='"+porciento+"' class='dt-right dt-nowrap'>"+porciento+"</th></tr>";
                            }else{
                                trTotal +="<th></th></tr>";
                            }
                        
                        }

                        // totales de registro por columna
                        //trTotal += "<tr><td></td><td>Total Registros</td>\n";
                        // lineaPie = "<th></th>\n<th>Total Registros</th>\n";
                        lineaPie = "<th colspan='2'>Total Registros</th>\n";

                        trTotal += "<tr>";
                        //trTotalTabla +="<tr style='display:none;'>\n";

                        //                      
                        for (var po in arrCuantos) {
                            //console.log("po=>"+po+" cuantos=>"+arrCuantos[po]);
                            if (po == "porc") {
                                lineaPie += "<th data-order='0' class='dt-right dt-nowrap'>-</th>\n";
                            } else {
                                lineaPie += "<th class='dt-right dt-nowrap'>" + arrCuantos[po] + "</th>\n";
                            }
                        }

                        trTotal += lineaPie + "</tr>";
                        //                        console.log("-----tr total tabla : ----------------------");
                        //                        console.log(trTotalTabla);
                                            //    console.log("----- fin tr total tabla:---------------------");
                                            //    console.log("----- tr total:---------------------");
                                            //    console.log(trTotal);
                                            //    console.log("------fin total-----------------------")
                        // contienesVentasRubro.find("tbody").append(trTotalTabla);
                        contienesVentasRubro.find("tfoot").append(trTotal);
                        //contienesVentasRubro.DataTable();  

                        //                        console.log("contenido de la tabla");
                        // console.log(contienesVentasRubro.html());



                        
                        var tt = contienesVentasRubro.DataTable({
                            "ordering": true,
                            "responsive": false,
                            "info": true,
                            "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todos"]],
                            "pageLength": 5 ,

                            
                            // column definitions
                            "columnDefs": [{
                                    "targets": 0,
                                    "searchable": false,
                                    "orderable": false
                                },
                                // {
                                //     "targets": [-1],
                                //     "render": $.fn.dataTable.render.number('.', ',', 1, '')
                                // },
                                // {
                                //     "targets": '_all',
                                //     "render": $.fn.dataTable.render.number('.', ',', 2, simbIzq)
                                // }

                            ],
                            // scrollY: "400px",
                            // scrollX: true,
                            // scrollCollapse: true,
                            paging: true,
                            // fixedColumns: {
                            //     leftColumns: 2,
                            //     rightColumns: 2
                            // },

                            //                            "order":[[0,'asc'],[itotal,'desc']]
                            // nota: si tengo el itotal, lo ordeno si no que no orden nada y venga por la base
                            "order": [
                                [itotal, 'desc']
                            ],
                            "dom": 'lfrBtip',
                            buttons: [

                                // $.extend(true, {}, buttonCommonExcel, {
                                $.extend(true, {},buttonCommonExcel, {


                                    extend: 'excelHtml5',
                                    text: 'generar Excel',
                                    title: tituloInforme,
                                    filename: 'informe_ventas_gerenciales_'+fechaFormateada,
                                    footer:false,
                                    header:true,
                                    customizeData: function(data) {
                                    //    console.log('dentro del customize data');
                                        // Procesar cuerpo de la tabla
                                        data.body.forEach(function(row) {
                                            row.forEach(function(cell, index) {
                                                // if (index !== 0 && index!==1) { // Excluir la columna de texto
                                                if (index !== 0) { // Excluir la columna de texto
                                                    
                                                    
                                                    // Eliminar símbolo de moneda y ajustar formato para Excel
                                                    let valor = cell.replace(/[^\d,.-]/g, '')  // Eliminar caracteres no numéricos
                                                                    .replace(/\./g, '')        // Quitar separador de miles
                                                                    .replace(',', '.');        // Cambiar coma a punto para decimales
                                                                    // console.log('campos a pasar',valor);
                                                    row[index] = parseFloat(valor);
                                                }
                                            });
                                        });

                                        // Procesar footer de la tabla (si existe) footer simple
                                        // if (data.footer) {
                                        //     data.footer.forEach(function(cell, index) {
                                        //         if (index !== 0) { // Excluir la columna de texto
                                        //             // Eliminar símbolo de moneda y ajustar formato para Excel
                                        //             let valor = cell.replace(/[^\d,.-]/g, '')  
                                        //                             .replace(/\./g, '')        
                                        //                             .replace(',', '.');        
                                        //             data.footer[index] = valor;
                                        //         }
                                        //     });
                                        // }
                                        // console.log('antes del error del pie',data.footer);
                                      
                                         // pasa el footer al excel completo.
                                        if ($('tfoot').length) {
                                            // data.footer = []; // Reiniciar `data.footer`
                                            // console.log('error de footer es porq ui',$('tfoot').length);
                                            // Iterar sobre cada fila del footer en el DOM
                                            $('tfoot tr').each(function() {
                                                let footerRow = [];
                                                
                                                // Iterar sobre cada celda en la fila
                                                $(this).find('th, td').each(function(index) {
                                                    let cellText = $(this).text().trim(); // Eliminar espacios en blanco
                                    
                                                    // Eliminar símbolos de moneda y ajustar formato para Excel si no es texto
                                                    let valor = index > 0 && cellText ? cellText.replace(/[^\d,.-]/g, '')
                                                                                       .replace(/\./g, '')
                                                                                       .replace(',', '.')
                                                                                   : cellText;
                                    
                                                    // Convertir a número si es necesario
                                                    if (index > 0 && valor) {
                                                        valor = valor;
                                                    }
                                                    
                                                    footerRow.push(valor);
                                                });
                                    
                                                // Completar la fila si tiene menos columnas para que todas tengan la misma cantidad
                                                while (footerRow.length < data.header.length) {
                                                    footerRow.push(''); // Agregar celdas vacías si faltan columnas
                                                }
                                    
                                                // Agregar la fila procesada al footer de los datos exportados
                                            //    data.footer.push(footerRow);
                                                // agrego al body el footer
                                                data.body.push(footerRow);
                                            });
                                        }

                                        

                                        $("#spinner").hide();
                                    }
                                    
                                })
                            ]
                        });


                        // console.log({tt.data});
                        // ordenamiento con la columna de indices

                        tt.on('order.dt search.dt', function() {
                            //                            console.log("=====RECUPERANDO ROW=?=");
                            var contando = 1;
                            // console.log({tt.column(1)});
                            var total = tt.column(0, {
                                search: 'applied',
                                order: 'applied'
                            }).nodes().count();

                            tt.column(0, {
                                search: 'applied',
                                order: 'applied'
                            }).nodes().each(function(cell, i) {
                                //                                console.log("===ORDENAMIENTO===");
                                //                                console.log(i);



                                //console.log(cell.hidden===false);

                                //console.log(i+1);
                                //                                console.log(cell['hidden']);
                                //console.log(cell.hidden);
                                //cell.innerHTML = i+1;
                                if (cell.hidden === true) {
                                    //                                    console.log(cell.hidden);
                                    //                                    console.log(total);
                                    cell.innerHTML = total + 1;
                                    total++;
                                    //                                    console.log("total invisible-> "+total);

                                } else {
                                    //                                    console.log("que indice soy=>"+i);
                                    // console.log({tt.column(1).nodes(i))};
                                    //                                    console.log(contando);
                                    cell.innerHTML = contando;
                                    contando++;
                                    //                                    console.log("contando mas uno-> "+contando);
                                }
                                //                                console.log("---- como quedo el numero ------");
                                //                                console.log(cell.innerHTML);
                                //contando++;

                            });
                        }).draw();

                        panelVentas.show('fast');
                        // $(this).attr('disabled', true);
                        // $(this).html(textoGenerar);

                    }
                },
                error: function(x, e) {
                    botonBuscar.attr('disabled', true);
                    botonBuscar.html(textoGenerar);

                    $("#spinner").hide('fast');
                    var s = x.status,
                        m = 'Ajax error: ';
                    if (s === 0) {
                        m += 'Check your network connection.' + x.status + e;
                    }
                    if (s === 404 || s === 500) {
                        m += s;
                    }
                    if (e === 'parsererror' || e === 'timeout') {
                        m += e;
                    }
                    alert(m);
                }
            }); /* fin ventas por rubro*/



        });


            // * accion de Utilidad Gerencial
            $('#botonBuscarUtilidad').click(function(){
                var botonBuscarUtilidad = $('#botonBuscarUtilidad');

                botonBuscarUtilidad.attr('disabled', true);
                botonBuscarUtilidad.html(textoEspere);
                console.log('posicion 1:', botonBuscarUtilidad.attr('disabled'));
               
                               
                var contienesVentas = $('#myTableVentas'),
                   nombreVentas    = "#myTableVentas",
                   contienesVentasRubro = $('#myTableVentasRubro'),
                   nombreVentasRubro    = "#myTableVentasRubro",
                   fechaDesde = $('#fechaDesde').val(),
                   fechaHasta = $('#fechaHasta').val(),
                   fechaDesdeDos = $('#fechaDesdeDos').val(),
                   fechaHastaDos = $('#fechaHastaDos').val(),
                   rangoDoble = 0,
                   opRango = $('#tipoOperacion').val(),
                   datasetGrafico="",
                   tipoResumen = $('#campoPeriodo').val(),
                   tipo =  $('#verInforme').val(), 
                   listarPor = $('#agrupoPor').val(),
                   filtrarPor = $('#filtroSelec').val(),
                   simbDer ="",
                   simbIzq ="",
                   
                   vuelta  = 0,
                   grafico = 0,
                   puntoVenta = $('#pvSelec').val(),
                   verGrafico = $('#aceptaGrafico');
       
                   /* valido los filtros al menos el del punto de venta no puede
                    * venir vacio
                    * */
       
                   if(verGrafico.is(':checked')===true){
                       grafico = 1;
                   }else{
                       grafico = 0;
                   }
                   
                   if(tipo==""){
                       alert("Debe seleccionar tipo de información ");
                       Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar tipo de información",
                        });
                        return false;
                       
                   }
                   if(listarPor==""){
                       alert("Debe seleccionar un campo por el cual listar el informe");
                       Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar un campo por el cual listar el informe",
                        });
                        return false;
                     
                   }
                   if(tipoResumen==""){
                       alert("Debe seleccionar un período de resumen");
                       Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar un período de resumen",
                        });
                        return false;
                    
                   }
                   if(fechaDesde==""){
                       alert("Debe seleccionar una fecha desde primaria");
                       Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar una fecha desde primaria",
                        });
                        return false;
                     
                   }
                   if(fechaHasta==""){
                       alert("Debe seleccionar una fecha hasta secundaria");
                       Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar una fecha hasta secundaria",
                        });
                        return false;
                     
                   }
                   // verifico si aplico el rango doble de fecha.
                   if(fechaDesdeDos!=="" && fechaHastaDos!==""){
                       rangoDoble = 1;                
                     
                   }
                   
                   if(puntoVenta==""){
                     alert("Debe seleccionar una fecha hasta secundaria");
                     Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar una fecha hasta secundaria",
                        });
                        return false;
                     
                     
                   }
                   
                   
                
                /*
                 * configuro el simbolo a mostrar.
                 */
                switch(tipo){
                    case "un":
                        simbDer = "";
                        simbIzq = "";
                        break;
                    case "monto":
                        simbDer = "";
                        simbIzq = "<i class='fa fa-usd'></i>";
                        break;
                    case "peso":
                        simbDer = "";
                        simbIzq = "";
                        break;
                }
                simbDer = "";
                simbIzq = "$";
                
                $.ajax({
                       type: 'GET',
                       url: 'relay-ventas-netas-gerencia.php',
                       data:{
                           "ajax" : "true",
                                               
                           "fechaDesde" : fechaDesde,
                           "fechaHasta" : fechaHasta,
       //                    "fechaDesdeDos" : fechaDesdeDos,
       //                    "fechaHastaDos" : fechaHastaDos,
                           "tipoResumen": tipoResumen,
       //                    "tipo" :  tipo, 
                           "listarPor" : listarPor,
                           "filtrarPor" : filtrarPor,                    
                           "puntoVenta" : puntoVenta,
       //                    "grafico"   : grafico,
       //                    "rangoDoble": rangoDoble,
                           "opRango"   : opRango,
                           "queInforme" : "ut",
                           "queSalida" : "html"
                           
                       },
                       success: function(response) {

                        botonBuscarUtilidad.attr('disabled', false);
                        // console.log($(this).html);
                        botonBuscarUtilidad.html(textoGenerar);
                        // console.log($(this).html);
                        // console.log('posicion 2:', botonBuscarUtilidad.attr('disabled'));


                           var fecha = new Date();
                           var fechaFormateada = fecha.getFullYear() +'-'+
                                               ("0" + (fecha.getMonth() + 1)).slice(-2) +'-'+
                                               ("0" + fecha.getDate()).slice(-2) +'-'+
                                               ("0" + fecha.getHours()).slice(-2) +
                                               ("0" + fecha.getMinutes()).slice(-2) +
                                               ("0" + fecha.getSeconds()).slice(-2);
       //                   console.log(response);
                          if(response==="vacio"){
                               // sin resultado
                               var trCampos="<tr><td>No se encontraron resultados</td></tr>";
                               contienesVentasRubro.find("tbody").empty();
                               contienesVentasRubro.find("tbody").append(trCampos);
                           }else{
                               var pepe = jQuery.parseJSON(response);
       //                        console.log(pepe);
       
                               /**
                                * TABLA HTML 
                                * */
                               if ( $.fn.dataTable.isDataTable( nombreVentasRubro)) {
                                   contienesVentasRubro.DataTable().destroy();
                               }
                               contienesVentasRubro.find("thead").empty();
                               contienesVentasRubro.find("tbody").empty();
                               contienesVentasRubro.find("tfoot").empty();
                               // titulos de las tablas
       
                               var trTitulo = "<tr>",
                                   trCabecera ="<tr><th></th>",
                                   trTituloLista="";
       //                            console.log(pepe.titulos);
       // ordenar bien los titulos porque me los toma mal u obtener la fecha con el mes en dos digitos.
                               $.each(pepe.titulos,function(pos,titulo){
                                   if(pos==0){
                                       trTituloLista = "<th>"+titulo.titulo+"</th>";
                                   }
                                   else {
                                       if(titulo.rowspan>1){
                                           trTitulo = trTitulo +"<th colspan='"+titulo.span+"' rowspan='"+titulo.rowspan+"'>"+titulo.titulo+"</th>";
                                           trTitulo = trTitulo +"<th colspan='"+titulo.span+"' rowspan='2'>"+titulo.titulo+"(%)</th>";
                                       }else{
                                           trTitulo = trTitulo +"<th colspan='"+titulo.span+"'>"+titulo.titulo+"</th>";
                                       }
                                      
                                   }
                               });
                               trTitulo = trTitulo +"</tr>";
                               contienesVentasRubro.find("thead").append(trTitulo);
       
                               // cabeceras
                               // coloco el listar aca
                               //console.log(trTituloLista);
                               trCabecera = trCabecera + trTituloLista;
                               //console.log(pepe.cabeceras);
                               var itotal = 1;
                               $.each(pepe.cabeceras,function(pos,cabeza){
       
                                   trCabecera = trCabecera +"<th class='dt-right'>"+cabeza+"</th>";
                                   itotal++;
       
                               });
       //                        console.log("itotal"+itotal);
       //                        console.log("cabecera: ="+trCabecera);
                               contienesVentasRubro.find("thead").append(trCabecera);
                               // renglones 
                               var cuantasCeldas =0;
                               var cuantosReng = 0;
                               var totalGeneral =0;
                               var totalCosto =0;
                               var totalNeto=0;
                               var totalUtilidad=0;
                               var cuantasColumnas = 0;
                               var arrColumnas= [];
                               // sumo el total de renglones distintos de cero por columna.
                               //tengo varios subtotales.
                               var arrCuantos = [];
                               // calculo el total general
                               // ver de que capaz no haga falta.
       //                         $.each(pepe.data,function(pos,renglones){
       //                              $.each(renglones, function(posi,celda){
       //                                  
       //                                  if(posi=="subt"){
       //                                    totalGeneral += parseFloat(celda); 
       //                                    }
       //                                    //cuantasCeldas++;
       //                            });                                                                                                   
       //                        });
                               // pasar los subtotales como un footer para poder operar por pagina 
                               //  y mostrar los valores negativos y positov.s
                               // dibujo cada columna agrego porcentaje
                               $.each(pepe.data,function(pos,renglones){
                                   var trCampos = "<tr>";
                                   var celdaSubTotal =0;
                                   cuantosReng++;
                                   trCampos = trCampos + "<td></td>";
                                   cuantasColumnas =0;
                                   $.each(renglones, function(posi,celda){
                                       cuantasColumnas++;
                                        cuantasCeldas++;
                                       if(posi!=0){
                                            // sumatoria de las columnas.
                                           //console.log(arrColumnas[posi]);
                                           if(arrColumnas[posi]===undefined){
                                               arrCuantos[posi] = 1;
                                               arrColumnas[posi] = parseFloat(celda);
                                           }
                                           else{
                                               if(parseFloat(celda)!==0){
                                                    arrCuantos[posi] = arrCuantos[posi] + 1;
                                               }
                                               arrColumnas[posi] = arrColumnas[posi] + parseFloat(celda);
                                               
                                           }
                                           if(posi=="porc"){
                                               trCampos =  trCampos + "<td class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(celda)+"</td>";
                                           }else{
                                               trCampos =  trCampos + "<td class='dt-right dt-nowrap'>"+simbIzq+""+ReplaceNumberWithCommas(celda)+simbDer+"</td>";
                                           }
                                       }else{
                                           
                                           
                                               trCampos =  trCampos + "<td class='dt-left'><div class='corto'>"+celda+"</div></td>";
                                           
                                       }
       //                                console.log(posi);
       //                               
                                       celdaSubTotal += parseFloat(celda); 
                                       
                                       cuantasCeldas++;
       
                                   });
       //                            trCampos = trCampos + "<td class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(parseFloat(celdaSubTotal*100/totalGeneral))+" <strong>%</strong></td>";
       //                             cuantasCeldas++;
                                   trCampos = trCampos+"</tr>";
                                   contienesVentasRubro.find("tbody").append(trCampos);                                                
                               });
                               
       //                        arrColumnas['porc'] = 100;
       //                        arrCuantos['porc'] ="";
           
                               var totalCampos=cuantasColumnas-2;
                               // linea final con subtotal
       //                        console.log("ArrColumnas: " +arrColumnas);
                               
                               var trTotal = "<tr><td colspan='2'>Total</td>\n";
                               // subtotales por columna
                               for (var po in arrColumnas) {
       //                            console.log(po);
       //                            console.log(arrColumnas[po]);
                                   if(po==3){
                                       totalNeto=arrColumnas[po];
                                   }
                                   if(po==4){
                                       totalCosto = arrColumnas[po];
                                   }
       //                            console.log(po);
                                   
                                   if(po=="porc"){
                                       
                                       var utilPorciento = totalNeto / totalCosto;
       //                                console.log("neto=> "+totalNeto+" costo=> "+totalCosto+" resultado=" + totalNeto/totalCosto);
                                       if(utilPorciento>=1){
                                           //utilidad positiva
                                           utilPorciento=(utilPorciento-1)*100;
                                       }else{
                                           // utilidad negativa
                                           utilPorciento=((utilPorciento)*100 )*-1;
                                       }
                                       trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(utilPorciento)+ " %</strong></td>\n";
                                   }else{
                                       trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(arrColumnas[po])+ simbDer+" </strong></td>\n";
                                   }
                               }
                               trTotal += "</tr>";
       //                        console.log("pepe:");
       //                        console.log(pepe.impNC);
                               // notas de credito
                               /*
                               *  si no viene el array de notas de credito saco los dos renglones de nc y d total gral.
                                */
                               if(pepe.impNC !==undefined){ 
                                   trTotal += "<tr><td colspan='2'>Total NC por importe <br> Total Descuentos </td>";
                                   var totalNC = 0;
                                   var textoTotGeneral = "";
                                   var valorVentaNeta =0;
       //                            console.log("indice impnc:"+pepe.impNC[0]);
                                   totalNC =parseFloat(pepe.impNC[0]); 
                                   if(isNaN(totalNC)){
                                       totalNC =parseFloat(pepe.impNC); 
                                   }
       //                            console.log("totalnc"+totalNC);
                                   valorVentaNeta = parseFloat(totalNeto) + parseFloat(totalNC) ; 
                                   
                                   // recorremos las columnas con los subtotales y dibujamos de nuevo
                                   for (var po in arrColumnas) {
                                       // valor de celda regular
                                       var valorCelda = arrColumnas[po];
                                       if(po==2){
                                           // descuentos o notas de credito
                                           valorCelda = totalNC;
                                       }
                                       if(po==3){
                                           // calculo el nuevo neto menos las nc
                                           totalNeto=arrColumnas[po]+totalNC;
                                    
                                           valorCelda = totalNeto;
                                       }
                                       if(po==4){
                                           // valor de costo 
                                           totalCosto = arrColumnas[po];
                                           valorCelda=totalCosto;
                                       }
                                       if(po==5){
                                           // valor de utilidad
                                           valorCelda = totalNeto-totalCosto;
                                       }
       //                                console.log(po);
       
                                       if(po=="porc"){
       
                                           var utilPorciento = totalNeto / totalCosto;
       //                                    console.log("neto=> "+totalNeto+" costo=> "+totalCosto+" resultado=" + totalNeto/totalCosto);
                                           if(utilPorciento>=1){
                                               //utilidad positiva
                                               utilPorciento=(utilPorciento-1)*100;
                                           }else{
                                               // utilidad negativa
                                               utilPorciento=((utilPorciento)*100 )*-1;
                                           }
                                           trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(utilPorciento)+ " %</strong></td>\n";
                                       }else{
                                           if(po==2){
                                               trTotal +="<td class='dt-right dt-nowrap'><strong>"+simbIzq+"("+ReplaceNumberWithCommas(valorCelda)+simbDer+" )</strong></td>";
                                           }else{
                                               trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(valorCelda)+ simbDer+" </strong></td>\n";
                                           }
                                       }
                                   }
                                   
                                   trTotal += "</tr>";
       
           //                        total general
       
       
                                   //trTotal += "<tr><td colspan='2'>Total General </td>"+textoTotGeneral+"<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(totalGeneral+totalNC)+simbDer+" </strong></td><td></td></tr>";
                               }
                               
                               // cantidad totales de registro por columna
                               trTotal += "<tr><td colspan='2'>Total Registros</td>\n";
                               // subtotales por columna
                               for (var po in arrCuantos) {
                                   if(po=="porc"){
                                       trTotal += "<td class='dt-right dt-nowrap'><strong>-</strong></td>\n";
                                   }else{
                                       trTotal += "<td class='dt-right dt-nowrap'><strong>"+arrCuantos[po]+ " </strong></td>\n";
                                   }
                               }
                               trTotal += "</tr>";
                               contienesVentasRubro.find("tfoot").append(trTotal);
                               //contienesVentasRubro.DataTable();  
                                var tt = contienesVentasRubro.DataTable({
                                   "ordering":true,                                   
                                   "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todos"]],
                                   "pageLength": 5 ,
                                   // column definitions
                                   "columnDefs": [ {
                                       "searchable": false,
                                       "orderable": false,
                                       "targets": 0
                                   } ],
       //                            "order":[[0,'asc'],[itotal,'desc']]
                                   "order":[[itotal,'desc']],
                                   "dom": 'lBfrtip',
                                   buttons: [
                                               {
                                                   extend: 'excelHtml5',
                                                   text: 'generar Excel',
                                                   title: 'informe_utilidad_gerencial_'+fechaFormateada,
                                                   filename: 'informe_utilidad_gerencial_'+fechaFormateada,
       
                                                   footer:true,
                                                   header:true,
                                                   customizeData: function(data) {
                                                       // Procesar cuerpo de la tabla
                                                       data.body.forEach(function(row) {
                                                           row.forEach(function(cell, index) {
                                                               if (index !== 0 && index!==1) { // Excluir la columna de texto
                                                                   // Eliminar símbolo de moneda y ajustar formato para Excel
                                                                   let valor = cell.replace(/[^\d,.-]/g, '')  // Eliminar caracteres no numéricos
                                                                                   .replace(/\./g, '')        // Quitar separador de miles
                                                                                   .replace(',', '.');        // Cambiar coma a punto para decimales
       
                                                                   row[index] = valor;
                                                               }
                                                           });
                                                       });
       
                                                       // Procesar footer de la tabla (si existe)
                                                       if ($('tfoot').length) {
                                                        // data.footer = []; // Reiniciar `data.footer`
                                                        // console.log('error de footer es porq ui',$('tfoot').length);
                                                        // Iterar sobre cada fila del footer en el DOM
                                                        $('tfoot tr').each(function() {
                                                            let footerRow = [];
                                                            
                                                            // Iterar sobre cada celda en la fila
                                                            $(this).find('th, td').each(function(index) {
                                                                let cellText = $(this).text().trim(); // Eliminar espacios en blanco
                                                
                                                                // Eliminar símbolos de moneda y ajustar formato para Excel si no es texto
                                                                let valor = index > 0 && cellText ? cellText.replace(/[^\d,.-]/g, '')
                                                                                                   .replace(/\./g, '')
                                                                                                   .replace(',', '.')
                                                                                               : cellText;
                                                
                                                                // Convertir a número si es necesario
                                                                if (index > 0 && valor) {
                                                                    valor = valor;
                                                                }
                                                                
                                                                footerRow.push(valor);
                                                            });
                                                
                                                            // Completar la fila si tiene menos columnas para que todas tengan la misma cantidad
                                                            while (footerRow.length < data.header.length) {
                                                                footerRow.push(''); // Agregar celdas vacías si faltan columnas
                                                            }
                                                
                                                            // Agregar la fila procesada al footer de los datos exportados
                                                        //    data.footer.push(footerRow);
                                                            // agrego al body el footer
                                                            data.body.push(footerRow);
                                                        });
                                                    }
                                                   }
                                               }
                                           ]
                               }); 
       
                               // ordenamiento con la columna de indices
                               tt.on( 'order.dt search.dt', function () {
                                   tt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                                       cell.innerHTML = i+1;
                                   } );
                               } ).draw();

                                // botonBuscarUtilidad.attr('disabled', false);                              
                                // botonBuscarUtilidad.html(textoGenerar);
                                // console.log('posicion 3:', botonBuscarUtilidad.attr('disabled'));
                               if(grafico==1){
                                   /*
                                    * GRAFICO BARRA
                                    * **/
                                    /*GRAFICO*/
                                   drawChart(pepe.gdata,pepe.goption,"ColumnChart","graficoVentasRubro"); 
       
                                   /**
                                    * GRAFICO DE TORTA
                                    * */
                                   /* parametros*/
                                   drawChart(pepe.gdataT,pepe.goptionT,"PieChart","graficoVentasRubroT"); 
                               }   
                           }
                       },
                       error: function(x, e) {
                        botonBuscarUtilidad.attr('disabled');
                        botonBuscarUtilidad.html(textoGenerar);
                               var s = x.status, 
                                       m = 'Ajax error: ' ; 
                               if (s === 0) {
                                       m += 'Check your network connection.' + x.status + e;
                               }
                               if (s === 404 || s === 500) {
                                       m += s;
                               }
                               if (e === 'parsererror' || e === 'timeout') {
                                       m += e;
                               }
                               alert(m);
                       }
                   });/* fin ventas por rubro*/
                   
                    
           
             });


            //* funcion para agregar filtros.
            $('#addFiltro').on('click', function() {
                var listaFiltro = $('#listaFiltro'),
                    textFiltro = $('#filtroSelec'),
                    filtro = $('#filtrarPor').val(),
                    seleccion = $('#seleccionFiltro').attr("alt").split("|");
                var tFiltro = textFiltro.val();
                //agregar item a la lista
                var indiceLi = listaFiltro.children().length + 1;

                // sin item para agregar
                if(seleccion=="" || seleccion[1] == undefined){
                    Toast.fire({
                        icon: "warning",
                        text: "Debe seleccionar un Item de la lista",
                        });

                    return false;
                }

                // al menos un item de filtro seleccionado
                if (seleccion !== "" && seleccion[1] !== undefined) {
                    listaFiltro.append('<li id="' + indiceLi + '"> <i class="fas fa-check-square fa-lg fa-fw"></i> ' + filtro + ' - <strong>' + seleccion[1] + '</strong> <a class="borrarLi" rel="listaFiltro|' + indiceLi + '" href="#" title="Eliminar de la lista"><i class="fas fa-trash fa-lg fa-fw"></i></a></li>');
                    tFiltro = tFiltro + filtro + '|' + seleccion[0] + '|' + seleccion[1] + '|' + indiceLi + '||';
                    textFiltro.val(tFiltro);

                    $('.borrarLi').on('click', function() {
                        var valorLi = $(this).attr("rel").split("|"),
                            textFiltro = $('#filtroSelec'),
                            arrFiltro = $('#filtroSelec').val().split("||");
                        var ul = valorLi[0],
                            li = valorLi[1] - 1,
                            liObj = valorLi[1],
                            textoFiltro = "";

                        for (var po in arrFiltro) {
                            if (arrFiltro[po] != "") {
                                var arrLinea = arrFiltro[po].split('|');
                                var iLi = arrLinea[3] - 1;
                                if (iLi != li) {

                                    textoFiltro = textoFiltro + arrFiltro[po] + "||";
                                }
                            }
                        }
                        textFiltro.val(textoFiltro);
                        $('#' + ul + ' #' + liObj).remove();
                    });
                }

                $('#seleccionFiltro').attr("alt", "");
                $('#seleccionFiltro').val("");
                // agregar al input una lista
            });
            // borrar un valor de la lista.
            // =====================================

            $('#addPv').on('click', function() {
                var listaPv = $('#listaPv'),
                    textPv = $('#pvSelec'),
                    seleccion = $('#puntoVenta').val().split("|");
                var tPv = textPv.val();
                var indiceLi = listaPv.children().length + 1;
                //agregar item a la lista
                if (seleccion !== "" && seleccion !== undefined) {
                    listaPv.append('<li  id="' + indiceLi + '"><i class="fas fa-check-square fa-lg fa-fw"></i> Punto venta: <strong>' + seleccion[1] + '</strong> <a class="borrarLi" rel="listaPv|' + indiceLi + '" href="#" title="Eliminar de la lista"><i class="fa fa-trash fa-lg"></i></a></li>');
                    tPv = tPv + seleccion[0] + '|' + seleccion[1] + '|' + indiceLi + '||';
                    //console.log(tPv);
                    textPv.val(tPv);
                }
                // agregar al input una lista
                $('.borrarLi').on('click', function() {
                    var valorLi = $(this).attr("rel").split("|"),
                        textFiltro = $('#pvSelec'),
                        arrFiltro = $('#pvSelec').val().split("||");
                    var ul = valorLi[0],
                        li = valorLi[1] - 1,
                        liObj = valorLi[1],
                        textoFiltro = "";

                    for (var po in arrFiltro) {
                        if (arrFiltro[po] != "") {
                            var arrLinea = arrFiltro[po].split('|');
                            var iLi = arrLinea[2] - 1;
                            //                        console.log("iLi:"+iLi);
                            //                        console.log("li:"+li);
                            if (iLi != li) {

                                textoFiltro = textoFiltro + arrFiltro[po] + "||";
                            }
                        }
                    }
                    //                console.log(textoFiltro);
                    textFiltro.val(textoFiltro);
                    $('#' + ul + ' #' + liObj).remove();
                });
            });

            $("#agrupoPor").on("change", function() {

                var titulo = $("#tituloInforme"),
                    opcion = $("#agrupoPor option:selected").text();
                // console.log("en el agrupo " + $("#agrupoPor option:selected").text());
                titulo.text("Ventas por " + opcion);

            });

            //botones de expansion y contrar

            $("#expandir").on("click", function() {
                $("#contraer").show();
                var dFiltros = $(".filtroInformes");
                dFiltros.animate({
                    height: "toggle"
                }, 700);
                $(this).toggleClass("fa-expand").toggleClass("fa-minus-square");

            });



        });