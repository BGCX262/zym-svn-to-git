<?php
require_once 'Zend/Image/Color.php';

class Zend_Image_Adapter_ImageMagick_Action_DrawLine {
/**
 * Draw a line on the image, returns the GD-handle
 *
 * @param  Zend_Image_Adapter_ImageMagick image resource    $handle Image to work on
 * @param  Zend_Image_Action_DrawLine   $lineObject The object containing all settings needed for drawing a line.
 * @return void
 */
    public function perform(Zend_Image_Adapter_ImageMagick $adapter,
        Zend_Image_Action_DrawLine $lineObject) {

        $handle = $adapter->getHandle();

        $draw = new ImagickDraw();

        $draw->setStrokeWidth($lineObject->getStrokeWidth());

        $color = $lineObject->getStrokeColor();
        $draw->setStrokeColor((string)$color);
        $draw->setStrokeOpacity($lineObject->getStrokeAlpha());
        $draw->line($lineObject->getPointStart()->getX(),
            $lineObject->getPointStart()->getY(),
            $lineObject->getPointEnd()->getX(),
            $lineObject->getPointEnd()->getY());

        $handle->drawImage($draw);
    }

}
