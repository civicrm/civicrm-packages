<?php

/**
 * Class line_base
 */
class line_base
{
	public function __construct()
	{
		$this->type      = "line";
		$this->text      = "Page views";
		$tmp = 'font-size';
		$this->$tmp = 10;
		
		$this->values    = array();
	}

    /**
     * @param $v
     */
    public function set_values( $v )
	{
		$this->values = $v;		
	}
	
	/**
     * Append a value to the line.
     *
     * @param mixed $v
     */
    public function append_value($v)
    {
        $this->values[] = $v;       
    }

    /**
     * @param $width
     */
    public function set_width( $width )
	{
		$this->width = $width;		
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

    /**
     * @param $size
     */
    public function set_dot_size( $size )
	{
		$tmp = 'dot-size';
		$this->$tmp = $size;		
	}

    /**
     * @param $size
     */
    public function set_halo_size( $size )
	{
		$tmp = 'halo-size';
		$this->$tmp = $size;		
	}

    /**
     * @param $text
     * @param $font_size
     */
    public function set_key( $text, $font_size )
	{
		$this->text      = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}

    /**
     * @param $tip
     */
    public function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}

    /**
     * @param $text
     */
    public function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
	
	public function loop()
	{
		$this->loop = true;
	}

    /**
     * @param $s
     */
    public function line_style( $s )
	{
		$tmp = "line-style";
		$this->$tmp = $s;
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
	
	
}