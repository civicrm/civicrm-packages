<?php

include_once 'ofc_bar_base.php';

class candle_value
{
	/**
	 *
	 */
	public function __construct( $high, $open, $close, $low )
	{
		$this->high = $high;
		$this->top = $open;
		$this->bottom = $close;
		$this->low = $low;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
}

class candle extends bar_base
{
	public function __construct($colour, $negative_colour=null)
	{
		$this->type      = "candle";
		parent::__construct();
		
		$this->set_colour( $colour );
		if(null !== $negative_colour)
			$this->{'negative-colour'} = $negative_colour;
	}
}

