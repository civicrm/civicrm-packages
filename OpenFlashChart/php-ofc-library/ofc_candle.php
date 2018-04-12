<?php

include_once 'ofc_bar_base.php';

/**
 * Class candle_value
 */
class candle_value
{
    /**
     * @param $high
     * @param $open
     * @param $close
     * @param $low
     */
	public function __construct( $high, $open, $close, $low )
	{
		$this->high = $high;
		$this->top = $open;
		$this->bottom = $close;
		$this->low = $low;
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
 * Class candle
 */
class candle extends bar_base
{
    /**
     * candle constructor.
     *
     * @param      $colour
     * @param null $negative_colour
     */
    public function __construct($colour, $negative_colour=null)
	{
		$this->type      = "candle";
		parent::__construct();
		
		$this->set_colour( $colour );
		if(null !== $negative_colour)
			$this->{'negative-colour'} = $negative_colour;
	}
}

