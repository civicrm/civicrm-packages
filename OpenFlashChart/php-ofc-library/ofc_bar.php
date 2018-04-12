<?php

include_once 'ofc_bar_base.php';

/**
 * Class bar_value
 */
class bar_value
{
    /**
     * bar_value constructor.
     *
     * @param      $top
     * @param null $bottom
     */
    public function __construct( $top, $bottom=null )
	{
		$this->top = $top;
		
		if( isset( $bottom ) ) {
        $this->bottom = $bottom;
    }
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

/**
 * Class bar
 */
class bar extends bar_base
{
	public function __construct()
	{
		$this->type      = "bar";
		parent::__construct();
	}
}

