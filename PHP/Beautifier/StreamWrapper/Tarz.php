<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* Custom stream to handle Tar files (compressed and uncompressed)
*
* PHP version 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license/3_0.txt.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
* @category   PHP
* @package PHP_Beautifier
* @subpackage StreamWrapper
* @author Claudio Bustos <cdx@users.sourceforge.com>
* @copyright  2004-2006 Claudio Bustos
* @link     http://pear.php.net/package/PHP_Beautifier
* @link     http://beautifyphp.sourceforge.net
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    CVS: $Id:$
*/
/**
* Require Archive_Tar
*/
require_once 'Archive/Tar.php';
/**
* Custom stream to handle Tar files (compressed and uncompressed)
* Use URL tarz://myfile.tgz#myfile.php
*
* @category   PHP
* @package PHP_Beautifier
* @subpackage StreamWrapper
* @author Claudio Bustos <cdx@users.sourceforge.com>
* @copyright  2004-2006 Claudio Bustos
* @link     http://pear.php.net/package/PHP_Beautifier
* @link     http://beautifyphp.sourceforge.net
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    Release: 0.1.14
*/
class PHP_Beautifier_StreamWrapper_Tarz implements PHP_Beautifier_StreamWrapper_Interface {
    public $oTar;
    public $sTar;
    public $sPath;
    public $sText;
    public $iSeek = 0;
    public $iSeekDir = 0;
    public $aContents = array();

    /**
     * @param $sPath
     * @param $sMode
     * @param $iOptions
     * @param $sOpenedPath
     *
     * @return bool|mixed
     */
    public function stream_open($sPath, $sMode, $iOptions, &$sOpenedPath)
    {
        if ($this->getTar($sPath)) {
            //ArrayNested->off()
            $aContents = $this->oTar->listContent();
            if (array_filter($aContents, array($this, 'tarFileExists'))) {
                $this->sText = $this->oTar->extractInString($this->sPath);
                return true;
            }
        }
        //ArrayNested->on()
        return false;
    }

    /**
     * @param $sPath
     *
     * @return bool
     */
    public function getTar($sPath)
    {
        if (preg_match("/tarz:\/\/(.*?(\.tgz|\.tar\.gz|\.tar\.bz2|\.tar)+)(?:#\/?(.*))*/", $sPath, $aMatch)) {
            $this->sTar = $aMatch[1];
            if (strpos($aMatch[2], 'gz') !== FALSE) {
                $sCompress = 'gz';
            } elseif (strpos($aMatch[2], 'bz2') !== FALSE) {
                $sCompress = 'bz2';
            } elseif (strpos($aMatch[2], 'tar') !== FALSE) {
                $sCompress = false;
            } else {
                return false;
            }
            if (isset($aMatch[3])) {
                $this->sPath = $aMatch[3];
            }
            if (file_exists($this->sTar)) {
                $this->oTar = new Archive_Tar($this->sTar, $sCompress);
                return true;
            }
        } else {
            return false;
        }
    }
    public function stream_close()
    {
        unset($this->oTar, $this->sText, $this->sPath, $this->iSeek);
    }

    /**
     * @param $iCount
     *
     * @return bool|mixed|string
     */
    public function stream_read($iCount)
    {
        $sRet = substr($this->sText, $this->iSeek, $iCount);
        $this->iSeek+= strlen($sRet);
        return $sRet;
    }

    /**
     * @param $sData
     *
     * @return mixed|void
     */
    public function stream_write($sData)
    {
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        // BUG in 5.0.0RC1<PHP<5.0.0.0RC4
        // DON'T USE EOF. Use ... another option :P
        if (version_compare(PHP_VERSION, '5.0.0.RC.1', '>=') and version_compare(PHP_VERSION, '5.0.0.RC.4', '<')) {
            return !($this->iSeek >= strlen($this->sText));
        } else {
            return $this->iSeek >= strlen($this->sText);
        }
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return $this->iSeek;
    }

    /**
     * @param $iOffset
     * @param $iWhence
     *
     * @return bool|mixed
     */
    public function stream_seek($iOffset, $iWhence)
    {
        switch ($iWhence) {
            case SEEK_SET:
                if ($iOffset<strlen($this->sText) and $iOffset >= 0) {
                    $this->iSeek = $iOffset;
                    return true;
                } else {
                    return false;
                }
            break;

            case SEEK_CUR:
                if ($iOffset >= 0) {
                    $this->iSeek+= $iOffset;
                    return true;
                } else {
                    return false;
                }
            break;

            case SEEK_END:
                if (strlen($this->sText) +$iOffset >= 0) {
                    $this->iSeek = strlen($this->sText) +$iOffset;
                    return true;
                } else {
                    return false;
                }
            break;

            default:
                return false;
            }
        }
        public function stream_flush()
        {
        }
        public function stream_stat()
        {
        }

    /**
     * @param $sPath
     *
     * @return mixed|void
     */
    public function unlink($sPath)
        {
        }

    /**
     * @param $sPath
     * @param $iOptions
     *
     * @return bool|mixed
     */
    public function dir_opendir($sPath, $iOptions)
        {
            if ($this->getTar($sPath)) {
                array_walk($this->oTar->listContent() , array(
                    $this,
                    'getFileList'
                ));
                return true;
            } else {
                return false;
            }
        }

    /**
     * @return bool|mixed
     */
    public function dir_readdir()
        {
            if ($this->iSeekDir >= count($this->aContents)) {
                return false;
            } else {
                return $this->aContents[$this->iSeekDir++];
            }
        }
        public function dir_rewinddir()
        {
            $this->iSeekDir = 0;
        }
        public function dir_closedir()
        {
            //unset($this->oTar, $this->aContents, $this->sPath, $this->iSeekDir);
            //return true;
            
        }

    /**
     * @param $aInput
     */
    public function getFileList($aInput)
        {
            $this->aContents[] = $aInput['filename'];
        }

    /**
     * @param $aInput
     *
     * @return bool
     */
    public function tarFileExists($aInput)
        {
            return ($aInput['filename'] == $this->sPath and empty($aInput['typeflag']));
        }
    }
    stream_wrapper_register('tarz', 'PHP_Beautifier_StreamWrapper_Tarz');
