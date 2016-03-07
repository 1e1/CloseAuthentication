<?php
/**
 * Created by PhpStorm.
 * User: AyGLR
 * Date: 07/03/16
 * Time: 11:07.
 */

namespace Hoathis\CAuth;

abstract class AbstractCurl
{
    /**
     * @var resource
     */
    protected $curl;

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public static function getDefaultOptions(): array
    {
        return [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPGET => false,
            CURLOPT_NETRC => false,
            CURLOPT_POST => false,
            CURLOPT_PUT => null,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Hoa Crawler',
            CURLOPT_VERBOSE => false,
            // CURLOPT_HTTPHEADER     => [],
        ];
    }

    /**
     * @return self
     */
    public function reset(): self
    {
        $this->options = self::getDefaultOptions();

        return $this;
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function addOptions(array $options): self
    {
        $this->options = $options + $this->options;

        return $this;
    }

    /**
     * @return self
     */
    public function filterOptions(): self
    {
        foreach ($this->options as $key => $value) {
            if (null === $value) {
                unset($this->options[$key]);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return array_filter($this->options);
    }

    /**
     * @return string
     *
     * @throws CurlException
     */
    public function exec(): string
    {
        //var_dump('---', $this->options, '---');
        curl_setopt_array($this->curl, $this->options);

        $source = curl_exec($this->curl);

        if (false === $source) {
            throw new CurlException(curl_error($this->curl), curl_errno($this->curl));
        }

        return $source;
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return curl_getinfo($this->curl);
    }

    /**
     * AbstractCurl constructor.
     */
    public function __construct()
    {
        $this->reset();
        $this->curl = curl_init();
    }

    /**
     *
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }
}

class CurlException extends \Exception
{
}
