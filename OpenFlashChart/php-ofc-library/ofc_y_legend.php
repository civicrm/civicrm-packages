<?php

/**
 * Class y_legend
 */
class y_legend
{
    /**
     * y_legend constructor.
     *
     * @param string $text
     */
    public function __construct( $text='' )
	{
		$this->text = $text;
	}

    /**
     * @param $css
     */
    public function set_style( $css )
	{
		$this->style = $css;
		//"{font-size: 20px; color:#0000ff; font-family: Verdana; text-align: center;}";		
	}
}