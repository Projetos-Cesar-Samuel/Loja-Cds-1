<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Blogfeatures_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected $_mediaDir;
    
    public function __construct()
    {
        $this->_mediaDir = Mage::getBaseDir('media');
        return $this;
    }
	public function getPlaceholderUrl($params){
		return $this->getImage(null, $params);
	}
    
	public function getImage($post, $width, $height = null, $params = array())
    {
		$fileName = null;
        
		if(is_object($post)) {
			$fileName = $post->getPostImage();
		}
        
		if(empty($fileName)){
			$fileName = 'codazon/blog/default-placeholder.png';
		}
        
		$filePath = $this->_mediaDir . DS . $fileName;
        
		$newFileName = explode('/',$fileName);
		$newFileName = $newFileName[1];
		
        $newFilePath = $this->_mediaDir . DS . 'codazon_cache' .DS. 'blog' . DS . 'cache' . DS . $width .'x'. $height . DS . $newFileName;
		
        if (!isset($params['keep_frame'])) {
            $params['keep_frame'] = true;
        }
        
        //if(!file_exists($newFilePath)){
			if(file_exists($filePath)){
				$imageObj = new Varien_Image($filePath);
				$imageObj->constrainOnly(true);
				$imageObj->keepAspectRatio(true);
				$imageObj->keepFrame($params['keep_frame'], array('center', 'middle'));
				$imageObj->backgroundColor(array(255,255,255));
				$imageObj->resize($width, $height);
				$imageObj->save($newFilePath);
			}
		//}
		return $this->getUrl($newFilePath);
	}
	
    protected function getUrl($filePath)
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $filePath);
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }
}
	 