<?php

/**
 * Class HTMLPurifier_URIFilter_DisableResources
 */
class HTMLPurifier_URIFilter_DisableResources extends HTMLPurifier_URIFilter
{
    public $name = 'DisableResources';

    /**
     * @param \Reference $uri
     * @param \Instance  $config
     * @param \Instance  $context
     *
     * @return bool
     */
    public function filter(&$uri, $config, $context) {
        return !$context->get('EmbeddedURI', true);
    }
}

// vim: et sw=4 sts=4
