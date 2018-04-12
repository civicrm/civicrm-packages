<?php

/**
 * Class hbar_value
 */
class hbar_value
{
    /**
     * hbar_value constructor.
     *
     * @param      $left
     * @param null $right
     */
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

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;	
	}

    /**
     * @param $tip
     */
    public function set_tooltip( $tip )
	{
		$this->tip = $tip;	
	}

    /**
     * @param $text
     */
    public function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
}

/**
 * Class hbar
 */
class hbar
{
    /**
     * hbar constructor.
     *
     * @param $colour
     */
    public function __construct( $colour )
	{
		$this->type      = "hbar";
		$this->values    = array();
		$this->set_colour( $colour );
	}

    /**
     * @param $v
     */
    public function append_value( $v )
	{
		$this->values[] = $v;		
	}

    /**
     * @param $v
     */
    public function set_values( $v )
	{
		foreach( $v as $val )
			$this->append_value( new hbar_value( $val ) );
	}

    /**
     * @param $colour
     */
    public function set_colour( $colour )
	{
		$this->colour = $colour;	
	}

    /**
     * @param $text
     */
    public function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}

    /**
     * @param $text
     * @param $size
     */
    public function set_key( $text, $size )
	{
		$this->text = $text;
		$tmp = 'font-size';
		$this->$tmp = $size;
	}

    /**
     * @param $tip
     */
    public function set_tooltip( $tip )
	{
		$this->tip = $tip;	
	}
}

