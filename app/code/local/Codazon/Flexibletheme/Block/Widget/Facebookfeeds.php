<?php

/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_Widget_FacebookFeeds extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected $_template = 'codazon_flexibletheme/widget/facebookfeeds.phtml';
    
    protected $_defaultData = array(
        'page_url'      => 'https://www.facebook.com/facebook',
        'hide_cover'    => 0,
        'show_facepile' => 1
    );
    
    public function _construct() {
        $this->addData(array(
            'cache_lifetime' => 86400,
            'cache_tags' => array('CDZ_FACEBOOK_FEED')
        ));
    }
    
    public function getCacheKeyInfo()
    {
        $instagram = serialize($this->getData());
        return array(
            'CDZ_FACEBOOK_FEED',
            Mage::app()->getStore()->getId(),
            md5(json_encode($this->getData())),             
            $instagram
        );
    }
}