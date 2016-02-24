<?php
declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: AyGLR
 * Date: 23/01/16
 * Time: 21:53
 */

namespace Hoathis\CAuth;


final class CAuth2
{
    const ELEMENT_VOID = '<void/>';

    /**
     * @var Crawler
     */
    public $crawler;

    /**
     * @var Form
     */
    public $form;

    /**
     * @var string
     */
    public $xpath;

    /**
     * @var \SimpleXMLElement
     */
    private $_source;

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * CAuth constructor.
     */
    public function __construct()
    {
        $this->crawler = new Crawler();
        $this->form    = new Form();
        $this->xpath   = '//form';
        $this->_source = new \SimpleXMLElement(self::ELEMENT_VOID);
    }

    /**
     * @param string $url
     * @param string $clientId
     * @param string $scope
     * @return self
     * @throws NoFormException
     */
    public function open(string $url, string $clientId, string $scope = ''): self
    {
        $this->_source = new \SimpleXMLElement(self::ELEMENT_VOID);

        $this->crawler
            ->setUrl($url)
            ->setClientId($clientId)
            ->setScope($scope)
            ->setRedirectUri('http://localhost');
        $this->_source = $this->crawler
            ->load()
            ->extractSimpleXmlForm($this->xpath);

        return $this;
    }

    /**
     * @param array $completions
     * @param array $uninputs
     * @return self
     * @throws ErrorException
     * @throws NoLocationException
     * @throws StateException
     * @throws \Exception
     */
    public function submit(array $completions, array $uninputs = []): self
    {
        $this->form
            ->setSimpleXMLForm($this->_source)
            ->addCompletions($completions);

        foreach ($uninputs as $uninput) {
            $this->form->removeInput($uninput);
        }

        $method = 'get';
        $action = $this->crawler->getUrl();

        if ($this->form->hasAttribute('method')) {
            $method = $this->form->getAttribute('method');
        }

        if ($this->form->hasAttribute('action')) {
            $action = $this->form->getAttribute('action');
            if (isset($action[0])) {
                if ('/' === $action[0]) {
                    $action = $this->crawler->getBaseUrl() . $action;
                } else {
                    throw new \Exception("TODO: " . __FILE__ . " L" . __LINE__);
                }
            }
        }

        $this->crawler->submit($method, $action, $this->form->getData());

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->crawler->getCode();
    }

    /**
     * @param string $message
     * @return self
     */
    public function write(string $message): self
    {
        echo $message, PHP_EOL;

        return $this;
    }
}