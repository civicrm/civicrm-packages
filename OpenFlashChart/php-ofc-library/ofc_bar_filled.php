<?php

include_once 'ofc_bar_base.php';

/**
 * Class bar_filled_value
 */
class bar_filled_value extends bar_value
{
    /**
     * bar_filled_value constructor.
     *
     * @param      $top
     * @param null $bottom
     */
    public function __construct( $top, $bottom=null )
	{
		parent::__construct( $top, $bottom );	
	}

    /**
     * @param $outline_colour
     */
    public function set_outline_colour( $outline_colour )
	{
		$tmp = 'outline-colour';
		$this->$tmp = $outline_colour;	
	}
}

/**
 * Class bar_filled
 */
class bar_filled extends bar_base
{
    /**
     * bar_filled constructor.
     *
     * @param null $colour
     * @param null $outline_colour
     */
    public function __construct( $colour=null, $outline_colour=null )
	{
		$this->type      = "bar_filled";
		parent::__construct();
		
		if( isset( $colour ) )
			$this->set_colour( $colour );
		
		if( isset( $outline_colour ) )
			$this->set_outline_colour( $outline_colour );
	}

    /**
     * @param $outline_colour
     */
    public function set_outline_colour( $outline_colour )
	{
		$tmp = 'outline-colour';
		$this->$tmp = $outline_colour;	
	}
}

