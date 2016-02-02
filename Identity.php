<?php
/**
 * Created by PhpStorm.
 * User: AyGLR
 * Date: 23/01/16
 * Time: 21:46
 */

namespace Hoathis\CAuth;


final class Identity
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var int
     */
    protected $expireAt;

    /**
     * @var int
     */
    protected $expireIn;

    /**
     * @var string
     */
    protected $refreshToken;

    /**
     * @var string
     */
    protected $tokenType;

    public static function create()
    {
        return new self();
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return Identity
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * @param int $expireAt
     * @return Identity
     */
    public function setExpireAt($expireAt)
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        return $this->expireAt < time();
    }

    /**
     * @param $time
     * @return bool
     */
    public function willExpiredIn($time)
    {
        return $this->expireAt < time() + $time;
    }

    /**
     * @return int
     */
    public function getRemainingTime()
    {
        return $this->expireAt - time();
    }

    /**
     * @return int
     */
    public function getExpireIn()
    {
        return $this->expireIn;
    }

    /**
     * @param int $expireIn
     * @return Identity
     */
    public function setExpireIn($expireIn)
    {
        $this->expireIn = $expireIn;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return Identity
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @param string $tokenType
     * @return Identity
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;

        return $this;
    }


}