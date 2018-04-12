<?php

class hbar_value
{
	public function __construct( $left, $right=null )
	{
		if( isset( $right ) )
		{
			$this->left = $left;
			$this->right = $right;
		}
		else
			$this->right = $left;
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;	
	}
	
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;	
	}
		
	public function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
}

class hbar
{
	public function __construct( $colour )
	{
		$this->type      = "hbar";
		$this->values    = array();
		$this->set_colour( $colour );
	}
	
	public function append_value( $v )
	{
		$this->values[] = $v;		
	}
	
	public function set_values( $v )
	{
		foreach( $v as $val )
			$this->append_value( new hbar_value( $val ) );
	}
	
	public function set_colour( $colour )
	{
		$this->colour = $colour;	
	}
		
	public function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
	public function set_key( $text, $size )
	{
		$this->text = $text;
		$tmp = 'font-size';
		$this->$tmp = $size;
	}
	
	public function set_tooltip( $tip )
	{
		$this->tip = $tip;	
	}
}

