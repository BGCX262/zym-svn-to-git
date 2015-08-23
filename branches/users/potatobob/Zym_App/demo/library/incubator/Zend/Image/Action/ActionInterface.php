<?php
interface Zend_Image_Action_ActionInterface {
    
    public function __construct($options = array());
    
    public function addOptions($options = array());
    
    public function perform(Zend_Image_Adapter_AdapterAbstract $adapter = null);

    public function getName();
}
