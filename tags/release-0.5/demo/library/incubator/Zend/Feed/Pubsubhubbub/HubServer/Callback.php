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
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage HubServer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Feed_Pubsubhubbub
 */
require_once 'Zend/Feed/Pubsubhubbub.php';

/**
 * @see Zend_Feed_Pubsubhubbub
 */
require_once 'Zend/Feed/Pubsubhubbub/CallbackAbstract.php';

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @subpackage HubServer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_HubServer_Callback
    extends Zend_Feed_Pubsubhubbub_CallbackAbstract
{
    /**
     * The URL Hub Servers must use when communicating with this Subscriber
     *
     * @var string
     */
    protected $_callbackUrl = '';

    /**
     * By default, the Hub creates permanent subscriptions which should
     * not require re-subscriptions.
     *
     * @var int
     */
    protected $_leaseSeconds = null;

    /**
     * The POST payload as a parameter array. Where multiple values are
     * attached to an identical key, the parameter is an array of those
     * values in the order in which they were presented in the payload.
     *
     * @var array
     */
    protected $_postData = array();

    /**
     * The preferred verification mode (sync or async). By default, this
     * Hub Server prefers synchronous verification, but will support
     * asynchronous in the future.
     *
     * @var string
     */
    protected $_preferredVerificationMode
        = Zend_Feed_Pubsubhubbub::VERIFICATION_MODE_SYNC;

    /**
     * An array of any errors encountered.
     *
     * @var array
     */
    protected $_errors = array();
    
    /**
     * Entities to push to subscribers
     * 
     * @var array Array of Zend_Feed_Pubsubhubbub_Entity objects
     */
    protected $_entities = array();

    /**
     * Set the callback URL to be used by Publishers or Subscribers when
     * communication with the Hub Server
     *
     * @param  string $url
     * @return Zend_Feed_Pubsubhubbub_HubServer_Callback
     */
    public function setCallbackUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "url"'
                . ' of "' . $url . '" must be a non-empty string and a valid'
                . 'URL');
        }
        $this->_callbackUrl = $url;
        return $this;
    }

    /**
     * Get the callback URL to be used by Publishers or Subscribers when
     * communication with the Hub Server
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        if (empty($this->_callbackUrl)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('A valid Callback URL MUST be'
                . ' set before attempting any operation');
        }
        return $this->_callbackUrl;
    }

    /**
     * Set the number of seconds for which any subscription will remain valid
     *
     * @param int $seconds
     * @return Zend_Feed_Pubsubhubbub_HubServer_Callback
     */
    public function setLeaseSeconds($seconds)
    {
        $seconds = intval($seconds);
        if ($seconds <= 0) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Expected lease seconds'
                . ' must be an integer greater than zero');
        }
        $this->_leaseSeconds = $seconds;
        return $this;
    }

    /**
     * Get the number of lease seconds on subscriptions
     *
     * @return int
     */
    public function getLeaseSeconds()
    {
        return $this->_leaseSeconds;
    }
    
    /**
     * Set preferred verification mode (sync or async). By default, this
     * Hub Server prefers synchronous verification, but will support
     * asynchronous in the future.
     *
     * @param  string $mode Should be 'sync' or 'async'
     * @return Zend_Feed_Pubsubhubbub_HubServer_Callback
     */
    public function setPreferredVerificationMode($mode)
    {
        if ($mode !== Zend_Feed_Pubsubhubbub::VERIFICATION_MODE_SYNC
            && $mode !== Zend_Feed_Pubsubhubbub::VERIFICATION_MODE_ASYNC
        ) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid preferred'
                . ' mode specified: "' . $mode . '" but should be one of'
                . ' Zend_Feed_Pubsubhubbub::VERIFICATION_MODE_SYNC or'
                . ' Zend_Feed_Pubsubhubbub::VERIFICATION_MODE_ASYNC');
        }
        $this->_preferredVerificationMode = $mode;
        return $this;
    }

    /**
     * Get preferred verification mode (sync or async).
     *
     * @return string
     */
    public function getPreferredVerificationMode()
    {
        return $this->_preferredVerificationMode;
    }
    
    /**
     * Inject model entities for storage
     *
     * @param  object $entity
     * @return Zend_Feed_Pubsubhubbub_HubServer_Callback
     */
    public function addEntity($name, $entity)
    {
        $this->_entities[$name] = $entity;
        return $this;
    }

    /**
     * Get named entity object
     * 
     * @param  string $name 
     * @return null|object
     */
    public function getEntity($name)
    {
        $entities = array('subscription');
        if (array_key_exists($name, $this->_entities)) {
            return $this->_entities[$name];
        } elseif (array_key_exists($name, $entities)) {
            require_once 'Zend/Feed/Pubsubhubbub/Entity/' . ucfirst($name) . '.php';
            $class = 'Zend_Feed_Pubsubhubbub_Entity_' . ucfirst($name);
            $this->_entities[$name] = new $class;
            return $this->_entities[$name];
        }
        // throw exception...?
    }
    
    /**
     * Handle any callback related to a subscription, unsubscription or
     * publisher notification of new feed updates.
     *
     * @param  array $httpData
     * @param  bool $sendResponseNow Whether to send response now or when asked
     * @return void
     */
    public function handle(array $httpData = null, $sendResponseNow = false)
    {
        $this->_postData = $this->_parseParameters();
        $response = $this->getHttpResponse();
        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $response->setHttpResponseCode(404);
        } elseif ($this->isValidSubscription()) {
            $this->_handleSubscription('subscribe');
            if ($this->isSuccess()) {
                $response->setHttpResponseCode(204);
            } else {
                $response->setHttpResponseCode(404);
            }
        } elseif ($this->isValidUnsubscription()) {
            $this->_handleSubscription('unsubscribe');
            if ($this->isSuccess()) {
                $response->setHttpResponseCode(204);
            } else {
                $response->setHttpResponseCode(404);
            }
        } elseif ($this->isValidPublication()) {
            $response->setHttpResponseCode(204);
            $this->_handlePublication();
        } else {
            $response->setHttpResponseCode(404);
        }
        if ($sendResponseNow) {
            $this->sendResponse();
        }
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @return bool
     */
    public function isValidSubscription()
    {
        if (!isset($this->_postData['hub.mode'])
            || $this->_postData['hub.mode'] !== 'subscribe'
        ) {
            return false;
        }
        if (!$this->_hasValidSubscriptionOpParameters()) {
            return false;
        }
        return true;
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @return bool
     */
    public function isValidUnsubscription()
    {
        if (!isset($this->_postData['hub.mode'])
            || $this->_postData['hub.mode'] !== 'unsubscribe'
        ) {
            return false;
        }
        if (!$this->_hasValidSubscriptionOpParameters()) {
            return false;
        }
        if (!$this->getStorage()->hasSubscription($this->_getTokenKey(
                $this->_postData['hub.callback'], $this->_postData['hub.topic']
            ))
        ) {
            return false;
        }
        return true;
    }
    
    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @return bool
     */
    public function isValidPublication()
    {
        if (!isset($this->_postData['hub.mode'])
            || $this->_postData['hub.mode'] !== 'publish'
        ) {
            return false;
        }
        if (!$this->_hasValidPublicationOpParameters()) {
            return false;
        }
        return true;
    }

    /**
     * Returns a boolean indicator of whether the notifications to a
     * Subscriber was successful. If it failed, FALSE is returned.
     *
     * @return bool
     */
    public function isSuccess()
    {
        if (count($this->_errors) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Return an array of errors met from any failures, including keys:
     * 'response' => the Zend_Http_Response object from the failure
     * 'callbackUrl' => the URL of the Subscriber whose confirmation failed
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Handle a (Un)subscription request (currently synchronous only)
     *
     * @return void
     */
    protected function _handleSubscription($type)
    {
        $client = $this->_getHttpClient($type);
        $client->setUri($this->_postData['hub.callback']);
        $client->setRawData($this->_getRequestParameters($type));
        $response = $client->request();
        $subscriptionKey = $this->_getTokenKey(
            $this->_postData['hub.callback'], $this->_postData['hub.topic']
        );
        $data = $this->getStorage()->getSubscription($subscriptionKey);
        if ($response->getStatus() < 200 || $response->getStatus() > 299
            || hash('sha256', $response->getBody()) !== $data['challenge']
        ) {
            $this->_errors[] = array(
                'response' => $response,
                'callback' => $this->_postData['hub.callback'],
                'topic'    => $this->_postData['hub.topic'],
            );
        } elseif ($type == 'subscribe') {
            $data = array(
                'subscription_key'   => $subscriptionKey,
                'callback'           => $this->_postData['hub.callback'],
                'topic'              => $this->_postData['hub.topic'],
                'created_time'       => time(),
                'last_modified'      => time(),
                'lease_seconds'      => $this->getLeaseSeconds(),
                'secret'             => null,
                'expiration_time'    => $this->getLeaseSeconds() ? time() + $this->getLeaseSeconds() : null,
                'subscription_state' => Zend_Feed_Pubsubhubbub::SUBSCRIPTION_VERIFIED,
            );
            if ($this->getStorage()->hasSubscription($subscriptionKey)) {
                $origData = $this->getStorage()->getSubscription($subscriptionKey);
                $data['created_time'] = $origData['created_time'];
            }
            $this->getStorage()->setSubscription($subscriptionKey, $data);
        } elseif ($type == 'unsubscribe') {
            $this->getStorage()->removeSubscription($subscriptionKey);
        }
    }
    
    /**
     * Handle an incoming ping from a Publisher. Since this is an open architecture,
     * the Publisher need not have any Subscribers with the Hub, so the only
     * qualification for a positive response is the validity of the attached Topic
     * URIs. Here, we assume a positive response was sent, and we just need to
     * offload the topics to an asynchronous job queue.
     *
     * @return void
     */
    protected function _handlePublication() 
    {
    }

    /**
     * Get a basic prepared HTTP client for use
     *
     * @return Zend_Http_Client
     */
    protected function _getHttpClient()
    {
        $client = Zend_Feed_Pubsubhubbub::getHttpClient();
        $client->setMethod(Zend_Http_Client::GET);
        $client->setConfig(array(
            'useragent' => 'Zend_Feed_Pubsubhubbub_HubServer/' . Zend_Version::VERSION,
        ));
        return $client;
    }

    /**
     * Return a list of standard protocol/optional parameters for addition to
     * client's POST body that are specific to the current Hub Server URL
     *
     * @param  string $mode
     * @return string
     */
    protected function _getRequestParameters($mode)
    {
        if (!in_array($mode, array('subscribe', 'unsubscribe'))) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid mode specified: "'
                . $mode . '" which should have been "subscribe" or "unsubscribe"');
        }
        $params = array(
            'hub.callback' => $this->getCallbackUrl(),
            'hub.mode'     => $mode,
            'hub.topic'    => $this->_postData['hub.topic'],
        );
        if (isset($this->_postData['hub.verify_token'])) {
            $params['hub.verify_token'] = $this->_postData['hub.verify_token'];
        }

        /**
         * Establish a persistent Hub challenge and add to parameters
         */
        $key = $this->_getTokenKey(
            $this->_postData['hub.callback'], $this->_postData['hub.topic']
        );
        $challenge = $this->_getToken();
        $this->_storeSubscription($key, $challenge);
        $params['hub.challenge'] = $challenge;
        if ($mode == 'subscribe') {
            $params['hub.lease_seconds'] = $this->getLeaseSeconds() ? $this->getLeaseSeconds() : '';
        }

        return $this->_toByteValueOrderedString(
            $this->_urlEncode($params)
        );
    }
    
    /**
     * Store Subscription Data to backend storage
     *
     * @param  string $key
     * @param  string $challenge
     * @return void
     */
    protected function _storeSubscription($key, $challenge)
    {
        $data = array();
        if ($this->getStorage()->hasSubscription($key)) {
            $data = $this->getStorage()->getSubscription($key);
        } else {
            $data = array(
                'subscription_key' => $key,
                'callback'         => $this->_postData['hub.callback'],
                'callback_hash'    => null,
                'topic'            => $this->_postData['hub.topic'],
                'topic_hash'       => null,
                'created_time'     => time(),
                'secret'           => null,
                'hmac_algorithm'   => null,
                'eta'              => time(),
                'confirm_failures' => 0,
            );
        }
        $data['last_modified']      = time();
        $data['subscription_state'] = Zend_Feed_Pubsubhubbub::SUBSCRIPTION_NOTVERIFIED;
        $data['expiration_time']    = $this->getLeaseSeconds() ? time() + $this->getLeaseSeconds() : null;
        $data['lease_seconds']      = $this->getLeaseSeconds();
        $data['challenge']          = hash('sha256', $challenge);
        $this->getStorage()->setSubscription($key, $data);
    }

    /**
     * Check the validity of the parameters for (un)subscription requests
     *
     * @return bool
     */
    protected function _hasValidSubscriptionOpParameters()
    {
        $required = array(
            'hub.mode', 
            'hub.callback',
            'hub.topic', 
            'hub.verify',
        );

        foreach ($required as $key) {
            if (!array_key_exists($key, $this->_postData)) {
                return false;
            }
        }
        if (!Zend_Uri::check($this->_postData['hub.topic'])) {
            return false;
        }
        if (!Zend_Uri::check($this->_postData['hub.callback'])) {
            return false;
        }
        return true;
    }
    
    /**
     * Check the validity of the parameters for publisher update pings
     *
     * @return bool
     */
    protected function _hasValidPublicationOpParameters()
    {
        $required = array('hub.mode', 'hub.url');
        foreach ($required as $key) {
            if (!array_key_exists($key, $this->_postData)) {
                return false;
            }
        }
        if (is_array($this->_postData['hub.url'])) {
            foreach ($this->_postData['hub.url'] as $url) {
                if (!Zend_Uri::check($url)) {
                    return false;
                }
            }
        } else {
            if (!Zend_Uri::check($this->_postData['hub.url'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Build an array of POST parameters from the raw body (this prevents)
     * the overwrites of keys in $_POST for repeated keyed parameters
     *
     * @return array|void
     */
    protected function _parseParameters()
    {
        $params = array();
        $body   = $this->_getRawBody();
        if (empty($body)) {
            return array();
        }
        $parts = explode('&', $body);
        foreach ($parts as $kvpair) {
            $pair  = explode('=', $kvpair);
            $key   = rawurldecode($pair[0]);
            $value = rawurldecode($pair[1]);
            if (isset($params[$key])) {
                if (is_array($params[$key])) {
                    $params[$key][] = $value;
                } else {
                    $params[$key] = array($params[$key], $value);
                }
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server. Follows no particular method, which means
     * it might be improved/changed in future.
     *
     * @return string
     */
    protected function _getToken()
    {
        if (!empty($this->_testStaticToken)) {
            return $this->_testStaticToken;
        }
        return uniqid(rand(), true) . time();
    }

    /**
     * Simple helper to generate a verification token used in (un)subscribe
     * requests to a Hub Server.
     *
     * @param  string $subscriberUrl The Hub Server URL for which this token will apply
     * @param  string $topicUrl
     * @param  string $type
     * @return string
     */
    protected function _getTokenKey($subscriberUrl, $topicUrl, $type = '')
    {
        $keyBase = $subscriberUrl . $topicUrl . $type;
        $key     = md5($keyBase);
        return $key;
    }

    /**
     * URL Encode an array of parameters
     *
     * @param  array $params
     * @return array
     */
    protected function _urlEncode(array $params)
    {
        $encoded = array();
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $ekey = Zend_Feed_Pubsubhubbub::urlencode($key);
                $encoded[$ekey] = array();
                foreach ($value as $duplicateKey) {
                    $encoded[$ekey][]
                        = Zend_Feed_Pubsubhubbub::urlencode($duplicateKey);
                }
            } else {
                $encoded[Zend_Feed_Pubsubhubbub::urlencode($key)]
                    = Zend_Feed_Pubsubhubbub::urlencode($value);
            }
        }
        return $encoded;
    }

    /**
     * Order outgoing parameters
     *
     * @param  array $params
     * @return array
     */
    protected function _toByteValueOrderedString(array $params)
    {
        $return = array();
        uksort($params, 'strnatcmp');
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return implode('&', $return);
    }
    
    /**
     * This is STRICTLY for testing purposes only...
     */
    protected $_testStaticToken = null;
    final public function setTestStaticToken($token)
    {
        $this->_testStaticToken = (string) $token;
    }
}
