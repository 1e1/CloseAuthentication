<?php

declare (strict_types = 1);

/**
 * Created by PhpStorm.
 * User: AyGLR
 * Date: 23/01/16
 * Time: 21:45.
 */

namespace Hoathis\CAuth;

final class Crawler
{
    const STATE_HASH = 'SHA512';

    /**
     * @var string
     */
    public static $USER_AGENT = 'Hoa Crawler';

    /**
     * @var string
     */
    private $_state;

    /**
     * @var \SimpleXMLElement
     */
    private $_source;

    /**
     * @var array
     */
    private $_cookies;

    /**
     * @var string
     */
    private $_code;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $redirect_uri;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var resource
     */
    protected $curl;

    /**
     * @return self
     */
    public function load(): self
    {
        $data = [
            'client_id' => $this->client_id,
            'scope' => $this->scope,
            'redirect_uri' => $this->redirect_uri,
            'state' => $this->_state,
        ];
        $link = $this->baseUrl.$this->path.'?'.http_build_query($data);

        $options = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_NETRC => false,
            CURLOPT_POST => false,
            CURLOPT_PUT => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $link,
            CURLOPT_USERAGENT => self::$USER_AGENT,
            CURLOPT_VERBOSE => true,
        ];

        curl_setopt_array($this->curl, $options);

        $this->_source = null;
        $source = curl_exec($this->curl);

        list($header, $body) = explode("\r\n\r\n", $source, 2);

        $headers = explode("\r\n", $header);
        foreach ($headers as $header) {
            $headerName = strtok($header, ':');
            switch (strtolower($headerName)) {
                case 'set-cookie':
                    // "Set-Cookie: XEESessionId=k8keh9oicfa5hp0i9tmpmftqm3; path=/; HttpOnly"
                    $cookieName = strtok('=');
                    $cookieValue = strtok(';');

                    $this->_cookies[ltrim($cookieName)] = $cookieValue;
                    break;
            }
        }

        $doc = new \DOMDocument();
        $doc->loadHTML($body);
        $this->_source = simplexml_import_dom($doc);

        return $this;
    }

    /**
     * @param string $xpath
     *
     * @return \SimpleXMLElement
     *
     * @throws NoFormException
     */
    public function extractSimpleXmlForm(string $xpath = '//form'): \SimpleXMLElement
    {
        $forms = $this->_source->xpath($xpath);

        if (!isset($forms[0])) {
            throw new NoFormException();
        }

        return $forms[0];
    }

    /**
     * @param string $method
     * @param string $action
     * @param array  $data
     *
     * @return self
     *
     * @throws ErrorException
     * @throws NoLocationException
     * @throws StateException
     */
    public function submit(string $method, string $action, array $data): self
    {
        $link = $action;
        $parameters = http_build_query($data);
        $cookie = http_build_query($this->_cookies, '', '; ');

        $options = [
            CURLOPT_FOLLOWLOCATION => false,
            // CURLOPT_MAXREDIRS => 5,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPGET => false,
            CURLOPT_NETRC => false,
            CURLOPT_POST => false,
            CURLOPT_PUT => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_COOKIE => $cookie,
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $link,
            CURLOPT_USERAGENT => self::$USER_AGENT,
            CURLOPT_HTTP200ALIASES => [301, 302],
            // CURLOPT_HTTPHEADER     => [],
        ];

        switch ($method) {
            case 'post':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $parameters;
                break;

            default:
                $options[CURLOPT_HTTPGET] = true;
                $options[CURLOPT_URL] = $link.'?'.$parameters;
        }

        curl_setopt_array($this->curl, $options);

        $this->_code = null;
        /*$source      =*/
        curl_exec($this->curl);
        $location = curl_getinfo($this->curl, CURLINFO_REDIRECT_URL);

        if (false === $location) {
            throw new NoLocationException();
        }

        $elements = parse_url($location);
        parse_str($elements['query'], $infos);

        if (isset($this->_state) && ($this->_state !== $infos['state'])) {
            throw new StateException();
        }

        if (isset($infos['error'])) {
            throw new ErrorException($infos['error']);
        }

        if (isset($infos['code'])) {
            $this->_code = $infos['code'];
        }

        return $this;
    }

    /**
     * oauth constructor.
     */
    public function __construct()
    {
        $this->_cookies = [];
        $this->curl = curl_init();
        $this->_state = hash('SHA512', openssl_random_pseudo_bytes(1024));
    }

    /**
     *
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * @param string $state
     *
     * @return self
     */
    public function setState(string $state): self
    {
        $this->_state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     *
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return self
     */
    public function setUrl(string $url): self
    {
        $p = strpos($url, '/', 8);
        if (false === $p) {
            $this->setBaseUrl($url);
            $this->setPath('');
        } else {
            $this->setBaseUrl(substr($url, 0, $p));
            $this->setPath(substr($url, $p));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->getBaseUrl().$this->getPath();
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->client_id;
    }

    /**
     * @param string $client_id
     *
     * @return self
     */
    public function setClientId(string $client_id): self
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirect_uri;
    }

    /**
     * @param string $redirect_uri
     *
     * @return self
     */
    public function setRedirectUri(string $redirect_uri): self
    {
        $this->redirect_uri = $redirect_uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     *
     * @return self
     */
    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCode(): bool
    {
        return isset($this->_code);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }
}

class NoFormException extends \Exception
{
}

class NoLocationException extends \Exception
{
}

class StateException extends \Exception
{
}

class ErrorException extends \Exception
{
}
