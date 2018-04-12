<?php

class scatter_value
{
	public function __construct( $x, $y, $dot_size=-1 )
	{
		$this->x = $x;
		$this->y = $y;
		
		if( $dot_size > 0 )
		{
			$tmp = 'dot-size';
			$this->$tmp = $dot_size;
		}
	}
}

class scatter
{
	public function __construct( $colour )
	{
		$this->type      = "scatter";
		$this->set_colour( $colour );
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

	public function set_default_dot_style( $style )
	{
		$tmp = 'dot-style';
		$this->$tmp = $style;	
	}
	
	/**
	 * @param $v as array, can contain any combination of:
	 *  - integer, Y position of the point
	 *  - any class that inherits from scatter_value
	 *  - <b>null</b>
	 */
	public function set_values( $values )
	{
		$this->values = $values;
	}
}
