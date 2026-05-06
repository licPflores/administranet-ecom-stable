<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    
    if (isset($_POST['getImagen']) && $_POST['getImagen'] == 1) {

        $arrParam = array();

        if (isset($_POST['arrImage']) && $_POST['arrImage'] != '') {
            $arrParam['arrImage'] = $_POST['arrImage'];
        }

        for ($i = 0; $i < count($arrParam['arrImage']); $i++) {
            echo '<img src="'.$arrParam['arrImage'][$i]['foto'].'" />';
        }

    } else {
        echo "no funciono";
    }
?>