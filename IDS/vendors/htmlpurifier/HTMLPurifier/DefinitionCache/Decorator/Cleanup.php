<?php

/**
 * Definition cache decorator class that cleans up the cache
 * whenever there is a cache miss.
 */
class HTMLPurifier_DefinitionCache_Decorator_Cleanup extends
      HTMLPurifier_DefinitionCache_Decorator
{

    public $name = 'Cleanup';

    /**
     * @return \HTMLPurifier_DefinitionCache_Decorator|\HTMLPurifier_DefinitionCache_Decorator_Cleanup
     */
    public function copy() {
        return new HTMLPurifier_DefinitionCache_Decorator_Cleanup();
    }

    /**
     * @param $def
     * @param $config
     *
     * @return mixed
     */
    public function add($def, $config) {
        $status = parent::add($def, $config);
        if (!$status) {
            parent::cleanup($config);
        }
        return $status;
    }

    /**
     * @param $def
     * @param $config
     *
     * @return mixed
     */
    public function set($def, $config) {
        $status = parent::set($def, $config);
        if (!$status) {
            parent::cleanup($config);
        }
        return $status;
    }

    /**
     * @param $def
     * @param $config
     *
     * @return mixed
     */
    public function replace($def, $config) {
        $status = parent::replace($def, $config);
        if (!$status) {
            parent::cleanup($config);
        }
        return $status;
    }

    /**
     * @param $config
     *
     * @return mixed
     */
    public function get($config) {
        $ret = parent::get($config);
        if (!$ret) {
            parent::cleanup($config);
        }
        return $ret;
    }

}

// vim: et sw=4 sts=4
