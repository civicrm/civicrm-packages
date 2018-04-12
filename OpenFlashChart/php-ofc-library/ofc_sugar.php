<?php

/**
 * Sugar: to make stars easier sometimes
 */
class s_star extends star
{
    /**
     * I use this wrapper for default dot types,
     * it just makes the code easier to read.
     *
     * @param $colour
     * @param $size
     */
	public function __construct($colour, $size)
	{
		parent::__construct();
		$this->colour($colour)->size($size);
	}
}

/**
 * Class s_box
 */
class s_box extends anchor
{
    /**
     * I use this wrapper for default dot types,
     * it just makes the code easier to read.
     * @param $colour
     * @param $size
     */
	public function __construct($colour, $size)
	{
		parent::__construct();
		$this->colour($colour)->size($size)->rotation(45)->sides(4);
	}
}

/**
 * Class s_hollow_dot
 */
class s_hollow_dot extends hollow_dot
{
    /**
     * I use this wrapper for default dot types,
     * it just makes the code easier to read.
     * @param $colour
     * @param $size
     */
	public function __construct($colour, $size)
	{
		parent::__construct();
		$this->colour($colour)->size($size);
	}
}