<?php

class Icommerce_Image {


    public static function convertPngToJpeg($pngUrl, $width=null, $height=null)
    {
        if(!$pngUrl) return;

        if(strpos($pngUrl, ".jpg"||".gif"))
            return $pngUrl;

        $pngUrl = str_replace(Mage::getBaseUrl(), "", $pngUrl."");
        $jpgUrl = str_replace('.png','.jpg',$pngUrl);

        if(file_exists($pngUrl) && !file_exists($jpgUrl)){

            try{

                $background = ImageCreateTrueColor($width, $height);
                $color=imagecolorallocate($background, 255, 255, 255);
                imagefill($background, 0, 0, $color);
                $image = imagecreatefrompng($pngUrl);

                if($width==null && $height==null){
                    $width = imagesx($image);
                    $height = imagesy($image);
                }

                $w = imagesx($image);
                $h = imagesy($image);
                $ratio=$w/$h;
                $target_ratio=$width/$height;
                if ($ratio>$target_ratio){
                    $new_w=$width;
                    $new_h=round($width/$ratio);
                    $x_offset=0;
                    $y_offset=round(($height-$new_h)/2);
                }else {
                    $new_h=$height;
                    $new_w=round($height*$ratio);
                    $x_offset=round(($width-$new_w)/2);
                    $y_offset=0;
                }
                $insert = ImageCreateTrueColor($new_w, $new_h);
                imagecopyResampled ($insert, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
                imagecopymerge($background,$insert,$x_offset,$y_offset,0,0,$new_w,$new_h,100);
                imagejpeg($background, $jpgUrl, 85);
                imagedestroy($insert);
                imagedestroy($background);

            }catch(Exception $ex){

                $jpgUrl = $pngUrl;
            }

        }

        return Mage::getBaseUrl().$jpgUrl;

    }









}