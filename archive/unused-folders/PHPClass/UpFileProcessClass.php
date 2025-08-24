<?php
<? 
function thumnail($file, $save_filename, $save_path, $max_width, $max_height)
{
       $img_info = getImageSize($file);
       if($img_info[2] == 1)
       {
              $src_img = ImageCreateFromGif($file);
              }elseif($img_info[2] == 2){
              $src_img = ImageCreateFromJPEG($file);
              }elseif($img_info[2] == 3){
              $src_img = ImageCreateFromPNG($file);
              }else{
              return 0;
       }
       $img_width = $img_info[0];
       $img_height = $img_info[1];

       if($img_width > $max_width || $img_height > $max_height)
       {
              if($img_width == $img_height)
              {
                     $dst_width = $max_width;
                     $dst_height = $max_height;
              }elseif($img_width > $img_height){
                     $dst_width = $max_width;
                     $dst_height = ceil(($max_width / $img_width) * $img_height);
              }else{
                     $dst_height = $max_height;
                     $dst_width = ceil(($max_height / $img_height) * $img_width);
              }
       }else{
              $dst_width = $img_width;
              $dst_height = $img_height;
       }
       if($dst_width < $max_width) $srcx = ceil(($max_width - $dst_width)/2); else $srcx = 0;
       if($dst_height < $max_height) $srcy = ceil(($max_height - $dst_height)/2); else $srcy = 0;

       if($img_info[2] == 1) 
       {
              $dst_img = imagecreate($max_width, $max_height);
       }else{
              $dst_img = imagecreatetruecolor($max_width, $max_height);
       }

       $bgc = ImageColorAllocate($dst_img, 255, 255, 255);
       ImageFilledRectangle($dst_img, 0, 0, $max_width, $max_height, $bgc); 
       ImageCopyResampled($dst_img, $src_img, $srcx, $srcy, 0, 0, $dst_width, $dst_height, ImageSX($src_img),ImageSY($src_img));

       if($img_info[2] == 1) 
       {
              ImageInterlace($dst_img);
              ImageGif($dst_img, $save_path.$save_filename);
       }elseif($img_info[2] == 2){
              ImageInterlace($dst_img);
              ImageJPEG($dst_img, $save_path.$save_filename);
       }elseif($img_info[2] == 3){
              ImagePNG($dst_img, $save_path.$save_filename);
       }
       ImageDestroy($dst_img);
       ImageDestroy($src_img);
} 
?> 