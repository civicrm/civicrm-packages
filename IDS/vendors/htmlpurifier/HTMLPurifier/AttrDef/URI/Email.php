<?php

/**
 * Class HTMLPurifier_AttrDef_URI_Email
 */
abstract class HTMLPurifier_AttrDef_URI_Email extends HTMLPurifier_AttrDef
{

    /**
     * Unpacks a mailbox into its display-name and address
     *
     * @param $string
     */
    public function unpack($string) {
        // needs to be implemented
    }

}

// sub-implementations

// vim: et sw=4 sts=4
