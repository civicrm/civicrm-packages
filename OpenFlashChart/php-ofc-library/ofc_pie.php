<?php

class pie_value
{
	public function __construct( $value, $label )
	{
		$this->value = $value;
		$this->label = $label;
	}
	
		    /**
     * Sets the text for the line.
     *
     * @param string $text
     */   
    public function set_text($text)
    {
        $this->text = $text;
    }
	
	public function set_key_on_click( $action )
	{
		$tmp = 'key-on-click';
		$this->$tmp = $action;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	public function set_label( $label, $label_colour, $font_size )
	{
		$this->label = $label;
		
		$tmp = 'label-colour';
		$this->$tmp = $label_colour;
		
		$tmp = 'font-size';
		$this->$tmp = $font_size;
		
	}
	
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
	
	public function on_click( $event )
	{
		$tmp = 'on-click';
		$this->$tmp = $event;
	}
	
	
	/**
	 * An object that inherits from base_pie_animation
	 */
	public function add_animation( $animation )
	{
		if( !isset( $this->animate ) )
			$this->animate = array();
			
		$this->animate[] = $animation;
		
		return $this;
	}
}

class base_pie_animation{}

/**
 * fade the pie slice from $alpha (pie set_alpha) to 100% opaque.
 */
class pie_fade extends base_pie_animation
{
	public function __construct()
	{
		$this->type="fade";
	}
}

/**
 * Bounce the pie slice out a little
 */
class pie_bounce extends base_pie_animation
{
	/**
	 * @param $distance as integer, distance to bounce in pixels
	 */
	public function __construct( $distance )
	{
		$this->type="bounce";
		$this->distance = $distance;
	}
}

/**
 * Make a pie chart and fill it with pie slices
 */
class pie
{
	public function __construct()
	{
		$this->type      		= 'pie';
	}
	
	public function set_colours( $colours )
	{
		$this->colours = $colours;
	}
	
	/**
	 * Sugar wrapped around set_colours
	 */
	public function colours( $colours )
	{
		$this->set_colours( $colours );
		return $this;
	}
	
	/**
	 * @param $alpha as float (0-1) 0.75 = 3/4 visible
	 */
	public function set_alpha( $alpha )
	{
		$this->alpha = $alpha;
	}
	
	/**
	 *sugar wrapped set_alpha
	 **/
	public function alpha( $alpha )
	{
		$this->set_alpha( $alpha );
		return $this;
	}
	
	/**
	 * @param $v as array containing one of
	 *  - null
	 *  - real or integer number
	 *  - a pie_value object
	 */
	public function set_values( $v )
	{
		$this->values = $v;		
	}

	/**
	 * sugar for set_values
	 */
	public function values( $v )
	{
		$this->set_values( $v );
		return $this;
	}
	
	/**
	 * HACK to keep old code working.
	 */
	public function set_animate( $bool )
	{
		if( $bool )
			$this->add_animation( new pie_fade() );
			
	}
	
	/**
	 * An object that inherits from base_pie_animation
	 */
	public function add_animation( $animation )
	{
		if( !isset( $this->animate ) )
			$this->animate = array();
			
		$this->animate[] = $animation;
		
		return $this;
	}
	
	/**
	 * @param $angle as real number
	 */
	public function set_start_angle( $angle )
	{
		$tmp = 'start-angle';
		$this->$tmp = $angle;
	}
	
	/**
	 * sugar for set_start_angle
	 */
	public function start_angle($angle)
	{
		$this->set_start_angle( $angle );
		return $this;
	}
	
	/**
	 * @param $tip as string. The tooltip text. May contain magic varibles
	 */
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
	
	/**
	 * sugar for set_tooltip
	 */
	public function tooltip( $tip )
	{
		$this->set_tooltip( $tip );
		return $this;
	}
	
	public function set_gradient_fill()
	{
		$tmp = 'gradient-fill';
		$this->$tmp = true;
	}
	
	public function gradient_fill()
	{
		$this->set_gradient_fill();
		return $this;
	}
	
	/**
	 * By default each label is the same colour as the slice,
	 * but you can ovveride that behaviour using this method.
	 * 
	 * @param $label_colour as string HEX colour;
	 */
	public function set_label_colour( $label_colour )
	{
		$tmp = 'label-colour';
		$this->$tmp = $label_colour;	
	}
	
	public function label_colour( $label_colour )
	{
		$this->set_label_colour( $label_colour );
		return $this;
	}
	
	/**
	 * Turn off the labels
	 */
	public function set_no_labels()
	{
		$tmp = 'no-labels';
		$this->$tmp = true;
	}
	
	public function on_click( $event )
	{
		$tmp = 'on-click';
		$this->$tmp = $event;
	}
	
	/**
	 * Fix the radius of the pie chart. Take a look at the magic variable #radius#
	 * for helping figure out what radius to set it to.
	 * 
	 * @param $radius as number
	 */
	public function radius( $radius )
	{
		$this->radius = $radius;
		return $this;
	}
}
