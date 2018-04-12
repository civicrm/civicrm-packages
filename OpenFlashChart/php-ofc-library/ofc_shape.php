<?php

/**
 * Class shape_point
 */
class shape_point
{
    /**
     * shape_point constructor.
     *
     * @param $x
     * @param $y
     */
    public function __construct( $x, $y )
	{
		$this->x = $x;
		$this->y = $y;
	}
}

/**
 * Class shape
 */
class shape
{
    /**
     * shape constructor.
     *
     * @param $colour
     */
    public function __construct( $colour )
	{
		$this->type		= "shape";
		$this->colour	= $colour;
		$this->values	= array();
	}

    /**
     * @param $p
     */
    public function append_value( $p )
	{
		$this->values[] = $p;	
	}
}