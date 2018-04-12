<?php


class legend
{
	public function __construct(){}
	
	public function set_position( $position )
	{
		$this->position = $position;
	}	

	public function set_visible( $visible )
	{
		$this->visible = $visible;
	}

	public function set_shadow( $shadow )
	{
		$this->shadow = $shadow;
	}
	
	public function set_padding( $padding )
	{
		$this->padding = $padding;
	}
	
	public function set_border( $border )
	{
		$this->border = $border;
	}
	
	public function set_stroke( $stroke )
	{
		$this->stroke = $stroke;
	}
	
	public function set_margin( $margin )
	{
		$this->margin = $margin;
	}
	
	public function set_alpha( $alpha )
	{
		$this->alpha = $alpha;
	}	
	
	public function set_border_colour( $border_colour )
	{
		$tmp = "border_colour";
		$this->$tmp = $border_colour;
	}
	
	public function set_bg_colour( $bg_colour )
	{
		$tmp = "bg_colour";
		$this->$tmp = $bg_colour;
	}

}

