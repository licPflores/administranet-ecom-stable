/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//jQuery.noConflict();
jQuery(document).ready(function() {
    jQuery("#myTable")
    .tablesorter({widthFixed: false, widgets: ['zebra']})
    .tablesorterPager({container: jQuery("#pager"),size:10});
});

function valida(campos){
    /*
     * el campo contiene un array de los nombre de campo y si son requeridos o no
     * voy a validar antes que haga cualquier cosa,
     * el caracter separador de campos | y de valores -
     */
    ///alert(campos);
    var campoValida=campos.split('|');
    
    for(i=0;i<=campoValida.length-1;i++){

        var campito=document.getElementById("'"+campoValida[i]+"'");
         //alert(document.getElementById("'"+campoValida[i]+"'"));
        if(campito.value==""){
            campito.style.color='FF0000';
            campito.focus();
            return false;
        }
    }
    return true;
    
}