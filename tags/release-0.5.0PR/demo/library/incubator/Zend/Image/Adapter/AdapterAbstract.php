<?php
require_once 'Zend/Image/Adapter/AdapterInterface.php';

abstract class Zend_Image_Adapter_AdapterAbstract
    implements Zend_Image_Adapter_AdapterInterface
{
    /**
     * The handle of this adapter
     *
     * @var object $_handle
     */
    protected $_handle = null;
    

    /**
     * Path of the location of the image
     */
    protected $_imagePath;
    
    protected $_length;
    protected $_height;
    protected $_width;

    public function __construct($config) {
        if(is_array($config)) {
            $this->setOptions($config);
        } else {
            $this->setConfig($config);
        }
    }
    
    /**
     * Set Adapter state from Zend_Config object
     *
     * @param  array $options
     * @return Zend_Image_Adapter_*
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);
            $method = 'set' . $normalized;
            
            switch($key) {
                case 'path':
                    $this->setPath($value);
                    break;
                default:
                    require 'Zend/Image/Exception.php';
                    throw new Zend_Image_Exception("Unknown config parameter specified: '" . $key . "'");
            }
        }

        return $this;
    }

    /**
     * Set Adapter state from Zend_Config object
     *
     * @param  Zend_Config $config
     * @return Zend_Image_Adapter_*
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }
    
    /**
     * Set the path of the image
     *
     * @param string   $path (Optional) The path of the image
     * @throw Zend_Image_Exception if path is set on nonexistent adapter
     */
    public function setImagePath ($path = null)
    {
        if (null !== $path) {
            $this->_imagePath = $path;
            if (null !== $this->_adapter) {
                $this->setImagePath();
            }
        } else {
            if (null === $this->_adapter) {
                require_once 'Zend/Image/Exception.php';
                throw new Zend_Image_Exception('Cannot set image path on an adapter that hasn\'t been set.');
            } elseif (! file_exists($this->_imagePath)) {
                require_once 'Zend/Image/Exception.php';
                throw new Zend_Image_Exception('Image path does not exist.');
            }
            
            $this->_adapter->setPath($this->_imagePath);
        }
        
        return $this;
    }

    


    /**
     * Get a string containing the image
     *
     * @param string $format (Optional) The format of the image to return
     * @return void
     */
    public function render ($format = 'png')
    {
        return $this->getImage($format);
    }

    public function display ($format = 'png', $sendHeader = true)
    {
        if ($sendHeader) {
            header('Content-type: image/png');
        }
        
        echo $this->render($format);
    }

    /**
     * Get a string containing the image
     *
     * @return string The image
     */
    public function __toString ()
    {
        return $this->render();
    }

    public function getHeight ()
    {
        return $this->_height;
    }

    public function getWidth ()
    {
        return $this->_width;
    }

    public function getImageLength ()
    {
        return $this->_length; 
    }
    
    /**
     * Perform an action on the image
     * @param mixed $param1
     * @param array $options Options that will be parsed on to the action
     * @return Zend_Image
     * @todo: use plugin loader.
     */
    public function perform($action, $parameters = array()) {
        if(is_string($action)) {
            $name = 'Zend_Image_Action_' . ucfirst($action);
            Zend_Loader::loadClass($name);
            $object = new $name($parameters);
            
            if(!$object instanceof Zend_Image_Action_ActionAbstract) {
                require_once 'Zend/Image/Exception.php';
                throw new Zend_Image_Exception('Action specified does not inherit from Zend_Image_Action_Abstract');
            }
        } elseif($action instanceof Zend_Image_Action_ActionAbstract) {
            $object = $action;
        }
        
        if (! $this->_handle) {
            $this->_loadHandle ();
        }
        
        if(null != ($result = $object->perform ( $this, $object ))) {
            $this->_handle = $result;
        }
        
        return $this;
    }
    
    /**
     * 
     * @param $handle
     * @todo: update height&width etc
     */
    public function setHandle($handle) {
        $this->_handle = $handle;
        return $this;
    }
    
    public function __call ($action, $arguments)
    {
        if(isset($arguments[0]) && 
           $arguments[0] instanceof Zend_Image_Action_ActionAbstract)
        {
            $this->perform($arguments[0]);
            
        } else {
            if(is_array($arguments) && isset($arguments[0])) {
                $this->perform($action, $arguments[0]);
            } else {
                $this->perform($action);
            }
        }
        
        return $this;
    }
    
}
