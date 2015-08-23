<?php

require_once 'Zend/Image/Color.php';
require_once 'Zend/Image/Adapter/ImageMagick/Action/ActionAbstract.php';

class Zend_Image_Adapter_ImageMagick_Action_Crop {

    public function perform(Zend_Image_Adapter_ImageMagick $adapter,
        Zend_Image_Action_Crop $crop)
    {
        $handle = $adapter->getHandle();

        $x = $crop->getX();
        $y = $crop->getY();

        $width = $crop->getWidth();
        $height = $crop->getHeight();

        $handle->cropImage($width, $height, $x, $y);
    }

}
