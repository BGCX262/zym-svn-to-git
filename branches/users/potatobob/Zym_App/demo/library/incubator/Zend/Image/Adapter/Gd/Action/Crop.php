<?php

require_once 'Zend/Image/Color.php';
require_once 'Zend/Image/Adapter/ImageMagick/Action/ActionAbstract.php';

class Zend_Image_Adapter_Gd_Action_Crop {

    public function perform(Zend_Image_Adapter_Gd   $adapter,
                            Zend_Image_Action_Crop  $crop)
    {
        $handle = $adapter->getHandle();

        $targetWidth  = $crop->getWidth();
        $targetHeight = $crop->getHeight();
        
        $im = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopy($im, $adapter->getHandle(), 0, 0, $crop->getX(), $crop->getY(), $targetWidth, $targetHeight);

        return $im;
    }
}
