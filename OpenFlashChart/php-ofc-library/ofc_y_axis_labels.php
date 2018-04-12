<?php

/**
 * Class y_axis_labels
 */
class y_axis_labels
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
	 *
	 * @param $labels as an array of [y_axis_label or string]
	 */
	public function set_labels( $labels )
	{
		$this->labels = $labels;
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

    /**
     * font size in pixels
     *
     * @param $size
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
     * @param $angle
     */
    public function rotate( $angle )
	{
		$this->rotate = $angle;
	}
	
	/**
	 * @param $text default text that all labels inherit
	 */
	public function set_text( $text )
	{
		$this->text = $text;
	}
}