<?php

/**
 * Validates http (HyperText Transfer Protocol) as defined by RFC 2616
 */
class HTMLPurifier_URIScheme_http extends HTMLPurifier_URIScheme {

    public $default_port = 80;
    public $browsable = true;
    public $hierarchical = true;

    /**
     * @param \Reference            $uri
     * @param \HTMLPurifier_Config  $config
     * @param \HTMLPurifier_Context $context
     *
     * @return bool
     */
    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        return true;
    }

}

// vim: et sw=4 sts=4
