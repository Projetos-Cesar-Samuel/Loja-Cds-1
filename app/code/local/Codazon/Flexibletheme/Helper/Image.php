<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected $_cacheDir = 'codazon_cache/flexibletheme';
    protected $_type = '';
    protected $_baseDir = '';
    protected $_mediaDir;
    protected $_mediaUrl;
    
    public function __construct()
    {
        $this->_mediaDir = Mage::getBaseDir('media');
        $this->_mediaUrl = Mage::getBaseUrl('media');
    }
    
    public function init($params)
    {
        
        if (isset($params['type'])) {
            $this->_type = $params['type'];
        }
        return $this;
    }
    
	public function getPlaceholderUrl($params){
		return $this->getImage(null,$params);
	}
    
    protected function _getBasePath($fileName)
    {
        if ($this->_type) {
            return $this->_mediaDir . DS . $this->_type . DS . $fileName;
        } else {
            return $this->_mediaDir . DS . $fileName;
        }
    }
    
    protected function _getCachePath($fileName)
    {
        if ($this->_type) {
            return $this->_mediaDir . DS . $this->_cacheDir . DS . $this->_type . DS . $fileName;
        } else {
             return $this->_mediaDir . DS . $this->_cacheDir . DS . $fileName;
        }
    }
    
	public function getImage($fileName, $width, $height, $params = array()){
		
        $filePath = $this->_getBasePath($fileName);
		$newFilePath = $this->_getCachePath($fileName);
        
		if(!file_exists($newFilePath)) {
			if(file_exists($filePath)) {
				$imageObj = new Varien_Image($filePath);
				$imageObj->constrainOnly(true);
				$imageObj->keepAspectRatio(true);
				$imageObj->keepFrame(true, array('center', 'middle'));
				$imageObj->backgroundColor(array(255,255,255));
				$imageObj->resize($width, $height);
				$imageObj->save($newFilePath);
			}
		}
		return $this->getUrl($newFilePath);
	}
	protected function getUrl($filePath)
    {
        $path = str_replace($this->_mediaDir . DS, "", $filePath);
        return $this->_mediaUrl . str_replace(DS, '/', $path);
    }
}
