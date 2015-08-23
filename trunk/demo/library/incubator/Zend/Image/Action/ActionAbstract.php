<?php
require_once 'Zend/Image/Action/ActionInterface.php';
require_once 'Zend/Loader.php';

abstract class Zend_Image_Action_ActionAbstract
    implements Zend_Image_Action_ActionInterface
{

    public function __construct($options = array()) {
        $this->addOptions($options);
    }
    
    protected function _parseOptions($options) {
        if(is_object($options)) {
            if ($options instanceof Zend_Config ||
                method_exists($options,'toArray'))
            {
                $options = $options->toArray();
            }
        }
        
        return (array)$options;
    }
    
    public function perform(Zend_Image_Adapter_AdapterAbstract $adapter = null) {
        if(null === $adapter) {
            require_once 'Zend/Image/Action/Exception.php';
            throw new Zend_Image_Action_Exception('No adapter given.');
        }
        
        $name = 'Zend_Image_Adapter_' . $adapter->getName() . '_Action_' . $this->getName();
        Zend_Loader::loadClass ( $name );

        $actionObject = new $name ( );
        return $actionObject->perform ( $adapter, $this);
    }

}
