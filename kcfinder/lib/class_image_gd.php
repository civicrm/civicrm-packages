<?php

/** This file is part of KCFinder project
  *
  *      @desc GD image driver class
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  */

namespace kcfinder;

/**
 * Class image_gd
 *
 * @package kcfinder
 */
class image_gd extends image {


    // ABSTRACT PUBLIC METHODS

    /**
     * @param int $width
     * @param int $height
     *
     * @return bool
     */
    public function resize($width, $height) {
        if (!$width) {
            $width = 1;
        }
        if (!$height) {
            $height = 1;
        }
        return (
          (false !== ($img = new self(array($width, $height)))) &&
          $img->imageCopyResampled($this) &&
            (false !== ($this->image = $img->image)) &&
            (false !== ($this->width = $img->width)) &&
            (false !== ($this->height = $img->height))
        );
    }

    /**
     * @param int  $width
     * @param int  $height
     * @param bool $background
     *
     * @return bool
     */
    public function resizeFit($width, $height, $background=false) {
        if ((!$width && !$height) || (($width == $this->width) && ($height == $this->height))) {
            return true;
        }
        if (!$width || (($height / $width) < ($this->height / $this->width))) {
            $h = $height;
            $w = round(($this->width * $h) / $this->height);
        } elseif (!$height || (($width / $height) < ($this->width / $this->height))) {
            $w = $width;
            $h = round(($this->height * $w) / $this->width);
        } else {
            $w = $width;
            $h = $height;
        }
        if (!$w) {
            $w = 1;
        }
        if (!$h) {
            $h = 1;
        }

        if ($background === false) {
            return $this->resize($w, $h);
        }

        else {
            $img = new self(array($width, $height));
            $x = round(($width - $w) / 2);
            $y = round(($height - $h) / 2);

            if ((false === $this->resize($w, $h)) ||
                (false === $img->imageFilledRectangle(0, 0, $width, $height, $background)) ||
                (false === $img->imageCopyResampled($this->image, $x, $y, 0, 0, $w, $h))
            ) {
                return false;
            }

            $this->image = $img->image;
            $this->width = $width;
            $this->height = $height;

            return true;
        }
    }

    /**
     * @param      $width
     * @param      $height
     * @param bool $offset
     *
     * @return bool
     */
    public function resizeCrop($width, $height, $offset=false) {

        if (($this->width / $this->height) > ($width / $height)) {
            $h = $height;
            $w = ($this->width * $h) / $this->height;
            $y = 0;
            if ($offset !== false) {
                if ($offset > 0) {
                    $offset = -$offset;
                }
                if (($w + $offset) <= $width) {
                    $offset = $width - $w;
                }
                $x = $offset;
            } else {
                $x = ($width - $w) / 2;
            }

        } else {
            $w = $width;
            $h = ($this->height * $w) / $this->width;
            $x = 0;
            if ($offset !== false) {
                if ($offset > 0) {
                    $offset = -$offset;
                }
                if (($h + $offset) <= $height) {
                    $offset = $height - $h;
                }
                $y = $offset;
            } else {
                $y = ($height - $h) / 2;
            }
        }

        $x = round($x);
        $y = round($y);
        $w = round($w);
        $h = round($h);
        if (!$w) {
            $w = 1;
        }
        if (!$h) {
            $h = 1;
        }

        $return = (
            (false !== ($img = new self(array($width, $height))))) &&
                  (false !== ($img->imageCopyResampled($this->image, $x, $y, 0, 0, $w, $h))
        );

        if ($return) {
            $this->image = $img->image;
            $this->width = $w;
            $this->height = $h;
        }

        return $return;
    }

    /**
     * @param int    $angle
     * @param string $background
     *
     * @return bool
     */
    public function rotate($angle, $background="#000000") {
        $angle = -$angle;
        $img = @imagerotate($this->image, $angle, $this->gdColor($background));
        if ($img === false) {
            return false;
        }
        $this->width = imagesx($img);
        $this->height = imagesy($img);
        $this->image = $img;
        return true;
    }

    /**
     * @return bool
     */
    public function flipHorizontal() {
        $img = imagecreatetruecolor($this->width, $this->height);
        if (imagecopyresampled($img, $this->image, 0, 0, ($this->width - 1), 0, $this->width, $this->height, -$this->width, $this->height)) {
            $this->image = $img;
        }
        else {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function flipVertical() {
        $img = imagecreatetruecolor($this->width, $this->height);
        if (imagecopyresampled($img, $this->image, 0, 0, 0, ($this->height - 1), $this->width, $this->height, $this->width, -$this->height)) {
            $this->image = $img;
        }
        else {
            return false;
        }
        return true;
    }

    /**
     * @param string $file
     * @param bool   $left
     * @param bool   $top
     *
     * @return bool
     */
    public function watermark($file, $left=false, $top=false) {
        $info = getimagesize($file);
        list($w, $h, $t) = $info;
        if (!in_array($t, array(IMAGETYPE_PNG, IMAGETYPE_GIF))) {
            return false;
        }
        $imagecreate = ($t == IMAGETYPE_PNG) ? "imagecreatefrompng" : "imagecreatefromgif";

        if (!@imagealphablending($this->image, true) ||
            (false === ($wm = @$imagecreate($file)))
        ) {
            return false;
        }

        $w = imagesx($wm);
        $h = imagesy($wm);
        $x =
            ($left === true) ? 0 : (
            ($left === null) ? round(($this->width - $w) / 2) : (
            (($left === false) || !preg_match('/^\d+$/', $left)) ? ($this->width - $w) : $left));
        $y =
            ($top === true) ? 0 : (
            ($top === null) ? round(($this->height - $h) / 2) : (
            (($top === false) || !preg_match('/^\d+$/', $top)) ? ($this->height - $h) : $top));

        if ((($x + $w) > $this->width) ||
            (($y + $h) > $this->height) ||
            ($x < 0) || ($y < 0)
        ) {
            return false;
        }

        if (($wm === false) || !@imagecopy($this->image, $wm, $x, $y, 0, 0, $w, $h)) {
            return false;
        }

        @imagealphablending($this->image, false);
        @imagesavealpha($this->image, true);
        return true;
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return bool
     */
    public function output($type='jpeg', array $options=array()) {
        $method = "output_$type";
        if (!method_exists($this, $method)) {
            return false;
        }
        return $this->$method($options);
    }


    // ABSTRACT PROTECTED METHODS

    /**
     * @param int $width
     * @param int $height
     *
     * @return mixed|resource
     */
    protected function getBlankImage($width, $height) {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        return $image;
    }

    /**
     * @param mixed $image
     * @param int   $width
     * @param int   $height
     *
     * @return bool|mixed|resource
     */
    protected function getImage($image, &$width, &$height) {

        if (is_resource($image) && (get_resource_type($image) == "gd")) {
            $width = @imagesx($image);
            $height = @imagesy($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
            return $image;

        } elseif (is_string($image) &&
            (false !== (list($width, $height, $t) = @getimagesize($image)))
        ) {
            $image =
                ($t == IMAGETYPE_GIF)  ? @imagecreatefromgif($image)  : (
                ($t == IMAGETYPE_WBMP) ? @imagecreatefromwbmp($image) : (
                ($t == IMAGETYPE_JPEG) ? @imagecreatefromjpeg($image) : (
                ($t == IMAGETYPE_PNG)  ? @imagecreatefrompng($image)  : (
                ($t == IMAGETYPE_XBM)  ? @imagecreatefromxbm($image)  : false
            ))));

            if ($t == IMAGETYPE_PNG) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
            return $image;

        } else {
            return false;
        }
    }


    // PSEUDO-ABSTRACT STATIC METHODS

    /**
     * @return bool
     */
    public static function available() {
        return function_exists("imagecreatefromjpeg");
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public static function checkImage($file) {
        if (!is_string($file) ||
            ((false === (list($width, $height, $t) = @getimagesize($file))))
        ) {
            return false;
        }

        $img =
            ($t == IMAGETYPE_GIF)  ? @imagecreatefromgif($file)  : (
            ($t == IMAGETYPE_WBMP) ? @imagecreatefromwbmp($file) : (
            ($t == IMAGETYPE_JPEG) ? @imagecreatefromjpeg($file) : (
            ($t == IMAGETYPE_PNG)  ? @imagecreatefrompng($file)  : (
            ($t == IMAGETYPE_XBM)  ? @imagecreatefromxbm($file)  : false
        ))));

        return ($img !== false);
    }


    // OWN METHODS

    /**
     * @param array $options
     *
     * @return bool
     */
    protected function output_png(array $options=array()) {
        $file = isset($options['file']) ? $options['file'] : null;
        $quality = isset($options['quality']) ? $options['quality'] : null;
        $filters = isset($options['filters']) ? $options['filters'] : null;
        if (($file === null) && !headers_sent()) {
            header("Content-Type: image/png");
        }
        return imagepng($this->image, $file, $quality, $filters);
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    protected function output_jpeg(array $options=array()) {
        $file = isset($options['file']) ? $options['file'] : null;
        $quality = isset($options['quality'])
            ? $options['quality']
            : self::DEFAULT_JPEG_QUALITY;
        if (($file === null) && !headers_sent()) {
            header("Content-Type: image/jpeg");
        }
        return imagejpeg($this->image, $file, $quality);
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    protected function output_gif(array $options=array()) {
        $file = isset($options['file']) ? $options['file'] : null;
        if (isset($options['file']) && !headers_sent()) {
            header("Content-Type: image/gif");
        }
        return imagegif($this->image, $file);
    }

    /**
     * @return bool|int
     */
    protected function gdColor() {
        $args = func_get_args();

        $exprRGB = '/^rgb\(\s*(\d{1,3})\s*\,\s*(\d{1,3})\s*\,\s*(\d{1,3})\s*\)$/i';
        $exprHex1 = '/^\#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i';
        $exprHex2 = '/^\#?([0-9a-f])([0-9a-f])([0-9a-f])$/i';
        $exprByte = '/^([01]?\d?\d|2[0-4]\d|25[0-5])$/';

        if (!isset($args[0])) {
            return false;
        }

        if (count($args[0]) == 3) {
            list($r, $g, $b) = $args[0];

        } elseif (preg_match($exprRGB, $args[0], $match)) {
            list($tmp, $r, $g, $b) = $match;

        } elseif (preg_match($exprHex1, $args[0], $match)) {
            list($tmp, $r, $g, $b) = $match;
            $r = hexdec($r);
            $g = hexdec($g);
            $b = hexdec($b);

        } elseif (preg_match($exprHex2, $args[0], $match)) {
            list($tmp, $r, $g, $b) = $match;
            $r = hexdec("$r$r");
            $g = hexdec("$g$g");
            $b = hexdec("$b$b");

        } elseif ((count($args) == 3) &&
            preg_match($exprByte, $args[0]) &&
            preg_match($exprByte, $args[1]) &&
            preg_match($exprByte, $args[2])
        ) {
            list($r, $g, $b) = $args;

        } else {
            return false;
        }

        return imagecolorallocate($this->image, $r, $g, $b);
    }

    /**
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @param $color
     *
     * @return bool
     */
    protected function imageFilledRectangle($x1, $y1, $x2, $y2, $color) {
        $color = $this->gdColor($color);
        if ($color === false) {
            return false;
        }
        return imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $color);
    }

    /**
     * @param      $src
     * @param int  $dstX
     * @param int  $dstY
     * @param int  $srcX
     * @param int  $srcY
     * @param null $dstW
     * @param null $dstH
     * @param null $srcW
     * @param null $srcH
     *
     * @return bool
     */
    protected function imageCopyResampled(
        $src, $dstX=0, $dstY=0, $srcX=0, $srcY=0, $dstW=null, $dstH=null, $srcW=null, $srcH=null
    ) {
        $imageDetails = $this->buildImage($src);

        if ($imageDetails === false) {
            return false;
        }

        list($src, $srcWidth, $srcHeight) = $imageDetails;

        if (null === $dstW) {
            $dstW = $this->width - $dstW;
        }
        if (null === $dstH) {
            $dstH = $this->height - $dstY;
        }
        if (null === $srcW) {
            $srcW = $srcWidth - $srcX;
        }
        if (null === $srcH) {
            $srcH = $srcHeight - $srcY;
        }
        return imagecopyresampled($this->image, $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
    }
}

