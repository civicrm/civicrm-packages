<?php

/**
 * Class radar_axis
 */
class radar_axis
{
    /**
     * radar_axis constructor.
     *
     * @param $max
     */
    public function __construct( $max )
	{
		$this->set_max( $max );
	}

    /**
     * @param $max
     */
    public function set_max( $max )
	{
		$this->max = $max;
	}

    /**
     * @param $steps
     */
    public function set_steps( $steps )
	{
		$this->steps = $steps;
	}

    /**
     * @param $s
     */
    public function set_stroke( $s )
	{
		$this->stroke = $s;
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
     * @param $labels
     */
    public function set_labels( $labels )
	{
		$this->labels = $labels;
	}

    /**
     * @param $labels
     */
    public function set_spoke_labels( $labels )
	{
		$tmp = 'spoke-labels';
		$this->$tmp = $labels;
	}
}

