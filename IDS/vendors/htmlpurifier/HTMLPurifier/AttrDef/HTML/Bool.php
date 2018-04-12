<?php

/**
 * Validates a boolean attribute
 */
class HTMLPurifier_AttrDef_HTML_Bool extends HTMLPurifier_AttrDef
{

    protected $name;
    public $minimized = true;

    /**
     * HTMLPurifier_AttrDef_HTML_Bool constructor.
     *
     * @param bool $name
     */
    public function __construct($name = false) {$this->name = $name;}

    /**
     * @param String     $string
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return bool
     */
    public function validate($string, $config, $context) {
        if (empty($string)) return false;
        return $this->name;
    }

    /**
     * @param $string Name of attribute
     *
     * @return \HTMLPurifier_AttrDef_HTML_Bool
     */
    public function make($string) {
        return new HTMLPurifier_AttrDef_HTML_Bool($string);
    }

}

// vim: et sw=4 sts=4
