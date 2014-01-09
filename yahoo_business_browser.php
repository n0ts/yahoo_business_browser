<?php
/**
 * Class login and scraping your business.yahoo.co.jp pages
 *
 * PHP version 5
 *
 * @category Scraping
 * @package  Naoya Nakazawa
 * @author   Naoya Nakazawa <me@n0ts.org>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 */

/*
 * Class representing a HTTP request message
 * PEAR package should be installed
 */
require_once 'HTTP/Request2.php';
require_once 'simple_html_dom.php';

/**
 * Class login and scraping your business.yahoo.co.jp pages
 *
 */
class YahooBusinessBrowser
{
    /*
     * HTTP_Request2 Class Object
     */
    protected $rq;
    protected $rs;

    protected $user_name;
    protected $password;
    public    $cookies;
    public    $body;
    public    $last_error_message;

    /*
     * error codes
     */
    public $ERROR_GET_BEACON_URL = 1;


    /**
     * construction function
     *
     * @param string $id      yahoo business user account
     * @param string $pass    yahoo business user password
     * @param array  $cookies optionl settings if you know login cookies
     *
     * @return void
     */
    function __construct($id, $pass, $cookies = array())
    {
        $this->rq = new HTTP_Request2();
        $this->rq->setConfig(array(
            'connect_timeout' => 60,
            'timeout' => 60,
        ));
        $this->rq->setHeader(
            'User-Agent',
            'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko'
        );
        $this->rq->setHeader('Keep-Alive', 15);
        $this->rq->setHeader('Connection', 'keep-alive');
        $this->id = $id;
        $this->pass = $pass;
        if (empty($cookies)) {
            $this->login();
        } else {
            $this->cookies = $cookies;
        }

        $this->last_error_message = '';
    }

    /**
     * login yahoo
     *
     * @return boolean
     */
    function login()
    {
        $login_url = 'https://login.bizmanager.yahoo.co.jp/login';
        $login_params = '?url=https://pmc.business.yahoo.co.jp';
        $current_cookies = $this->cookies;
        $this->cookies = array();
        if (!$this->getGetBody($login_url . $login_params)) {
            return false;
        }

        // get post params
        preg_match_all(
            '/<input type="hidden" name="(.*?)" value="(.*?)" ?>/',
            $this->body,
            $matches_post_params,
            PREG_SET_ORDER
        );

        // find img tag src="bX.yahoo.co.jp"
        $beacon_url = '';
        $html = str_get_html($this->body);
        foreach ($html->find('img') as $img) {
            if (preg_match('/https:\/\/b.*\.yahoo.co.jp.*/', $img->src, $matches_img)) {
                $beacon_url = $matches_img[0];
                break;
            }
        }
        if (empty($beacon_url)) {
            $this->last_error_message = sprintf('Could not find bean url from %s',
                                                $login_url);
            return false;
        }

        // get cookie
        if (!$this->getGetBody($beacon_url)) {
            return false;
        }

        // login
        $post_data = array();
        foreach ($matches_post_params as $entry) {
            $post_data[$entry[1]] = $entry[2];
        }
        $post_data['user_name'] = $this->id;
        $post_data['password'] = $this->pass;

        $this->getPostBody($login_url . $login_params, $post_data);

        $response_cookies = $this->cookies;

        $this->cookies = $current_cookies;
        $this->_updateCookies($response_cookies);

        if (empty($this->cookies)) {
            $this->last_error_message = 'Cookie is empty';
            return false;
        }

        return true;
    }

    /**
     * get response body function
     *
     * @param string $url     target url
     * @param string $referer referer url
     *
     * @return boolean
     */
    function getBody($url, $referer = '')
    {
        if (empty($url)) {
            $this->last_error_message = 'URL is empty';
            return false;
        }

        try {
            $this->rq->setUrl($url);
            $this->rq->setHeader('Referer', $referer);
            if (!empty($this->cookies)) {
                foreach ($this->cookies as $cookie) {
                    $this->rq->addCookie($cookie['name'], $cookie['value']);
                }
            }

            $this->rs = $this->rq->send();
            $this->_updateCookies();
            $this->body = $this->rs->getBody();
        } catch (HTTP_Request2_Exception $e) {
            $this->last_error_message = $e->getMessage();
            return false;
        }

        return true;
    }

    function getGetBody($url, $referer = '') {
        // clear request body
        $this->rq->setBody('');

        $this->rq->setMethod(HTTP_Request2::METHOD_GET);
        return $this->getBody($url, $referer);
    }

    function getPostBody($url, $post_data = array(), $referer = '')
    {
        // clear request body
        $this->rq->setBody('');

        $this->rq->setMethod(HTTP_Request2::METHOD_POST);
        foreach ($post_data as $key => $value) {
            $this->rq->addPostParameter($key, $value);
        }

        return $this->getBody($url, $referer);
    }

    /**
     * get response header by key
     *
     * @param string $key header name
     *
     * @return header value
     */
    function getResponseHeader($key) {
        $headers = $this->rs->getHeader();
        return isset($headers[$key]) ? $headers[$key] : '';
    }

    /**
     * Update cookies function
     *
     * @param array $response_cookies responsed cookies
     *
     * @return boolean
     */
    function _updateCookies($response_cookies = array())
    {
        if (empty($response_cookies)) {
            $response_cookies = $this->rs->getCookies();
        }
        if (empty($response_cookies)) {
            return false;
        }
        for ($i=0; $i < count($response_cookies); $i++) {
            $create = true;
            for ($j=0; $j < count($this->cookies); $j++) {
                if ($this->cookies[$j]['name'] === $response_cookies[$i]['name']) {
                    $this->cookies[$j]['value'] = $response_cookies[$i]['value'];
                    $create = false;
                }
            }
            if ($create) {
                $new_cookies[] = array(
                    'name' => $response_cookies[$i]['name'],
                    'value' => $response_cookies[$i]['value']
                );
            }
        }

        if (!empty($new_cookies)) {
            foreach ($new_cookies as $new_cookie) {
                $this->cookies[] = $new_cookie;
            }
        }
    }

} // END class
?>
