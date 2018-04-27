<?php

/**
 * y_axis_label see y_axis_labels
 */
class y_axis_label
{
    /**
     * y_axis_label constructor.
     *
     * @param $y
     * @param $text
     */
    public function __construct( $y, $text)
	{
		$this->y = $y;
		$this->set_text( $text );
	}

    /**
     * @param $text
     */
    public function set_text( $text )
	{
		$this->text = $text;
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
    public function set_size( $size )
	{
		$this->size = $size;
	}

    /**
     * @param $rotate
     */
    public function set_rotate( $rotate )
	{
		$this->rotate = $rotate;
	}
	
	public function set_vertical()
	{
		$this->rotate = "vertical";
	}
}