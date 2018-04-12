<?php

/**
 * Special-case enum attribute definition that lazy loads allowed frame targets
 */
class HTMLPurifier_AttrDef_HTML_FrameTarget extends HTMLPurifier_AttrDef_Enum
{

    public $valid_values = false; // uninitialized value
    protected $case_sensitive = false;

    public function __construct() {}

    /**
     * @param String     $string
     * @param \Mandatory $config
     * @param \Mandatory $context
     *
     * @return bool|string
     */
    public function validate($string, $config, $context) {
        if ($this->valid_values === false) {
            $this->valid_values = $config->get('Attr.AllowedFrameTargets');
        }
        return parent::validate($string, $config, $context);
    }

}

// vim: et sw=4 sts=4
