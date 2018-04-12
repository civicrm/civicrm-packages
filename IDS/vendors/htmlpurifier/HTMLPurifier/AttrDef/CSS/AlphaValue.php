<?php

/**
 * Class HTMLPurifier_AttrDef_CSS_AlphaValue
 */
class HTMLPurifier_AttrDef_CSS_AlphaValue extends HTMLPurifier_AttrDef_CSS_Number
{

    public function __construct() {
        parent::__construct(false); // opacity is non-negative, but we will clamp it
    }

    /**
     * @param $number
     * @param $config
     * @param $context
     *
     * @return bool|string
     */
    public function validate($number, $config, $context) {
        $result = parent::validate($number, $config, $context);
        if ($result === false) return $result;
        $float = (float) $result;
        if ($float < 0.0) $result = '0';
        if ($float > 1.0) $result = '1';
        return $result;
    }

}

// vim: et sw=4 sts=4
