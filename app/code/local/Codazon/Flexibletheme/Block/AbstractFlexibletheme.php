<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_AbstractFlexibletheme extends Mage_Core_Block_Template
{
    protected $helper;
    
    public function _construct()
    {
        parent::_construct();
        $this->helper = Mage::helper('flexibletheme');
    }
    
    public function filter($content){
		return Mage::helper('cms')->getBlockTemplateProcessor()->filter($content);
	}
    
    public function getMediaUrl()
    {
        if ($this->_mediaUrl === null) {
            $this->_mediaUrl = Mage::getBaseUrl('media');
        }
        return $this->_mediaUrl;
    }
}