<?php
require_once 'Zend/Image/Action/ActionAbstract.php';
require_once 'Zend/Loader/Autoloader.php';


class Zend_Image_Chain extends Zend_Image_Action_ActionAbstract {

    protected $_actions = array();

    public function subscribe($action, $options = array()) {
        if(is_string($action)) {
            $action = ucfirst($action);
            if (!Zend_Loader_Autoloader::autoload($action) &&
                !(($action = 'Zend_Image_Action_' . $action) &&
                      Zend_Loader_Autoloader::autoload($action)))
            {
                require_once 'Zend/Image/Exception.php';
                throw new Zend_Image_Exception(
                                'Could not instantiate specified class');
            }
            
            $action = new $action($options);
            
        } elseif($action instanceof Zend_Image_Action_ActionInterface) {
            $action->addOptions($options);
        } else {
            require_once 'Zend/Image/Exception.php';
            throw new Zend_Image_Exception('Given object does not seem' 
                                         . 'to be a valid Zend_Image Action');
        }

        $this->_actions[] = $action;
        return $this;
    }

    public function addOptions($options = array()) {
        $options = $this->_parseOptions($options);

        foreach($options as $key => $values) {
            $this->subscribe($key, $values);
        }

        return $this;
    }

    public function perform(Zend_Image_Adapter_AdapterAbstract $adapter = null) {
        foreach($this->_actions as $action) {
            if(null !== ($handle = $action->perform($adapter))) {
                $adapter->setHandle($handle);
            }
        }
    }

    public function getName() {
        return 'Chain';
    }

}
