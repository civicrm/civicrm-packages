<?php

/**
 * Simple transformation, just change tag name to something else,
 * and possibly add some styling. This will cover most of the deprecated
 * tag cases.
 */
class HTMLPurifier_TagTransform_Simple extends HTMLPurifier_TagTransform
{

    protected $style;

    /**
     * @param $transform_to Tag name to transform to.
     * @param $style CSS style to add to the tag
     */
    public function __construct($transform_to, $style = null) {
        $this->transform_to = $transform_to;
        $this->style = $style;
    }

    /**
     * @param \Tag       $tag
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return \Tag
     */
    public function transform($tag, $config, $context) {
        $new_tag = clone $tag;
        $new_tag->name = $this->transform_to;
        if (null !== $this->style
            &&
            ($new_tag instanceof HTMLPurifier_Token_Start || $new_tag instanceof HTMLPurifier_Token_Empty)
        ) {
            $this->prependCSS($new_tag->attr, $this->style);
        }
        return $new_tag;
    }

}

// vim: et sw=4 sts=4
