<?php

/**
 * Represents a directive ID in the interchange format.
 */
class HTMLPurifier_ConfigSchema_Interchange_Id
{

    public $key;

    /**
     * HTMLPurifier_ConfigSchema_Interchange_Id constructor.
     *
     * @param $key
     */
    public function __construct($key) {
        $this->key = $key;
    }

    /**
     * @warning This is NOT magic, to ensure that people don't abuse SPL and
     *          cause problems for PHP 5.0 support.
     */
    public function toString() {
        return $this->key;
    }

    /**
     * @return bool|string
     */
    public function getRootNamespace() {
        return substr($this->key, 0, strpos($this->key, '.'));
    }

    /**
     * @return bool|string
     */
    public function getDirective() {
        return substr($this->key, strpos($this->key, '.') + 1);
    }

    /**
     * @param $id
     *
     * @return \HTMLPurifier_ConfigSchema_Interchange_Id
     */
    public static function make($id) {
        return new HTMLPurifier_ConfigSchema_Interchange_Id($id);
    }

}

// vim: et sw=4 sts=4
