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
 * @package    Zend_Service_Linkback
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */ 

/**
 * Linkback client
 *
 * @category   Zend
 * @package    Zend_Service_Linkback
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Linkback_Client
{
    const TRACKBACK = 'trackback';
    const PINGBACK  = 'pingback';
    const REFBACK   = 'refback';
    /**
     * @var Zend_Http_Client
     */
    protected static $_client;

    /**
     * Source Uri
     * @var string
     */
    protected $_sourceUrl;

    /**
     * Target Uri
     * @var <type>
     */
    protected $_targetUrl;

    /**
     * Exerpt to send to remote host for Trackback and Pingback requests
     * @var string
     */
    protected $_excerpt;

    /**
     * Title to send to remote host for Trackback and Pingback requests
     * @var <type>
     */
    protected $_sourceTitle;

    /**
     * Records the sent linkback types
     * @var <type>
     */
    protected $_sentLinkbacks = array();

    /**
     * Sets the types of linkback to send.
     * @var <type>
     */
    protected $_sendLinkbackTypes = array(
        self::PINGBACK  => true,
        self::TRACKBACK => true,
        self::REFBACK   => true,
    );

    /**
     * Stores the response from the HTTP client
     * @var Zend_Http_Response
     */
    protected $_httpResponse = null;

    /**
     * @var string
     */
    protected $_pingXmlRpc = null;

    /**
     * @var string
     */
    protected $_trackbackXmlRpc = null;

    /**
     * Ping a remote resource
     * Will read metadata at the target URL and determine
     * automatically whether to use pingback or trackback
     *
     * @param string|Zend_Uri $sourceUrl
     * @param string|Zend_Uri $targetUrl
     * @param string          $excerpt
     * @param string          $sourceTitle
     * @return void
     */
    public function ping($sourceUrl, $targetUrl, $excerpt = null, $sourceTitle = null)
    {
        $this->setSourceUrl($sourceUrl);
        $this->setTargetUrl($targetUrl);
        if (null !== $excerpt) {
            $this->_excerpt = (string) $excerpt;
        }

        if (null !== $sourceTitle) {
            $this->_sourceTitle = (string) $sourceTitle;
        }

        $response = $this->_getResponse();

        if ($this->hasPingback($response)
            && $this->_sendLinkbackTypes[self::PINGBACK]) {
            $this->_sentLinkbacks[] = self::PINGBACK;
            // Send ping
        }
        
        if ($this->hasTrackback($response)) {
            // Send Trackback
        }
    }

    /**
     * Set the linkback target url
     *
     * @param  string|Zend_Uri $targetUrl
     * @return Zend_Service_Linkback_Client
     */
    public function setTargetUrl($targetUrl)
    {
        $this->_targetUrl = (string) $targetUrl;
        return $this;
    }

    /**
     * Set the linkback source URL
     *
     * @param string|Zend_Uri $sourceUrl
     * @return Zend_Service_Linkback_Client
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->_sourceUrl = (string) $sourceUrl;
        return $this;
    }

    /**
     * Gets the linkback target url
     *
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->_targetUrl;
    }

    /**
     * Sets the source url
     *
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->_sourceUrl;
    }

    /**
     * Fetches the HTTP response from the remote server
     *
     * @return Zend_Http_Response
     */
    protected function _getResponse()
    {
        if (null === $this->_httpResponse) {
            $client = $this->getHttpClient();
            $client->setUri($this->getTargetUrl());
            if($this->_sendLinkbackTypes[self::REFBACK]) {
                $client->setHeaders('Referer', $this->getSourceUrl());
            }
            $this->_httpResponse = $client->request(Zend_Http_Client::GET);
            $this->_sentLinkbacks[] = self::REFBACK;
        }
        return $this->_httpResponse;
    }

    /**
     * Checks the set url for the presence of a pingback xmlrpc
     *
     * @return boolean
     */
    public function hasPingback()
    {
        $response = $this->_getResponse();
        $pingHeader = $response->getHeader('X-Pingback');
        if (null !== $pingHeader) {
                $this->setPingXmlRpc($pingHeader);
            return true;
        }
        $matches = array();
        if (preg_match('#<link rel="pingback" href="([^"]+)" ?/?>#',
                       $response->getBody(),
                       $matches)) {
            // If regular expression is used, only the first match should be
            // used, as per the specification
            $this->setPingXmlRpc($matches[1]);
            return true;
        }
        return false;
    }

    /**
     * Checks the HTTP response for a trackback RDF
     * Will set the trackback XmlRpc location if a trackback url is
     * detected
     *
     * @return boolean
     */
    public function hasTrackback()
    {
        $response = $this->_getResponse();
        $matches = array();
        if (preg_match('#trackback.ping="(.*?)"#',
                       $response->getBody(),
                       $matches)) {
            $this->setTrackbackXmlRpc($matches[1]);
            return true;
        }
        return false;
    }



    /**
     * Set the HTTP client to use to send HTTP requests.
     *
     * @param Zend_Http_Client $client
     * @return void
     */
    public static function setHttpClient(Zend_Http_Client $client)
    {
        self::$_client = $client;
    }

    /**
     * Get the registered HTTP client.
     * If no client is set, a HTTP client is automatically registered.
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (null === self::$_client) {
            self::$_client = new Zend_Http_Client();
        }
        return self::$_client;
    }

    /**
     * Reset the Http client response
     * @return Zend_Service_Linkback_Client
     */
    public function resetHttpResponse()
    {
        $this->_httpResponse = null;
        return $this;
    }

    /**
     * Set a XMLRpc endpoint uri for sending pingbacks
     *
     * @param  string $pingXmlRpc
     * @return Zend_Service_Linkback_Client
     */
    public function setPingXmlRpc($pingXmlRpc)
    {
        $this->_pingXmlRpc = (string) $pingXmlRpc;
        return $this;
    }

    /**
     * Gets the XMLRpc endpoint uri detected for pingbacks (if any)
     *
     * @return string|null
     */
    public function getPingXmlRpc()
    {
        return $this->_pingXmlRpc;
    }

    /**
     * Sets a XMLRpc endpoint uri for sending trackbacks
     *
     * @param string $trackbackXmlRpc
     * @return Zend_Service_Linkback_Client
     */
    public function setTrackbackXmlRpc($trackbackXmlRpc)
    {
        $this->_trackbackXmlRpc = (string) $trackbackXmlRpc;
        return $this;
    }

    /**
     * Get the XMLRpc endpoint uri detected for trackbacks (if any)
     *
     * @return string|null
     */
    public function getTrackbackXmlRpc()
    {
        return $this->_trackbackXmlRpc;
    }
}