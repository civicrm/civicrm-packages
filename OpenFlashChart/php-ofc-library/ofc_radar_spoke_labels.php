<?php

/**
 * Class radar_spoke_labels
 */
class radar_spoke_labels
{
	// $labels : array
    /**
     * radar_spoke_labels constructor.
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