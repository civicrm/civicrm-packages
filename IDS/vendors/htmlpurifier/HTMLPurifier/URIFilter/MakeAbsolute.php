<?php

// does not support network paths

/**
 * Class HTMLPurifier_URIFilter_MakeAbsolute
 */
class HTMLPurifier_URIFilter_MakeAbsolute extends HTMLPurifier_URIFilter
{
    /**
     * @var string
     */
    public $name = 'MakeAbsolute';
    /**
     * @var
     */
    protected $base;
    /**
     * @var array
     */
    protected $basePathStack = array();

    /**
     * @param $config
     *
     * @return bool
     */
    public function prepare($config) {
        $def = $config->getDefinition('URI');
        $this->base = $def->base;
        if (null === $this->base) {
            trigger_error('URI.MakeAbsolute is being ignored due to lack of value for URI.Base configuration', E_USER_WARNING);
            return false;
        }
        $this->base->fragment = null; // fragment is invalid for base URI
        $stack = explode('/', $this->base->path);
        array_pop($stack); // discard last segment
        $stack = $this->_collapseStack($stack); // do pre-parsing
        $this->basePathStack = $stack;
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
        if (null === $this->base) return true; // abort early
        if (
            $uri->path === '' && null === $uri->scheme
            && null === $uri->host
            && null === $uri->query
            && null === $uri->fragment
        ) {
            // reference to current document
            $uri = clone $this->base;
            return true;
        }
        if (null !== $uri->scheme) {
            // absolute URI already: don't change
            if (null !== $uri->host) return true;
            $scheme_obj = $uri->getSchemeObj($config, $context);
            if (!$scheme_obj) {
                // scheme not recognized
                return false;
            }
            if (!$scheme_obj->hierarchical) {
                // non-hierarchal URI with explicit scheme, don't change
                return true;
            }
            // special case: had a scheme but always is hierarchical and had no authority
        }
        if (null !== $uri->host) {
            // network path, don't bother
            return true;
        }
        if ($uri->path === '') {
            $uri->path = $this->base->path;
        } elseif ($uri->path[0] !== '/') {
            // relative path, needs more complicated processing
            $stack = explode('/', $uri->path);
            $new_stack = array_merge($this->basePathStack, $stack);
            if ($new_stack[0] !== '' && null !== $this->base->host) {
                array_unshift($new_stack, '');
            }
            $new_stack = $this->_collapseStack($new_stack);
            $uri->path = implode('/', $new_stack);
        } else {
            // absolute path, but still we should collapse
            $uri->path = implode('/', $this->_collapseStack(explode('/', $uri->path)));
        }
        // re-combine
        $uri->scheme = $this->base->scheme;
        if (null === $uri->userinfo) $uri->userinfo = $this->base->userinfo;
        if (null === $uri->host)     $uri->host = $this->base->host;
        if (null === $uri->port)     $uri->port = $this->base->port;
        return true;
    }

    /**
     * Resolve dots and double-dots in a path stack
     *
     * @param $stack
     *
     * @return array
     */
    private function _collapseStack($stack) {
        $result = array();
        $is_folder = false;
        for ($i = 0; isset($stack[$i]); $i++) {
            $is_folder = false;
            // absorb an internally duplicated slash
            if ($stack[$i] == '' && $i && isset($stack[$i+1])) continue;
            if ($stack[$i] == '..') {
                if (!empty($result)) {
                    $segment = array_pop($result);
                    if ($segment === '' && empty($result)) {
                        // error case: attempted to back out too far:
                        // restore the leading slash
                        $result[] = '';
                    } elseif ($segment === '..') {
                        $result[] = '..'; // cannot remove .. with ..
                    }
                } else {
                    // relative path, preserve the double-dots
                    $result[] = '..';
                }
                $is_folder = true;
                continue;
            }
            if ($stack[$i] == '.') {
                // silently absorb
                $is_folder = true;
                continue;
            }
            $result[] = $stack[$i];
        }
        if ($is_folder) $result[] = '';
        return $result;
    }
}

// vim: et sw=4 sts=4
