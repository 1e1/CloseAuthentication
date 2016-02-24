<?php
declare(strict_types = 1);
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

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return self
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpireAt(): int
    {
        return $this->expireAt;
    }

    /**
     * @param int $expireAt
     * @return self
     */
    public function setExpireAt(int $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        return $this->expireAt < time();
    }

    /**
     * @param int $time
     * @return bool
     */
    public function willExpiredIn(int $time): bool
    {
        return $this->expireAt < time() + $time;
    }

    /**
     * @return int
     */
    public function getRemainingTime(): int
    {
        return $this->expireAt - time();
    }

    /**
     * @return int
     */
    public function getExpireIn(): int
    {
        return $this->expireIn;
    }

    /**
     * @param int $expireIn
     * @return self
     */
    public function setExpireIn(int $expireIn): self
    {
        $this->expireIn = $expireIn;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return self
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenType(): self
    {
        return $this->tokenType;
    }

    /**
     * @param string $tokenType
     * @return self
     */
    public function setTokenType(string $tokenType): self
    {
        $this->tokenType = $tokenType;

        return $this;
    }


}