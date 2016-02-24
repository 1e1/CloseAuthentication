<?php
declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: AyGLR
 * Date: 23/01/16
 * Time: 21:46
 */

namespace Hoathis\CAuth;


final class Form
{

    /**
     * @var string[]
     */
    protected $completions;

    /**
     * @var array
     */
    protected $uninputs;

    /**
     * @var \SimpleXMLElement
     */
    protected $xmlForm;

    /**
     * @var \SimpleXMLElement
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $inputXpath;

    public function __construct()
    {
        $this->completions = [];
        $this->uninputs    = [];

        $this
            ->setSimpleXMLForm(new \SimpleXMLElement('<void/>'))
            ->setInputXpath('//input');
    }

    /**
     * @param \SimpleXMLElement $xmlForm
     * @return self
     */
    public function setSimpleXMLForm(\SimpleXMLElement $xmlForm): self
    {
        $this->xmlForm    = $xmlForm;
        $this->attributes = $this->xmlForm->attributes();

        return $this;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * @param string $attribute
     * @return string
     */
    public function getAttribute(string $attribute): string
    {
        return isset($this->attributes[$attribute])
            ? '' . $this->attributes[$attribute]
            : '';
    }

    /**
     * @param string $inputXpath
     * @return self
     */
    public function setInputXpath(string $inputXpath): self
    {
        $this->inputXpath = $inputXpath;

        return $this;
    }

    /**
     * @return string
     */
    public function getInputXpath(): string
    {
        return $this->inputXpath;
    }

    /**
     * @return \SimpleXMLElement[]
     */
    public function getInputs(): array
    {
        return $this->xmlForm->xpath($this->inputXpath);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasCompletion(string $key): bool
    {
        return isset($this->completions[$key]);
    }

    /**
     * @param string $key
     * @param string $value
     * @return self
     */
    public function addCompletion(string $key, string $value): self
    {
        $this->completions[$key] = $value;

        return $this;
    }

    /**
     * @param string[] $completions
     * @return self
     */
    public function addCompletions(array $completions): self
    {
        $this->completions = $completions + $this->completions;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasInput(string $name): bool
    {
        return !isset($this->uninputs[$name]);
    }

    /**
     * @param string $name
     * @return self
     */
    public function removeInput(string $name): self
    {
        $this->uninputs[$name] = true;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getData(): array
    {
        $data   = [];
        $inputs = $this->getInputs();

        foreach ($inputs as $input) {
            $attributes = $input->attributes();
            $name       = '' . $attributes['name'];
            $value      = $this->hasCompletion($name)
                ? $this->completions[$name]
                : '' . $attributes['value'];

            if ($this->hasInput($name)) {
                $data[$name] = $value;
            }
        }

        return $data;
    }
}