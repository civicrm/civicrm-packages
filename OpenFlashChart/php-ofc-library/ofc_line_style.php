<?php

/**
 * Class line_style
 */
class line_style
{
    /**
     * line_style constructor.
     *
     * @param $on
     * @param $off
     */
    public function __construct($on, $off)
	{
		$this->style	= "dash";
		$this->on		= $on;
		$this->off		= $off;
	}
}