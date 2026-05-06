
function controlarFechas(fechaDesde, fechaHasta, distancia) {
    var fecha1 = new Date(fechaDesde);
    var fecha2 = new Date(fechaHasta);

    switch (distancia) {
        case "diario":
            return esDiario(fecha1, fecha2);
        case "semanal":
            return esSemanal(fecha1, fecha2);
        case "mensual":
            return esMensual(fecha1, fecha2);
        case "anual":
            return esAnual(fecha1, fecha2);
        default:
            return false;
    }
}

function esDiario(fecha1, fecha2) {
    return fecha1.getTime() === fecha2.getTime();
}

function esSemanal(fecha1, fecha2) {
    var unaSemanaEnMilisegundos = 7 * 24 * 60 * 60 * 1000;
    return Math.abs(fecha2.getTime() - fecha1.getTime()) === unaSemanaEnMilisegundos;
}

function esMensual(fecha1, fecha2) {
    var diferenciaMeses = (fecha2.getFullYear() - fecha1.getFullYear()) * 12 + fecha2.getMonth() - fecha1.getMonth();
    return diferenciaMeses === 1;
}

function esAnual(fecha1, fecha2) {
    return fecha2.getFullYear() - fecha1.getFullYear() === 1 && fecha2.getMonth() === fecha1.getMonth() && fecha2.getDate() === fecha1.getDate();
}


