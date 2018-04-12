<?php

/**
 * inherits from line
 */
class area extends line
{
	public function __construct()
	{
		$this->type      = 'area';
	}

    /**
     * the fill colour
     *
     * @param $colour
     */
	public function set_fill_colour( $colour )
	{
		$this->fill = $colour;
  }

    /**
     * sugar: see set_fill_colour
     * @param $colour
     * @return \area
     */
	public function fill_colour( $colour )
	{
		$this->set_fill_colour( $colour );
		return $this;
	}

    /**
     * @param $alpha
     */
    public function set_fill_alpha( $alpha )
	{
		$tmp = 'fill-alpha';
		$this->$tmp = $alpha;
	}
	
	public function set_loop()
	{
		$this->loop = true;
	}
}
