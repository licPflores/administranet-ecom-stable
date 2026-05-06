<?php 
//
//phpinfo();
error_reporting(E_ALL);
//ini_set('display_errors', '1');
        require_once 'conexion.inc.php';
        $mini="";
        $tam=150;
        //$g=explode("|",$_GET["origen"]);
        $origen="logo";
        $quien=0;		
        switch($origen)
        {
            case "logo":
                $query = "SELECT 
                                logo AS Foto,
                                'image/pjpeg' AS Tipo 
                        FROM Configuracion;";
                $mini = 0;
                break;                  
            case "foto1":

                        $query="SELECT Foto1 as Foto,'image/pjpeg' as Tipo FROM articulo WHERE IDArt=".$quien;
                        $mini=0;
                        break;
            
            case "foto2":

                        $query="SELECT Foto2 as Foto,'image/pjpeg' as Tipo FROM articulo WHERE IDArt=".$quien;
                        $mini=0;
                        break;

                case "empresa":
                        $query="SELECT foto_empresa as Foto,tipo_foto_empresa as Tipo FROM empresa WHERE id_empresa=".$quien;
                        $mini=0;
                break;


        }

        
                $sal=mysqli_query($connV,$query)or die("no anduvo".mysqli_error($connV));
                $resul=mysqli_num_rows($sal);
                if ($resul!=0)
                {
                        $fila=  mysqli_fetch_assoc($sal);
                        if($fila["Foto"]==NULL||$fila["Tipo"]=="")
                        {

                                header("Content-type:".$fila["Tipo"]);
                                header("Pragma: no-cache");
                                header("Expires: 0");
                                readfile('sistema/_img/sinfoto.jpg');

                        }
                        else
                        {        
//                            print_r($fila);
                            //CREAMOS UNA IMAGEN CON LA FUNCION DE GD imagecreatefromstring
                            //YA QUE ESTA PUEDE LEER EL CAMPO BLOB QUE ESTAMOS OBTENIENDO
                            $im=imagecreatefromstring($fila["Foto"]);

                            //OBTENEMOS EL TIPO MIME DEL ARCHIVO ASI EL NAVEGADOR SABRA DE QUE SE TRATA
                            

                            //OBTENEMOS LAS MEDIDAS ACTUALES DE LA IMAGEN
                            $width = imagesx($im);
                            $height = imagesy($im);

                            // ESTABLECEMOS EL TAMA�O DEL THUMBNAIL
                            $imgw = $width;
//                             $imgw = $tam;

                            //CALCULAMOS EL ALTO DE LA IMAGEN PARA MANTER EL ASPECTO
                            $imgh = $height / $width * $imgw;

                            // CREAMOS UNA NUEVA IMAGEN UTILIZANDO LAS NUEVAS MEDIDAS
                            $thumb=imagecreatetruecolor($imgw,$imgh);

                            //CREAMOS UN COLOR PARA EL FONDO
                            //ESTO ES IMPORTANTE PORQUE SI LA IMAGEN CONTIENE FONDO BLANCO
                            //SOLO OBTENDRIAMOS UNA IMEGEN NEGRA
                            $back = imagecolorallocate($thumb, 255, 255, 255);
                            // RELLENAMOS EL LA IMAGEN CON EL COLOR QUE CREAMOS EN EL PASO ANTERIOR
                            imagefill ( $thumb, 0, 0, $back );

                            // COPIAMOS LA IMAGEN ORIGINA AL THUMBNAIL
                            //imagecopyresized($thumb,$im,0,0,0,0,$imgw,$imgh,imagesx($im),imagesy($im));
                            imagecopyresampled($thumb,$im,0,0,0,0,$imgw,$imgh,imagesx($im),imagesy($im));
                            //CREAMOS UNA IMAGEN TIPO JPEG
//                            $out = imagejpeg($thumb);
                            //$out = imagejpeg($im);
                           
                            //Y POR ULTIMO SIMPLEMENTE IMPRIMIMOS EL CONTENIDO DEL ARCHIVO
                            header("Pragma: no-cache");
                            header("Expires: 0");
//                            header("Content-type:".$fila["Tipo"]);
                            header('Content-type: image/png');
                            $out = imagepng($im);
                         
                            // LIMPIAMOS LA MEMORIA
                            imagedestroy ($im);
                            imagedestroy ($thumb);  
                        }
                }
                else
                {

                        header("Content-type:image/pjpeg");
                        //header("Content-type:image/pjpeg");
                        header("Pragma: no-cache");
                        header("Expires: 0");
                        readfile('sistema/_img/sinfoto.jpg');
                }        //include("footer.inc.php");


