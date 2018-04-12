<?php

/**
 * Class legend
 */
class legend
{
	public function __construct(){}

    /**
     * @param $position
     */
    public function set_position( $position )
	{
		$this->position = $position;
	}

    /**
     * @param $visible
     */
    public function set_visible( $visible )
	{
		$this->visible = $visible;
	}

    /**
     * @param $shadow
     */
    public function set_shadow( $shadow )
	{
		$this->shadow = $shadow;
	}

    /**
     * @param $padding
     */
    public function set_padding( $padding )
	{
		$this->padding = $padding;
	}

    /**
     * @param $border
     */
    public function set_border( $border )
	{
		$this->border = $border;
	}

    /**
     * @param $stroke
     */
    public function set_stroke( $stroke )
	{
		$this->stroke = $stroke;
	}

    /**
     * @param $margin
     */
    public function set_margin( $margin )
	{
		$this->margin = $margin;
	}

    /**
     * @param $alpha
     */
    public function set_alpha( $alpha )
	{
		$this->alpha = $alpha;
	}

    /**
     * @param $border_colour
     */
    public function set_border_colour( $border_colour )
	{
		$tmp = 'border_colour';
		$this->$tmp = $border_colour;
	}

    /**
     * @param $bg_colour
     */
    public function set_bg_colour( $bg_colour )
	{
		$tmp = 'bg_colour';
		$this->$tmp = $bg_colour;
	}

}

