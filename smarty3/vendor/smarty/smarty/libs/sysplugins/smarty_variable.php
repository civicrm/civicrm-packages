<?php

/**
 * class for the Smarty variable object
 * This class defines the Smarty variable object
 *
 * @package    Smarty
 * @subpackage Template
 */
/*
 * Later versions of PHP deprecate dynamic properties in a class. Since it is
 * possible, without a detailed knowledge of the code, that a variable we
 * haven't declared might be a different class depending on the context,
 * we add this line. See
 * https://deycode.com/posts/
 * how-to-fix-php-warning-deprecated-creation-of-dynamic-property-is-deprecated
 */

#[\AllowDynamicProperties]
class Smarty_Variable
{
    /**
     * template variable
     *
     * @var mixed
     */
    public $value = null;

    /**
     * if true any output of this variable will be not cached
     *
     * @var boolean
     */
    public $nocache = false;

    /**
     * create Smarty variable object
     *
     * @param mixed   $value   the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     */
    public function __construct($value = null, $nocache = false)
    {
        $this->value = $value;
        $this->nocache = $nocache;
    }

    /**
     * <<magic>> String conversion
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
