<?php
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
     * @var array
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
        $this->uninputs = [];

        $this
            ->setSimpleXMLForm(new \SimpleXMLElement('<void/>'))
            ->setInputXpath('//input');
    }

    /**
     * @param \SimpleXMLElement $xmlForm
     * @return $this
     */
    public function setSimpleXMLForm(\SimpleXMLElement $xmlForm)
    {
        $this->xmlForm    = $xmlForm;
        $this->attributes = $this->xmlForm->attributes();

        return $this;
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function hasAttribute($attribute)
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * @param $attribute
     * @return string
     */
    public function getAttribute($attribute)
    {
        return isset($this->attributes[$attribute])
            ? '' . $this->attributes[$attribute]
            : '';
    }

    /**
     * @param $inputXpath
     * @return $this
     */
    public function setInputXpath($inputXpath)
    {
        $this->inputXpath = $inputXpath;

        return $this;
    }

    /**
     * @return string
     */
    public function getInputXpath()
    {
        return $this->inputXpath;
    }

    /**
     * @return \SimpleXMLElement[]
     */
    public function getInputs()
    {
        return $this->xmlForm->xpath($this->inputXpath);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasCompletion($key)
    {
        return isset($this->completions[$key]);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addCompletion($key, $value)
    {
        $this->completions[$key] = $value;

        return $this;
    }

    /**
     * @param array $completions
     * @return $this
     */
    public function addCompletions(array $completions)
    {
        $this->completions = $completions + $this->completions;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasInput($name)
    {
        return !isset($this->uninputs[$name]);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeInput($name)
    {
        $this->uninputs[$name] = true;

        return $this;
    }

    public function getData()
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