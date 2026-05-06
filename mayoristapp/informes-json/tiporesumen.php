<?php
ini_set("opcache.enable",0);
//SELECCION DE TAMANO DE MUESTRA
$tipoResumen = "mes";

if (isset($_REQUEST['tipoResumen'])) {
    switch ($_REQUEST['tipoResumen']) {
        case "dia":
            //// por dia
			$rango='%d-%m-%Y';



            array_push($nCampos, array("title" => ucwords("id"), "data" => "id", "visible" => 0));
            array_push($nCampos, array("title" => ucwords("Nombre"), "data" => "Nombre", "visible" => 1));
            $start = (new DateTime($_REQUEST['fechaDesde']));
            $end = (new DateTime($_REQUEST['fechaHasta']));
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($start, $interval, $end);

			$cortador=0;
            foreach ($period as $dt) {
				$cortador=$cortador+1;
                
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
            }
			
			if($cortador>0){
			$dt->add(new DateInterval("P1D"));
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
						}else{
							//Pongo la fecha porque no hay intervalo
						array_push($nCampos, array("title" => ucwords($start->format("d-m-Y")), "data" => $start->format("d-m-Y"), "visible" => 1));	
						}



            if (isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
                if ($_REQUEST['fechaDesdeDos'] != "" && $_REQUEST['fechaHastaDos'] != "") {
                    $start = (new DateTime($_REQUEST['fechaDesdeDos']));
                    $end = (new DateTime($_REQUEST['fechaHastaDos']));
                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($start, $interval, $end);

			$cortador=0;
            foreach ($period as $dt) {
				$cortador=$cortador+1;
                
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
            }
			
			if($cortador>0){
			$dt->add(new DateInterval("P1D"));
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
						}else{
							//Pongo la fecha porque no hay intervalo
						array_push($nCampos, array("title" => ucwords($start->format("d-m-Y")), "data" => $start->format("d-m-Y"), "visible" => 1));	
						}

                }



            break;
        case "semana":
            
			$rango = '%v %m-%Y';
			            array_push($nCampos, array("title" => ucwords("id"), "data" => "id", "visible" => 0));
            array_push($nCampos, array("title" => ucwords("Nombre"), "data" => "Nombre", "visible" => 1));
            //$start = (new DateTime($_REQUEST['fechaDesde']))->modify('first day of this month');
           // $end = (new DateTime($_REQUEST['fechaHasta']))->modify('first day of next month');
			
			//$start = new DateTime($_REQUEST['fechaDesde']);
			$start = (new DateTime($_REQUEST['fechaDesde']))->modify('first day of this month');
           // $end = (new DateTime($_REQUEST['fechaHasta']))->modify('+ 2 day');
            $end = (new DateTime($_REQUEST['fechaHasta']))->modify('last day of this month');
			
			
            $interval = DateInterval::createFromDateString('7 day');
            $period = new DatePeriod($start, $interval, $end);
			
            foreach ($period as $dt) {
            //  $semana=$dt->format("W m-Y");
			// echo "Semana = ".$semana."\n";
                array_push($nCampos, array("title" => ucwords(date('W m-Y', strtotime($dt->format("Y-m-d H:i:s")))), "data" => date('W m-Y', strtotime($dt->format("Y-m-d H:i:s"))), "visible" => 1));
            }

            if (isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
                if ($_REQUEST['fechaDesdeDos'] != "" && $_REQUEST['fechaHastaDos'] != "") {
                    $start = (new DateTime($_REQUEST['fechaDesdeDos']));
                    $end = (new DateTime($_REQUEST['fechaHastaDos']));
                    $interval = DateInterval::createFromDateString('7 day');
                    $period = new DatePeriod($start, $interval, $end);

                    foreach ($period as $dt) {
                        //echo $dt->format("m-Y");
                        array_push($nCampos, array("title" => ucwords(date('W m-Y', strtotime($dt->format("Y-m-d H:i:s")))), "data" => date('W m-Y', strtotime($dt->format("Y-m-d H:i:s"))), "visible" => 1));
                    }
                }
			
			
			
			
            break;

        case "ano":
            // por ano
			$rango = '%Y';



            array_push($nCampos, array("title" => ucwords("id"), "data" => "id", "visible" => 0));
            array_push($nCampos, array("title" => ucwords("Nombre"), "data" => "Nombre", "visible" => 1));
            $start = (new DateTime($_REQUEST['fechaDesde']))->modify('first day of this month');
            $end = (new DateTime($_REQUEST['fechaHasta']))->modify('first day of next month');
            $interval = DateInterval::createFromDateString('1 year');
            $period = new DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                //echo $dt->format("m-Y");
                array_push($nCampos, array("title" => ucwords($dt->format("Y")), "data" => $dt->format("Y"), "visible" => 1));
            }

            if (isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
                if ($_REQUEST['fechaDesdeDos'] != "" && $_REQUEST['fechaHastaDos'] != "") {
                    $start = (new DateTime($_REQUEST['fechaDesdeDos']));
                    $end = (new DateTime($_REQUEST['fechaHastaDos']));
                    $interval = DateInterval::createFromDateString('1 year');
                    $period = new DatePeriod($start, $interval, $end);

                    foreach ($period as $dt) {
                        //echo $dt->format("m-Y");
                        array_push($nCampos, array("title" => ucwords($dt->format("Y")), "data" => $dt->format("Y"), "visible" => 1));
                    }
                }


            break;
        default:
            //     echo "i no es igual a 0, 1 ni 2";
            // ya lo puse por defecto tipo resumen
$rango = '%m-%Y';


            array_push($nCampos, array("title" => ucwords("id"), "data" => "id", "visible" => 0));
            array_push($nCampos, array("title" => ucwords("Nombre"), "data" => "Nombre", "visible" => 1));
            $start = (new DateTime($_REQUEST['fechaDesde']))->modify('first day of this month');
            $end = (new DateTime($_REQUEST['fechaHasta']))->modify('first day of next month');
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                //echo $dt->format("m-Y");
                // array_push($nCampos,array("title"=>ucwords($dt->format("m-Y")),"data"=>$dt->format("m-Y"),"visible"=>1,"sType"=>"numeric","sClass"=>"numeros"));
                array_push($nCampos, array("title" => ucwords($dt->format("m-Y")), "data" => $dt->format("m-Y"), "visible" => 1, 'className' => 'numeros'));
            }
            //render: $.fn.dataTable.render.number('.', ',', 0, '$')

            if (isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
                if ($_REQUEST['fechaDesdeDos'] != "" && $_REQUEST['fechaHastaDos'] != "") {
                    $start = (new DateTime($_REQUEST['fechaDesdeDos']))->modify('first day of this month');
                    $end = (new DateTime($_REQUEST['fechaHastaDos']))->modify('first day of this month');
                    $interval = DateInterval::createFromDateString('1 month');
                    $period = new DatePeriod($start, $interval, $end);

                    foreach ($period as $dt) {
                        //echo $dt->format("m-Y");
                        array_push($nCampos, array("title" => ucwords($dt->format("m-Y")), "data" => $dt->format("m-Y"), "visible" => 1));
                    }
                }
    }
}
