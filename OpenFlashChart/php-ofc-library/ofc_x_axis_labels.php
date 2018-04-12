<?php

class x_axis_labels
{
	public function __construct(){}
	
	/**
	 * @param $steps which labels are generated
	 */
	public function set_steps( $steps )
	{
		$this->steps = $steps;
	}
	
	/**
	 * @param $steps as integer which labels are visible
	 */
	public function visible_steps( $steps )
	{
		$this->{"visible-steps"} = $steps;
		return $this;
	}
	
	/**
	 *
	 * @param $labels as an array of [x_axis_label or string]
	 */
	public function set_labels( $labels )
	{
		$this->labels = $labels;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	/**
	 * font size in pixels
	 */
	public function set_size( $size )
	{
		$this->size = $size;
	}
	
	/**
	 * rotate labels
	 */
	public function set_vertical()
	{
		$this->rotate = 270;
	}
	
	/**
	 * @param @angle as real. The angle of the text.
	 */
	public function rotate( $angle )
	{
		$this->rotate = $angle;
	}
	
	/**
	 * @param $text as string. Replace and magic variables with actual x axis position.
	 */
	public function text( $text )
	{
		$this->text = $text;
	}
}