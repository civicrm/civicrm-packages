<?php

include_once 'ofc_bar_base.php';

/**
 * Class bar_3d_value
 */
class bar_3d_value
{
    /**
     * bar_3d_value constructor.
     *
     * @param $top
     */
    public function __construct( $top )
	{
		$this->top = $top;
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
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

