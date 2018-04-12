<?php

/**
 * Class HTMLPurifier_DoctypeRegistry
 */
class HTMLPurifier_DoctypeRegistry
{

    /**
     * Hash of doctype names to doctype objects
     */
    protected $doctypes;

    /**
     * Lookup table of aliases to real doctype names
     */
    protected $aliases;

    /**
     * Registers a doctype to the registry
     *
     * @note Accepts a fully-formed doctype object, or the
     *       parameters for constructing a doctype object
     *
     * @param       $doctype Name of doctype or literal doctype object
     * @param bool  $xml
     * @param array $modules Modules doctype will load
     * @param array $tidy_modules
     * @param array $aliases Alias names for doctype
     * @param null  $dtd_public
     * @param null  $dtd_system
     *
     * @return \HTMLPurifier_Doctype|\Name registered doctype
     */
    public function register($doctype, $xml = true, $modules = array(),
        $tidy_modules = array(), $aliases = array(), $dtd_public = null, $dtd_system = null
    ) {
        $modules = (array)$modules;
        $tidy_modules = (array)$tidy_modules;
        $aliases = (array)$aliases;
        if (!is_object($doctype)) {
            $doctype = new HTMLPurifier_Doctype(
                $doctype, $xml, $modules, $tidy_modules, $aliases, $dtd_public, $dtd_system
            );
        }
        $this->doctypes[$doctype->name] = $doctype;
        $name = $doctype->name;
        // hookup aliases
        foreach ($doctype->aliases as $alias) {
            if (isset($this->doctypes[$alias])) {
                continue;
            }
            $this->aliases[$alias] = $name;
        }
        // remove old aliases
        if (isset($this->aliases[$name])) {
            unset($this->aliases[$name]);
        }
        return $doctype;
    }

    /**
     * Retrieves reference to a doctype of a certain name
     * @note This function resolves aliases
     * @note When possible, use the more fully-featured make()
     * @param $doctype Name of doctype
     * @return Editable doctype object
     */
    public function get($doctype) {
        if (isset($this->aliases[$doctype])) {
            $doctype = $this->aliases[$doctype];
        }
        if (!isset($this->doctypes[$doctype])) {
            trigger_error('Doctype ' . htmlspecialchars($doctype) . ' does not exist', E_USER_ERROR);
            $anon = new HTMLPurifier_Doctype($doctype);
            return $anon;
        }
        return $this->doctypes[$doctype];
    }

    /**
     * Creates a doctype based on a configuration object,
     * will perform initialization on the doctype
     * @note Use this function to get a copy of doctype that config
     *       can hold on to (this is necessary in order to tell
     *       Generator whether or not the current document is XML
     *       based or not).
     * @param $config
     * @return \Editable
*/
    public function make($config) {
        return clone $this->get($this->getDoctypeFromConfig($config));
    }

    /**
     * Retrieves the doctype from the configuration object
     * @param $config
     * @return string
*/
    public function getDoctypeFromConfig($config) {
        // recommended test
        $doctype = $config->get('HTML.Doctype');
        if (!empty($doctype)) {
            return $doctype;
        }
        $doctype = $config->get('HTML.CustomDoctype');
        if (!empty($doctype)) {
            return $doctype;
        }
        // backwards-compatibility
        if ($config->get('HTML.XHTML')) {
            $doctype = 'XHTML 1.0';
        } else {
            $doctype = 'HTML 4.01';
        }
        if ($config->get('HTML.Strict')) {
            $doctype .= ' Strict';
        } else {
            $doctype .= ' Transitional';
        }
        return $doctype;
    }

}

// vim: et sw=4 sts=4
