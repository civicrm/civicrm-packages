<?php

/**
 * Class dot_value
 */
class dot_value
{
    /**
     * dot_value constructor.
     *
     * @param $value
     * @param $colour
     */
    public function __construct( $value, $colour )
	{
		$this->value = $value;
		$this->colour = $colour;
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

    /**
     * @param $size
     */
    public function set_size( $size )
	{
		$this->size = $size;
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
 * Class line_dot
 */
class line_dot extends line_base
{
	public function __construct()
	{
		$this->type      = 'line_dot';
	}
}