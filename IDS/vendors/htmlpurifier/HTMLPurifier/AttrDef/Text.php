<?php

/**
 * Validates arbitrary text according to the HTML spec.
 */
class HTMLPurifier_AttrDef_Text extends HTMLPurifier_AttrDef
{

    /**
     * @param String     $string
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return mixed|string
     */
    public function validate($string, $config, $context) {
        return $this->parseCDATA($string);
    }

}

// vim: et sw=4 sts=4
