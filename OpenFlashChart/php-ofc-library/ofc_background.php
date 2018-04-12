<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
/**
 * Set the title of a chart, make one of these and pass it into
 * open_flash_chart set_title
 */
class inner_bg_grad
{
	public function __construct()
	{
		$this->alpha    = array(1,1);
		$this->ratio    = array(0,255);
		$this->angle    = 90;
		$this->fillType    = 'linear';
	}
	
	public function set_fillType( $text='linear' )
	{
		$this->fillType = $text;
	}

	public function set_colour1( $text='' )
	{
		$this->colour1 = $text;
	}
	
	public function set_colour2( $text='' )
	{
		$this->colour2 = $text;
	}

	public function set_alpha( $text=array(1,1) )
	{
		$this->alpha = $text;
	}
	
	public function set_ratio( $text=array(0,255) )
	{
		$this->ratio = $text;
	}	
	
	public function set_angle( $text='0' )
	{
		$this->angle = $text;
	}

	
}
