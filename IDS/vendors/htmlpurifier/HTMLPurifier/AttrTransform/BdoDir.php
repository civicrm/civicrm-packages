<?php

// this MUST be placed in post, as it assumes that any value in dir is valid

/**
 * Post-trasnform that ensures that bdo tags have the dir attribute set.
 */
class HTMLPurifier_AttrTransform_BdoDir extends HTMLPurifier_AttrTransform
{

    /**
     * @param \Assoc     $attr
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return \Assoc
     */
    public function transform($attr, $config, $context) {
        if (isset($attr['dir'])) return $attr;
        $attr['dir'] = $config->get('Attr.DefaultTextDir');
        return $attr;
    }

}

// vim: et sw=4 sts=4
