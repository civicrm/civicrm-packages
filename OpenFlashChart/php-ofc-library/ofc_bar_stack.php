<?php

include_once 'ofc_bar_base.php';

/**
 * Class bar_stack
 */
class bar_stack extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_stack';
		parent::__construct();
	}

    /**
     * @param $v
     */
    public function append_stack( $v )
	{
		$this->append_value( $v );
	}
	
	// an array of HEX colours strings
	// e.g. array( '#ff0000', '#00ff00' );
    /**
     * @param $colours
     */
    public function set_colours( $colours )
	{
		$this->colours = $colours;
	}
	
	// an array of bar_stack_value

    /**
     * @param $keys
     */
    public function set_keys( $keys )
	{
		$this->keys = $keys;
	}
}

/**
 * Class bar_stack_value
 */
class bar_stack_value
{
    /**
     * bar_stack_value constructor.
     *
     * @param $val
     * @param $colour
     */
    public function __construct( $val, $colour )
	{
		$this->val = $val;
		$this->colour = $colour;
	}

    /**
     * @param $tip
     */
    public function set_tooltip( $tip )
	{
		$this->tip = $tip;
	} 
}

/**
 * Class bar_stack_key
 */
class bar_stack_key
{
    /**
     * bar_stack_key constructor.
     *
     * @param $colour
     * @param $text
     * @param $font_size
     */
    public function __construct( $colour, $text, $font_size )
	{
		$this->colour = $colour;
		$this->text = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}
}