<?php

/**
 * Writes default type for all objects. Currently only supports flash.
 */
class HTMLPurifier_AttrTransform_SafeObject extends HTMLPurifier_AttrTransform
{
    public $name = "SafeObject";

    /**
     * @param \Assoc     $attr
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return \Assoc
     */
    public function transform($attr, $config, $context) {
        if (!isset($attr['type'])) {
            $attr['type'] = 'application/x-shockwave-flash';
        }
        return $attr;
    }
}

// vim: et sw=4 sts=4
