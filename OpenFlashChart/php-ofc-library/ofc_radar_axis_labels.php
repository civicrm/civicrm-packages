<?php

/**
 * Class radar_axis_labels
 */
class radar_axis_labels
{
	// $labels : array
    /**
     * radar_axis_labels constructor.
     *
     * @param $labels
     */
    public function __construct( $labels )
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
}