<?php

include_once 'ofc_bar_base.php';

class tooltip
{
	public function __construct(){}
	
	/**
	 * @param $shadow as boolean. Enable drop shadow.
	 */
	public function set_shadow( $shadow )
	{
		$this->shadow = $shadow;
	}
	
	/**
	 * @param $stroke as integer, border width in pixels (e.g. 5 )
	 */
	public function set_stroke( $stroke )
	{
		$this->stroke = $stroke;
	}
	
	/**
	 * @param $clash as bolean
	 */
	public function set_clash( $clash )
	{
		$this->clash = $clash;
	}
	
	/**
	 * @param $colour as string, HEX colour e.g. '#0000ff'
	 */
	public function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	/**
	 * @param $bg as string, HEX colour e.g. '#0000ff'
	 */
	public function set_background_colour( $bg )
	{
		$this->background = $bg;
	}
	
	/**
	 * @param $style as string. A css style.
	 */
	public function set_title_style( $style )
	{
		$this->title = $style;
	}
	
	/**
	 * @param $style as string. A css style.
	 */
    public function set_body_style( $style )
	{
		$this->body = $style;
	}
	
	public function set_proximity()
	{
		$this->mouse = 1;
	}
	
	public function set_hover()
	{
		$this->mouse = 2;
	}
}

