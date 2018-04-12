<?php

/**
 * Class y_axis_base
 */
class y_axis_base
{
	public function __construct(){}
	
	/**
	 * @param $s as integer, thickness of the Y axis line
	 */
	public function set_stroke( $s )
	{
		$this->stroke = $s;
	}
	
	/**
	 * @param $val as integer. The length of the ticks in pixels
	 */
	public function set_tick_length( $val )
	{
		$tmp = 'tick-length';
		$this->$tmp = $val;
	}

    /**
     * @param $colour
     * @param $grid_colour
     */
    public function set_colours( $colour, $grid_colour )
	{
		$this->set_colour( $colour );
		$this->set_grid_colour( $grid_colour );
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;
	}

    /**
     * @param $colour
     */
    public function set_grid_colour( $colour )
	{
		$tmp = 'grid-colour';
		$this->$tmp = $colour;
	}

    /**
     * Set min and max values, also (optionally) set the steps value.
     * You can reverse the chart by setting min larger than max, e.g. min = 10
     * and max = 0.
     *
     * @param     $min   as integer
     * @param     $max   as integer
     * @param int $steps as integer.
     */
	public function set_range( $min, $max, $steps=1 )
	{
		$this->min = $min;
		$this->max = $max;
		$this->set_steps( $steps );
	}

    /**
     * Sugar for set_range
     *
     * @param     $min
     * @param     $max
     * @param int $steps
     *
     * @return \y_axis_base
     */
	public function range( $min, $max, $steps=1 )
	{
		$this->set_range( $min, $max, $steps );
		return $this;
	}
	
	/**
	 * @param $off as Boolean. If true the Y axis is nudged up half a step.
	 */
	public function set_offset( $off )
	{
		$this->offset = $off?1:0;
	}
	
	/**
	 * @param $y_axis_labels as an y_axis_labels object
	 * Use this to customize the labels (colour, font, etc...)
	 */
	public function set_labels( $y_axis_labels )
	{
		$this->labels = $y_axis_labels;
	}
	
	/**
	 * Pass in some text for each label. This can contain magic variables "#val#" which
	 * will get replaced with the value for that Y axis label. Useful for:
	 * - "�#val#"
	 * - "#val#%"
	 * - "#val# million"
	 * 
	 * @param $text as string.
	 */
	public function set_label_text( $text )
	{
		$tmp = new y_axis_labels();
		$tmp->set_text( $text );
		$this->labels = $tmp;
	}
	
	/**
	 * @param $steps as integer.
	 *
	 * Only show every $steps label, e.g. every 10th
	 */
	public function set_steps( $steps )
	{
		$this->steps = $steps;	
	}
	
	/**
	 * Make the labels show vertical
	 */
	public function set_vertical()
	{
		$this->rotate = 'vertical';
	}

    /**
     * @param $logScale
     */
    public function set_logScale( $logScale)
	{
		$tmp = 'log-scale';
		$this->$tmp = $logScale;
	}
}