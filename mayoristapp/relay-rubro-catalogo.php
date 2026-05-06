<?php
session_start();  
   
$_SESSION["buscaRubro"] = $_REQUEST["idr"];
$_SESSION["claseLista"]="galeria";
 session_write_close();
echo  "ok";
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

