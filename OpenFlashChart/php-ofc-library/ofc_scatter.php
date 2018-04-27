<?php

/**
 * Class scatter_value
 */
class scatter_value
{
    /**
     * scatter_value constructor.
     *
     * @param     $x
     * @param     $y
     * @param int $dot_size
     */
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

/**
 * Class scatter
 */
class scatter
{
    /**
     * scatter constructor.
     *
     * @param $colour
     */
    public function __construct( $colour )
	{
		$this->type      = "scatter";
		$this->set_colour( $colour );
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

    /**
     * @param $style
     */
    public function set_default_dot_style( $style )
	{
		$tmp = 'dot-style';
		$this->$tmp = $style;	
	}

    /**
     * @param $values
     */
	public function set_values( $values )
	{
		$this->values = $values;
	}
}
