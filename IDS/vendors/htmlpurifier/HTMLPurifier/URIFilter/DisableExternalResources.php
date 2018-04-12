<?php

/**
 * Class HTMLPurifier_URIFilter_DisableExternalResources
 */
class HTMLPurifier_URIFilter_DisableExternalResources extends HTMLPurifier_URIFilter_DisableExternal
{
    public $name = 'DisableExternalResources';

    /**
     * @param $uri
     * @param $config
     * @param $context
     *
     * @return bool
     */
    public function filter(&$uri, $config, $context) {
        if (!$context->get('EmbeddedURI', true)) return true;
        return parent::filter($uri, $config, $context);
    }
}

// vim: et sw=4 sts=4
