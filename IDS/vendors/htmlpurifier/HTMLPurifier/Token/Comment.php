<?php

/**
 * Concrete comment token class. Generally will be ignored.
 */
class HTMLPurifier_Token_Comment extends HTMLPurifier_Token
{
    public $data; /**< Character data within comment. */
    public $is_whitespace = true;

    /**
     * Transparent constructor.
     *
     * @param      $data String comment data.
     * @param null $line
     * @param null $col
     */
    public function __construct($data, $line = null, $col = null) {
        $this->data = $data;
        $this->line = $line;
        $this->col  = $col;
    }
}

// vim: et sw=4 sts=4
