<?php

/**
 * Pre-transform that changes deprecated hspace and vspace attributes to CSS
 */
class HTMLPurifier_AttrTransform_ImgSpace extends HTMLPurifier_AttrTransform {

    protected $attr;
    protected $css = array(
        'hspace' => array('left', 'right'),
        'vspace' => array('top', 'bottom')
    );

    /**
     * HTMLPurifier_AttrTransform_ImgSpace constructor.
     *
     * @param $attr
     */
    public function __construct($attr) {
        $this->attr = $attr;
        if (!isset($this->css[$attr])) {
            trigger_error(htmlspecialchars($attr) . ' is not valid space attribute');
        }
    }

    /**
     * @param \Assoc     $attr
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return \Assoc
     */
    public function transform($attr, $config, $context) {

        if (!isset($attr[$this->attr])) {
            return $attr;
        }

        $width = $this->confiscateAttr($attr, $this->attr);
        // some validation could happen here

        if (!isset($this->css[$this->attr])) {
            return $attr;
        }

        $style = '';
        foreach ($this->css[$this->attr] as $suffix) {
            $property = "margin-$suffix";
            $style .= "$property:{$width}px;";
        }

        $this->prependCSS($attr, $style);

        return $attr;

    }

}

// vim: et sw=4 sts=4
