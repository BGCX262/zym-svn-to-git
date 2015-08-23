<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Tumblr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Tumblr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Tumblr_Post_Abstract
{
    /**
     * Post id
     *
     * @var int|null
     */
    protected $_id;
    
    /**
     * Post url
     *
     * @var string|null
     */
    protected $_url;
    
    /**
     * Post url with slug
     *
     * @var string|null
     */
    protected $_urlWithSlug;
    
    /**
     * Generator / source of post
     *
     * @var string|null
     */
    protected $_generator;
    
    /**
     * Date of post in blog timezone
     *
     * @var string|null
     */
    protected $_date;
    
    /**
     * GMT date of post
     *
     * @var string
     */
    protected $_gmtDate;
    
    /**
     * Tags for post
     *
     * @var array
     */
    protected $_tags = array();
    
    /**
     * Format of content
     *
     * @var string|null
     */
    protected $_format;
    
    /**
     * Post to a group instead of your own blog
     *
     * @var string|null
     */
    protected $_group;
    
    /**
     * @var Zend_Tumblr_Service
     */
    protected $_service;
    
    /**
     * Base parameters for every save request
     *
     * @var array
     */
    protected $_params = array();
    
    /**
     * Constructor
     *
     * @param Zend_Service_Tumblr $service
     */
    public function __construct(Zend_Service_Tumblr $service)
    {
        $this->_service = $service;
    }
    
    /**
     * Set post by <post> SimpleXmlElement
     *
     * @param DOMElement $postElement 
     * @return void
     */
    public function setFromXmlElement(SimpleXMLElement $postElement)
    {
        $this->_setBaseFromXmlElement($postElement);
        $this->_setFromXmlElement($postElement);
    }
    
    /**
     * Sets all common base properties from post
     *
     * @param DomElement $postElement 
     * @return void
     */
    protected function _setBaseFromXmlElement(SimpleXMLElement $postElement)
    {
        $this->setId((string) $postElement['id']);
        $this->setDate((string) $postElement['date']);
        $this->setFormat((string) $postElement['format']);
        //read only attributes (reason for no public setter)
        $this->_url         = (string) $postElement['url'];
        $this->_urlWithSlug = (string) $postElement['url-with-slug'];
        $this->_gmtDate     = (string) $postElement['date-gmt'];
        if (isset($postElement['generator'])) {
            $this->setGenerator((string) $postElement['generator']);
        }
        
        if (isset($postElement['group'])) {
            $this->setGroup((string) $postElement['group']);
        }
        
        $tagList = $postElement->tag;
        $tags = array();
        foreach ($tagList as $tag) {
            $tags[] = (string) $tag;
        }
        
        $this->setTags($tags);
    }
    
    /**
     * Set post type specific properties
     *
     * @param DOMElement $post 
     * @return void
     */
    abstract protected function _setFromXmlElement(SimpleXMLElement $post);
    
    /**
     * Id setter
     *
     * @param int $id 
     * @return void
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }
    
    /**
     * Id getter
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Url getter
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->_url;
    }
    
    /**
     * Url with slug getter
     *
     * @return string|null
     */
    public function getUrlWithSlug()
    {
        return $this->_urlWithSlug;
    }
    
    /**
     * Generator getter
     *
     * @return string|null
     */
    public function getGenerator()
    {
        return $this->_generator;
    }
    
    /**
     * Generator setter
     *
     * @param string $generator 
     * @return void
     */
    public function setGenerator($generator)
    {
        $this->_generator = $generator;
    }
    
    /**
     * Date getter
     *
     * @return string|null
     */
    public function getDate()
    {
        return $this->_date;
    }
    
    /**
     * Date setter
     *
     * @param string $date 
     * @return void
     */
    public function setDate($date)
    {
        $this->_date = $date;
    }
    
    /**
     * Gmt date getter
     *
     * @return string|null
     */
    public function getGmtDate()
    {
        return $this->_gmtDate;
    }
    
    /**
     * Format getter
     *
     * @return string|null
     */
    public function getFormat()
    {
        return $this->_format;
    }
    
    /**
     * Format setter
     *
     * @param string $format 
     * @return void
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }
    
    /**
     * Tags getter
     *
     * @return array
     */
    public function getTags()
    {
        return $this->_tags;
    }
    
    /**
     * Tags setter
     *
     * @param array $tags 
     * @return void
     */
    public function setTags(array $tags)
    {
        $this->_tags = $tags;
    }
    
    /**
     * Add a tag
     *
     * @param string $tag 
     * @return void
     */
    public function addTag($tag)
    {
        $this->_tags[] = $tag;
    }
    
    /**
     * Group getter
     *
     * @return string|null
     */
    public function getGroup()
    {
        return $this->_group;
    }
    
    /**
     * Group setter
     *
     * @param string $group
     * @return void
     */
    public function setGroup($group)
    {
        $this->_group = $group;
    }
    
    /**
     * Save post
     *
     * @return bool
     */
    public function save()
    {
        $this->_buildBaseParams();
        $this->_save();
        $response = $this->_service->makeWriteRequest($this->_getParameters());
        if ($response->isSuccessful()) {
            //if it is a new post set the id
            if ($this->getId() === null && is_numeric($response->getBody())) {
                $this->setId($response->getBody());
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Save method for post
     *
     * @abstract
     * @return bool
     */
    abstract protected function _save();
    
    /**
     * Returns array version of post
     * 
     * @return array post data
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'generator' => $this->getGenerator(),
            'date' => $this->getDate(),
            'tags' => $this->getTags(),
            'format' => $this->getFormat(),
            'group' => $this->getGroup()
        );
    }

    /**
     * Overloading getter properties
     *
     * Proxies to getter methods
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $methodName = 'get' . ucfirst($key);
        if (!method_exists($this, $methodName)) {
            require_once 'Zend/Service/Tumblr/Exception.php';
            throw new Zend_Service_Tumblr_Exception("Invalid property {$key}");
        }

        return $this->$methodName();
    }

    /**
     * Overloading setter properties
     *
     * Proxies to setter methods
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $methodName = 'set' . ucfirst($key);
        if (!method_exists($this, $methodName)) {
            require_once 'Zend/Service/Tumblr/Exception.php';
            throw new Zend_Service_Tumblr_Exception("Invalid property {$key}");
        }

        $this->$methodName($value);
    }
    
    /**
     * Build base paramaters for posts
     *
     * @return array base parameters
     */
    protected function _buildBaseParams()
    {
        $this->_setParameter('post-id', $this->getId());
        $this->_setParameter('email', $this->_service->getEmail());
        $this->_setParameter('password', $this->_service->getPassword());
        $this->_setParameter('generator', $this->getGenerator());
        $this->_setParameter('date', $this->getDate());
        if (count($this->getTags())) {
            $this->_setParameter('tags', implode(',', $this->getTags()));
        }
        
        $this->_setParameter('format', $this->getFormat());
        $this->_setParameter('group', $this->getGroup());
        return $this->_getParameters();
    }
    
    /**
     * Get parameters
     *
     * @return array
     */
    protected function _getParameters()
    {
        return $this->_params;
    }
    
    /**
     * Add a base parameter
     * 
     * Takes care of not adding values that are not null
     *
     * @param string $name 
     * @param string $value 
     * @return void
     */
    protected function _setParameter($key, $value)
    {
        if ($value !== null) {
            $this->_params[$key] = $value;
        }
    }
}