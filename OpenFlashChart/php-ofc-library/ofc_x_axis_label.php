<?php

/**
 * x_axis_label see x_axis_labels
 */
class x_axis_label
{
	public function __construct( $text, $colour, $size, $rotate )
	{
		$this->set_text( $text );
		$this->set_colour( $colour );
		$this->set_size( $size );
		$this->set_rotate( $rotate );
	}
	
	public function set_text( $text )
	{
		$this->text = $text;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	public function set_size( $size )
	{
		$this->size = $size;
	}
	
	public function set_rotate( $rotate )
	{
		$this->rotate = $rotate;
	}
	
	public function set_vertical()
	{
		$this->rotate = "vertical";
	}
	
	public function set_visible()
	{
		$this->visible = true;
	}
}