<?php
require_once 'Zend/Image/Adapter/ImageMagick/Action/ActionAbstract.php';
require_once 'Zend/Image/Color.php';

class Zend_Image_Adapter_ImageMagick_Action_DrawArc
    extends Zend_Image_Adapter_ImageMagick_Action_ActionAbstract
{

    /**
     * Draws an arc on the handle
     *
     * @param Zend_Image_Adapter_ImageMagick $handle The handle on which the ellipse is drawn
     * @param Zend_Image_Action_DrawEllipse $ellipseObject The object that with all info
     */
    public function perform(Zend_Image_Adapter_ImageMagick $adapter,
        Zend_Image_Action_DrawArc $arcObject)
    { 

        $draw = new ImagickDraw();

	    $color = (string)$arcObject->getFillColor();
        $draw->setStrokeColor($color);
        
        $location = $arcObject->getLocation($adapter);

        $cx = $location->getX();
        $cy = $location->getY();
        $width = $arcObject->getWidth();
        $height = $arcObject->getHeight();

        $sx = $cx - $width / 2;
        $ex = $cx + $width / 2;

        $sy = $cy - $height / 2;
        $ey = $cy + $height / 2;

        //$draw->arc($sx, $sy, $ex, $ey, $arcObject->getCutoutStart(), $arcObject->getCutoutEnd());
//        $draw->arc($sx, $sy, $ex, $ey, 90, 315);
        $draw->arc($sx, $sy, $ex, $ey, 90, 270);

        $adapter->getHandle()->drawImage($draw);
                       
	}
}
