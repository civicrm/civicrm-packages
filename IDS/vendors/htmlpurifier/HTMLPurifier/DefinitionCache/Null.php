<?php

/**
 * Null cache object to use when no caching is on.
 */
class HTMLPurifier_DefinitionCache_Null extends HTMLPurifier_DefinitionCache
{

    /**
     * @param $def
     * @param $config
     *
     * @return bool
     */
    public function add($def, $config) {
        return false;
    }

    /**
     * @param $def
     * @param $config
     *
     * @return bool
     */
    public function set($def, $config) {
        return false;
    }

    /**
     * @param $def
     * @param $config
     *
     * @return bool
     */
    public function replace($def, $config) {
        return false;
    }

    /**
     * @param $config
     *
     * @return bool
     */
    public function remove($config) {
        return false;
    }

    /**
     * @param $config
     *
     * @return bool
     */
    public function get($config) {
        return false;
    }

    /**
     * @param $config
     *
     * @return bool
     */
    public function flush($config) {
        return false;
    }

    /**
     * @param $config
     *
     * @return bool
     */
    public function cleanup($config) {
        return false;
    }

}

// vim: et sw=4 sts=4
