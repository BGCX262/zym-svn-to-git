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
class Zend_Service_Tumblr
{
    /**
     * Tumblr email
     *
     * @var string
     */
    protected $_email;
    
    /**
     * Tumblr password
     *
     * @var string
     */
    protected $_password;
    
    /**
     * Zend_Http_Client_Adapter_Curl
     *
     * @var Zend_Http_Client_Adapter_Curl
     */
    protected $_httpClient;
    
    /**
     * Valid Tumblr post types
     *
     * @var array
     */
    protected $_validPostTypes = array(
        'text',
        'photo',
        'video',
        'audio',
        'quote',
        'link',
        'conversation'
    );
        
    const API_URI = 'http://www.tumblr.com';
    const PATH_READ = '/api/read';
    const PATH_WRITE = '/api/write';
    const PATH_DELETE = '/api/delete';
    const PATH_AUTHENTICATE = '/api/authenticate';
    
    /**
     * Set email and password on init
     *
     * @param string $email 
     * @param string $password 
     */
    public function __construct($email = null, $password = null)
    {
        if ($email !== null && $password !== null) {
            $this->setEmail($email);
            $this->setPassword($password);
        }
    }
    
    /**
     * Gets http client instance
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (!$this->_httpClient instanceof Zend_Http_Client) {
            require_once 'Zend/Http/Client.php';
            //require_once 'Zend/Http/Client/Adapter/Curl.php';
            //$adapter = new Zend_Http_Client_Adapter_Curl();
            $this->_httpClient = new Zend_Http_Client();
            //$this->_httpClient->setAdapter($adapter);
        }
        
        return $this->_httpClient;
    }
    
    /**
     * Http client instance setter
     *
     * @param Zend_Http_Client $httpClient 
     * @return void
     */
    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        $this->_httpClient = $httpClient;
    }
    
    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }
    
    /**
     * Set email
     *
     * @param string $email 
     * @return void
     */
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    
    /**
     * Get password
     *
     * @return string $this->_password
     */
    public function getPassword()
    {
        return $this->_password;
    }
    
    /**
     * Set password
     *
     * @param string $password 
     * @return void
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }
    
    /**
     * Connection timeout setter
     *
     * Proxies to the http client. 
     * Provides a documented and easy way to set the timeout without dealing with 
     * the http client.
     *
     * @param int $timeout
     * @return void
     */
    public function setConnectionTimeout($timeout)
    {
        $httpClient = $this->getHttpClient();
        $httpClient->setConfig(array('timeout', $timeout));
    }
    
    /**
     * Factory method to create new post
     *
     * @param string $postType 
     * @return Zend_Service_Tumblr_Post_Abstract
     * @throws Zend_Service_Exception
     */
    public function createNewPost($postType)
    {
        return $this->createPost($postType);
    }
    
    /**
     * Create post
     *
     * @param string $postType 
     * @param DOMElement $postElement (optional) 
     * @return Zend_Service_Tumblr_Post_Abstract
     * @throws Zend_Service_Exception
     */
    public function createPost($postType, $postElement = null)
    {
        $postType = strtolower($postType);
        //a regular post the same as a text post
        if ($postType == 'regular') {
            $postType = 'text';
        }
        
        if (in_array($postType, $this->_validPostTypes)) {
            $postType = ucfirst($postType);
            $className = "Zend_Service_Tumblr_Post_{$postType}";
            if (!class_exists($className)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($className);
            }

            $post = new $className($this);
            if ($postElement !== null) {
                $post->setFromXmlElement($postElement);
            }
            
            return $post;
        }
        
        require_once 'Zend/Service/Exception.php';
        throw new Zend_Service_Exception("Post type {$postType} is not valid");
    }
    
    /**
     * Checks to see if authenticate credentials exist
     *
     * @return void
     */
    public function hasAuthentication()
    {
        if (!is_null($this->getEmail()) && !is_null($this->getPassword())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Authenticate
     *
     * @return bool
     */
    public function authenticate()
    {
        if (!$this->hasAuthentication()) {
            return false;
        }
        
        $response = $this->makeReadRequest(
            self::API_URI . self::PATH_AUTHENTICATE, 
            array(
                'email'     => $this->getEmail(),
                'password'  => $this->getPassword()
            )
        );
        
        if ($response->isSuccessful()) {
            return true; 
        }
        
        return false;
    }
    
    /**
     * Get posts for specific tumblelog url
     *
     * @param string $tumblelogUrl 
     * @param array $options (optional)
     * @return array
     */
    public function getPosts($tumblelogUrl, $options = array())
    {
        $validOptions = array(
            'start',
            'num',
            'type',
            'id',
            'filter',
            'search',
            'tagged'
        );
        foreach ($options as $key => $value) {
            if (!in_array($key, $validOptions)) {
                /**
                 * @see Zend_Service_Exception
                 */
                require_once 'Zend/Service/Exception.php';
                throw new Zend_Service_Exception("Option {$key} is not a valid option.");
            }
        }
        
        if ($this->hasAuthentication()) {
            $options['email'] = $this->getEmail();
            $options['password'] = $this->getPassword();
        }
        
        $response = $this->makeReadRequest('http://' . $tumblelogUrl . self::PATH_READ, $options);
        if (!$response->isSuccessful()) {
            return false;
        }
        
        $xml = new SimpleXmlElement($response->getBody());
        $postNodes = $xml->posts->post;
        $posts = array();
        foreach ($postNodes as $post) {
           $post = $this->createPost((string) $post['type'], $post);
           $posts[] = $post;
        }
        
        return $posts;
    }
    
    /**
     * Get single post by id
     *
     * @param string $tumblelogUrl 
     * @param int $id post id
     * @param array $options (optional)
     * @return Zend_Service_Tumblr_Post_Abstract|bool
     */
    public function getPostById($tumblelogUrl, $id, $options = array())
    {
        $options['id'] = $id;
        $posts = $this->getPosts($tumblelogUrl, $options);
        if (is_array($posts)) {
            return current($posts);
        }
        
        return false;
    }
    
    /**
     * Delete post by id
     *
     * @param int $id post id
     * @return bool
     */
    public function deletePostById($id)
    {
        $response = $this->_makeRequest(self::API_URI . self::PATH_DELETE, 'POST', array('post-id' => $id));
        if ($response->isSuccessful() && $response->getBody() == 'Deleted') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Make read request
     *
     * @param string $uri 
     * @param array $params 
     * @return DOMDocument|bool
     */
    public function makeReadRequest($uri, $params = array())
    {
        if ($this->hasAuthentication()) {
            $method = 'POST';
        } else {
            $method = 'GET';
        }
        
        return $this->_makeRequest($uri, $method, $params);
    }
    
    /**
     * Make tumblr write request
     *
     * @param array $params
     * @return Zend_Http_Response
     */
    public function makeWriteRequest(array $params)
    {
        return $this->_makeRequest(self::API_URI . self::PATH_WRITE, 'POST', $params);
    }
    
    /**
     * Make tumblr request
     *
     * @param string $uri 
     * @param string $method 
     * @param array $params 
     * @return Zend_Http_Response
     */
    protected function _makeRequest($uri, $method, array $params)
    {
        $httpClient = $this->getHttpClient();
        $httpClient->setUri($uri);
        $httpClient->setMethod(Zend_Http_Client::POST);
        if (isset($params['data'])) {
            $httpClient->setFileUpload($params['data'], 'data');
            unset($params['data']);
        }
        
        if ($method == Zend_Http_Client::POST) {
            $httpClient->setParameterPost($params);
        } else {
            $httpClient->setParameterGet($params);
        }
        
        return $httpClient->request();
    }
}