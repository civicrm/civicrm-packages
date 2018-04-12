<?php

/**
 * Class HTMLPurifier_URIFilter_HostBlacklist
 */
class HTMLPurifier_URIFilter_HostBlacklist extends HTMLPurifier_URIFilter
{
    public $name = 'HostBlacklist';
    protected $blacklist = array();

    /**
     * @param $config
     *
     * @return bool
     */
    public function prepare($config) {
        $this->blacklist = $config->get('URI.HostBlacklist');
        return true;
    }

    /**
     * @param \Reference $uri
     * @param \Instance  $config
     * @param \Instance  $context
     *
     * @return bool
     */
    public function filter(&$uri, $config, $context) {
        foreach($this->blacklist as $blacklisted_host_fragment) {
            if (strpos($uri->host, $blacklisted_host_fragment) !== false) {
                return false;
            }
        }
        return true;
    }
}

// vim: et sw=4 sts=4
