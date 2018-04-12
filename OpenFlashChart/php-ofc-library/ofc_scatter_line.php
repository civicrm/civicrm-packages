<?php

/**
 * Class scatter_line
 */
class scatter_line
{
    /**
     * scatter_line constructor.
     *
     * @param $colour
     * @param $width
     */
    public function __construct( $colour, $width  )
	{
		$this->type      = 'scatter_line';
		$this->set_colour( $colour );
		$this->set_width( $width );
	}

    /**
     * @param $style
     */
    public function set_default_dot_style( $style )
	{
		$tmp = 'dot-style';
		$this->$tmp = $style;	
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

    /**
     * @param $width
     */
    public function set_width( $width )
	{
		$this->width = $width;
	}

    /**
     * @param $values
     */
    public function set_values( $values )
	{
		$this->values = $values;
	}
	
	public function set_step_horizontal()
	{
		$this->stepgraph = 'horizontal';
	}
	
	public function set_step_vertical()
	{
		$this->stepgraph = 'vertical';
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
}