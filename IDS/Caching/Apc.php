<?php
/**
 * PHPIDS
 * 
 * Requirements: PHP5, SimpleXML
 *
 * Copyright (c) 2008 PHPIDS group (https://phpids.org)
 *
 * PHPIDS is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, version 3 of the License, or 
 * (at your option) any later version.
 *
 * PHPIDS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with PHPIDS. If not, see <http://www.gnu.org/licenses/>. 
 *
 * PHP version 5.1.6+
 * 
 * @category Security
 * @package  PHPIDS
 * @author   Mario Heiderich <mario.heiderich@gmail.com>
 * @author   Christian Matthies <ch0012@gmail.com>
 * @author   Lars Strojny <lars@strojny.net>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL
 * @link     http://php-ids.org/
 */
 
require_once 'IDS/Caching/Interface.php';

/**
 * APC caching wrapper
 *
 * This class inhabits functionality to get and set cache via memcached.
 *
 * @category  Security
 * @package   PHPIDS
 * @author    Yves Berkholz <godzilla80@gmx.net>
 * @copyright 2007-2009 The PHPIDS Groupoup
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @version   Release: $Id$
 * @link      http://php-ids.org/
 * @since     Version 0.6.5
 */
class IDS_Caching_Apc implements IDS_Caching_Interface
{

    /**
     * Caching type
     *
     * @var string
     */
    private $type = null;

    /**
     * Cache configuration
     *
     * @var IDS_Init
     */
    private $config = null;

    /**
     * Flag if the filter storage has been found in memcached 
     * 
     * @var boolean 
     */ 
    private $isCached = false; 
    
    /**
     * Holds an instance of this class
     *
     * @var object
     */
    private static $cachingInstance = null;


    /**
     * Constructor
     *
     * @param  string $type caching type
     * @param  IDS_Init  $init the IDS_Init object
     * 
     * @return void
     */
    public function __construct($type, $init) 
    {

        $this->type   = $type;
        $this->config = $init->config['Caching'];
    }

    /**
     * Returns an instance of this class
     *
     * @param  string $type caching type
     * @param  object $init the IDS_Init object
     * 
     * @return object $this
     */
    public static function getInstance($type, $init) 
    {

        if (!self::$cachingInstance) {
            self::$cachingInstance = new IDS_Caching_Apc($type, $init);
        }

        return self::$cachingInstance;
    }

    /**
     * Writes cache data
     *
     * @param  array $data the caching data
     * 
     * @return object $this
     */
    public function setCache(array $data) 
    {
        if(!$this->isCached)
            apc_store($this->config['key_prefix'] . '.storage', 
            	$data, $this->config['expiration_time']);
        return $this;
    }

    /**
     * Returns the cached data
     *
     * Note that this method returns false if either type or file cache is 
     * not set
     *
     * @return mixed cache data or false
     */
    public function getCache() 
    {
       $data = apc_fetch($this->config['key_prefix'] . '.storage');
       $this->isCached = !empty($data); 
       return $data;
    }
}

/**
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 expandtab
 */
