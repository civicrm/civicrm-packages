<?php

/* this is a base class */

/**
 * Class bar_base
 */
class bar_base
{
	public function __construct(){}

	/**
	 * @param $text as string the key text
	 * @param $size as integer, size in pixels
	 */
	public function set_key( $text, $size )
	{
		$this->text = $text;
		$tmp = 'font-size';
		$this->$tmp = $size;
	}

    /**
     * syntatical sugar.
     *
     * @param $text
     * @param $size
     */
	public function key( $text, $size )
	{
		$this->set_key( $text, $size );
	}

	/**
	 * @param $v as an array, a mix of:
	 * 	- a bar_value class. You can use this to customise the paramters of each bar.
	 * 	- integer. This is the Y position of the top of the bar.
	 */
	public function set_values( $v )
	{
		$this->values = $v;		
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

    /**
     * @param $action
     */
    public function set_key_on_click( $action )
	{
		$tmp = 'key-on-click';
		$this->$tmp = $action;
	}

    /**
     * @param $id
     */
    public function set_group_id( $id )
	{
		$this->id = $id;
  }

    /**
     * see set_values
     * @param $v
     */
	public function append_value( $v )
	{
		$this->values[] = $v;		
	}
	
	/**
	 * @param $colour as string, a HEX colour, e.g. '#ff0000' red
	 */
	public function set_colour( $colour )
	{
		$this->colour = $colour;
  }

    /**
     *syntatical sugar
     * @param $colour
     */
	public function colour( $colour )
	{
		$this->set_colour( $colour );
	}

	/**
	 * @param $alpha as real number (range 0 to 1), e.g. 0.5 is half transparent
	 */
	public function set_alpha( $alpha )
	{
		$this->alpha = $alpha;	
	}
	
	/**
	 * @param $tip as string, the tip to show. May contain various magic variables.
	 */
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;	
	}
	
	/**
	 *@param $on_show as line_on_show object
	 */
	public function set_on_show($on_show)
	{
		$this->{'on-show'} = $on_show;
	}

    /**
     * @param $text
     */
    public function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
	
	public function attach_to_right_y_axis()
	{
		$this->axis = 'right';
	}
}

