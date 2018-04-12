<?php

/**
 * Class HTMLPurifier_AttrTransform_SafeEmbed
 */
class HTMLPurifier_AttrTransform_SafeEmbed extends HTMLPurifier_AttrTransform
{
    public $name = 'SafeEmbed';

    /**
     * @param \Assoc     $attr
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return \Assoc
     */
    public function transform($attr, $config, $context) {
        $attr['allowscriptaccess'] = 'never';
        $attr['allownetworking'] = 'internal';
        $attr['type'] = 'application/x-shockwave-flash';
        return $attr;
    }
}

// vim: et sw=4 sts=4
