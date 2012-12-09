<?php

/*
Autor: Adam Šorfa
licence: Creative Commons 2006,
URL: http://test.adam-sorfa.cz/resize_foto.php
*/

/**
 * LogoResize
 *
 */
class LogoResizer 
{

    public static function imageResize($way, $max_new_width, $max_new_height, $save = null) {

            $imgInfo = getimagesize($way);

            switch($imgInfo[2])
            {
                    case(1):
                            $main = imagecreatefromgif($way);
                            break;

                    case(2):
                            $main = imagecreatefromjpeg($way);
                            break;

                    case(3):
                            $main = imagecreatefrompng($way);
                            break;

                    default:
                            trigger_error('NEPODPOROVANY FORMAT OBRAZKU', E_USER_WARNING);
                            exit();
                            break;
            }

            list($width, $height) = $imgInfo;

            /*resizování obrázku*/
            $originalWidth = $width;
            $originalHeight = $height;
            $ratio = $width / $height;
            $height = $max_new_height;
            $width = $height * $ratio;
            $move_left = 0;
            $move_top = 0;

            if($width > $max_new_width)
            {
                    $ratio = $height / $width;
                    $width = $max_new_width;
                    $height = $width * $ratio;
            }
            elseif($width < $max_new_width)
            {
                    $move_left = ($max_new_width - $width) / 2;
            }

            if($height < $max_new_height)
            {
                    $move_top = ($max_new_height - $height) / 2;
            }

            $newImg = imagecreatetruecolor($max_new_width, $max_new_height);

            //vytvoøení bílího pozadí
            $red = imagecolorallocate($newImg, 255, 255, 255);
            imagefill($newImg, 0, 0, $red);

            /*uložení alfy v obrázku(resp, v pozadí obrázku)*/
            if(($imgInfo[2] == 3))
            {
                    imagealphablending($newImg, false);/*<-- vypnu prolínání prùbhednosti s truecolor*/
                    imagesavealpha($newImg, true);/*<-- vytvoøí flag pro uložení alfy*/
            }
            elseif(($imgInfo[2] == 1))
            {
                    /*Zde zjistim barvu, kterou má gif urèenou pro prùhlednost. Urèim tu barvu jako prùhlednou truecolorovýmu prozadí. A nakonec s ní to pozadí vyplnim*/

                    $transparent = imageColorTransparent($main); /*<-- poukuda zadám jen jednu hodnotu, vrátí mi index barvy, jenž je urèená jako prùhledná*/

                    if($transparent != -1)/*ovìøim jestli vùbec má uèenou nìjakou barvu jako prùhlednou, jestliže ne, nechám ho na pokoji*/
                    {
                            $transparent_color = imageColorsForIndex($main, $transparent); /*<-- získám sloøky RGB z indexu*/

                            $transparent_new = imageColorAllocate($newImg, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']); /*<-- vytvoøí barvu pro obrázek $newIng*/
                            $transparent_new_index = imageColorTransparent($newImg, $transparent_new); /*<-- urèim barvu $transparent_new jako prùhlednou a uložim si novej prùhlednej index*/
                            imageFill($newImg, 0, 0, $transparent_new_index); /*<-- vyplnim obrázek barvou, kterou má urèenou jako prùhlednou, vytvoøim tak vlastnì prùhledný pozadí, místo truecolorovýho*/
                    }

                    imageCopyResized($newImg, $main, $move_left, $move_top, 0, 0, $width, $height, $originalWidth, $originalHeight); /*<-- obrázky musim slouèit pomocí týhle funkce, jink to dìlá píèoviny*/
            }

            if($imgInfo[2] != 1)
                    imagecopyresampled($newImg, $main, $move_left, $move_top, 0, 0, $width, $height, $originalWidth, $originalHeight);

            /*poslání obrázku na výstup*/
            if(empty($save))
                    header('Content-type: ' . $imgInfo['mime']);
            switch($imgInfo[2])
                    {
                            case(1): imagegif($newImg, $save); break;
                            case(2): imagejpeg($newImg, $save, 100); break;
                            case(3): imagepng($newImg, $save, 9); break;

                            default:
                                    trigger_error('Resizovani obraku se posralo, proc, to fakt nevim', E_USER_WARNING);
                                    break;
                    }

            imagedestroy($main);
            imagedestroy($newImg);
    }

    public static function watermark($way, $watermarkWay, $opacity){
        $imgInfo = getimagesize($way);

            switch($imgInfo[2])
            {
                    case(1):
                            $main = imagecreatefromgif($way);
                            break;

                    case(2):
                            $main = imagecreatefromjpeg($way);
                            break;

                    case(3):
                            $main = imagecreatefrompng($way);
                            break;

                    default:
                            header('Location: ' . dirname(__FILE__) . "/photos/galaxije.jpg");
                            exit();
                            break;
            }

            $watermark = imagecreatefromgif($watermarkWay);
            list($width, $height) = getimagesize($watermarkWay);
            list($mainWidth, $mainHeight) = getimagesize($way);

            $x = ($mainWidth / 2) - ($width / 2);
            $y = ($mainHeight / 2) - ($height / 2);

            if(($imgInfo[2] == 1) || ($imgInfo[2] == 3))
            {
                    imagealphablending($main, false);
                    imagesavealpha($main, true);
            }

            imagecopymerge($main, $watermark, $x, $y, 0, 0, $width, $height, $opacity);

            //header('Content-type: ' . $imgInfo['mime']);
            switch($imgInfo[2])
            {
                    case(1): imagegif($main, $way); break;
                    case(2): imagejpeg($main, $way, 100); break;
                    case(3): imagepng($main, $way, 9); break;

                    default:
                            trigger_error('resizování obrázku se nezdaøilo', E_USER_WARNING);
                            break;
            }

            imagedestroy($main);
            imagedestroy($watermark);
    }
}
