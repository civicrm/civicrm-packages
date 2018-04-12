<?php

/**
 * Structure object describing a directive alias
 */
class HTMLPurifier_ConfigDef_DirectiveAlias extends HTMLPurifier_ConfigDef
{
    public $class = 'alias';
    
    /**
     * Namespace being aliased to
     */
    public $namespace;
    /**
     * Directive being aliased to
     */
    public $name;

    /**
     * HTMLPurifier_ConfigDef_DirectiveAlias constructor.
     *
     * @param $namespace
     * @param $name
     */
    public function __construct($namespace, $name) {
        $this->namespace = $namespace;
        $this->name = $name;
    }
}

