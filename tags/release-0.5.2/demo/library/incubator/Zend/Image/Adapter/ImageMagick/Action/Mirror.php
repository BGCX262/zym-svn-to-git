<?php

require_once 'Zend/Image/Adapter/ImageMagick/Action/ActionAbstract.php';

class Zend_Image_Adapter_ImageMagick_Action_Mirror
    extends Zend_Image_Adapter_ImageMagick_Action_ActionAbstract
{
    
    public function perform(Zend_Image_Adapter_ImageMagick $adapter,
        Zend_Image_Action_Mirror $rotate)
    {
        $handle = $adapter->getHandle();
        if($rotate->flop()) {
            $handle->flopImage();
        }
        
        if($rotate->flip()) {
            $handle->flipImage();
        }
    }

}
