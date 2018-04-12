<?php

include_once 'ofc_bar_base.php';

/**
 * Class bar_on_show
 */
class bar_on_show
{
	/**
	 *@param $type as string. Can be any one of:
	 * - 'pop-up'
	 * - 'drop'
	 * - 'fade-in'
	 * - 'grow-up'
	 * - 'grow-down'
	 * - 'pop'
	 *
	 * @param $cascade as float. Cascade in seconds
	 * @param $delay as float. Delay before animation starts in seconds.
	 */
	public function __construct($type, $cascade, $delay)
	{
		$this->type = $type;
		$this->cascade = (float)$cascade;
		$this->delay = (float)$delay;
	}
}

/**
 * Class bar_value
 */
class bar_value
{
	/**
	 * @param $top as integer. The Y value of the top of the bar
	 * @param OPTIONAL $bottom as integer. The Y value of the bottom of the bar, defaults to Y min.
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
		$this->type      = 'bar';
		parent::__construct();
	}
}

/**
 * Class bar_glass
 */
class bar_glass extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_glass';
		parent::__construct();
	}
}

/**
 * Class bar_cylinder
 */
class bar_cylinder extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_cylinder';
		parent::__construct();
	}
}

/**
 * Class bar_cylinder_outline
 */
class bar_cylinder_outline extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_cylinder_outline';
		parent::__construct();
	}
}

/**
 * Class bar_rounded_glass
 */
class bar_rounded_glass extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_round_glass';
		parent::__construct();
	}
}

/**
 * Class bar_round
 */
class bar_round extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_round';
		parent::__construct();
	}
}

/**
 * Class bar_dome
 */
class bar_dome extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_dome';
		parent::__construct();
	}
}

/**
 * Class bar_round3d
 */
class bar_round3d extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_round3d';
		parent::__construct();
	}
}

/**
 * Class bar_3d
 */
class bar_3d extends bar_base
{
	public function __construct()
	{
		$this->type      = 'bar_3d';
		parent::__construct();
	}
}