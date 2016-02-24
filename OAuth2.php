<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: AyGLR
 * Date: 24/01/16
 * Time: 00:56
 */

namespace Hoathis\CAuth;


final class OAuth2
{
    const DEFAULT_METHOD = 'post';
    const MARGIN_TIME = 30;

    /**
     * @var string
     */
    private $_clientId;

    /**
     * @var string
     */
    private $_clientSecret;

    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var Identity
     */
    protected $identity;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $path;

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->curl = curl_init();
    }

    /**
     *
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * @param string $code
     * @param string $method
     * @return self
     */
    public function authenticate(string $code, string $method = self::DEFAULT_METHOD): self
    {
        $data = [
            'grant_type' => 'authorization_code',
            'code'       => $code,
        ];

        $this->loadIdentity($data, $method);

        return $this;
    }

    /**
     * @param bool   $force
     * @param string $method
     * @return self
     */
    public function refresh(bool $force = false, string $method = self::DEFAULT_METHOD): self
    {
        if (
            (true === $force) ||
            ($this->identity->willExpiredIn(self::MARGIN_TIME))
        ) {
            $data = [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->identity->getRefreshToken(),
            ];

            $this->loadIdentity($data, $method);
        }

        return $this;
    }

    /**
     * @param string[] $data
     * @param string   $method
     * @return self
     */
    protected function loadIdentity(array $data, string $method): self
    {
        $this->identity = new Identity();
        $link           = $this->getUrl();
        $credentials    = $this->_clientId . ':' . $this->_clientSecret;
        $parameters     = http_build_query($data);

        $options = [
            CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_MAXREDIRS => 5,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_HTTPGET        => false,
            CURLOPT_NETRC          => false,
            CURLOPT_POST           => false,
            CURLOPT_PUT            => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT        => 5,
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL            => $link,
            //CURLOPT_USERAGENT      => self::$USER_AGENT,
            CURLOPT_USERPWD        => $credentials,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ];

        switch ($method) {
            case 'post':
                $options[CURLOPT_POST]       = true;
                $options[CURLOPT_POSTFIELDS] = $parameters;
                break;

            default:
                $options[CURLOPT_HTTPGET] = true;
                $options[CURLOPT_URL]     = $link . '?' . $parameters;
        }

        curl_setopt_array($this->curl, $options);

        $source = curl_exec($this->curl);

        /*
{
    "access_token": "22fe0c13e995da4a44a63a7ff549badb5d337a42bf80f17424482e35d4cca91a",
    "expires_at": 1382962374,
    "expires_in": 3600,
    "refresh_token": "8eb667707535655f2d9e14fc6491a59f6e06f2e73170761259907d8de186b6a1",
    "token_type": "bearer"
}
         */

        $identity = json_decode($source, true);
        $this->identity
            ->setAccessToken($identity['access_token'])
            ->setExpireAt($identity['expires_at'])
            ->setExpireIn($identity['expires_in'])
            ->setRefreshToken($identity['refresh_token'])
            ->setTokenType($identity['token_type']);

        return $this;
    }

    /**
     * @param string $url
     * @param string $clientId
     * @param string $clientSecret
     * @return self
     */
    public function open(string $url, string $clientId, string $clientSecret): self
    {
        $this
            ->setUrl($url)
            ->setClientId($clientId)
            ->setClientSecret($clientSecret);

        return $this;
    }

    /**
     * @param string $method
     * @return string
     */
    public function getAccessToken(string $method = self::DEFAULT_METHOD): string
    {
        return $this
            ->refresh(false, $method)
            ->getIdentity()
            ->getAccessToken();
    }

    /**
     * @param string $clientSecret
     * @return self
     */
    public function setClientSecret(string $clientSecret): self
    {
        $this->_clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @param string $clientId
     * @return self
     */
    public function setClientId(string $clientId): self
    {
        $this->_clientId = $clientId;

        return $this;
    }

    /**
     * @return Identity
     */
    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    /**
     * @param Identity $identity
     * @return self
     */
    public function setIdentity(Identity $identity): self
    {
        $this->identity = $identity;

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
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $url
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
        return $this->getBaseUrl() . $this->getPath();
    }


}