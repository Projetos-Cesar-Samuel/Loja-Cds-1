<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_Widget_Instagramphotos extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected $_template = null;
	
    const API_URL = 'https://api.instagram.com/v1/users/self/media/recent';
	
    const ACCESS_TOKEN = '3893338542.38fb276.e8dbfaac57214bf69c0439027ee39d85';
	
    protected $_sliderData = null;
	    
	public function getCacheKeyInfo()
    {
        $instagram = serialize($this->getData());
        
        return array(
            'CDZ_INSTAGRAM',
            Mage::app()->getStore()->getId(),
            $instagram
        );
    }
	
    public function fetchData($url)
    {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
  	}
	
	public function getInstagramRecentPhotos()
    {
		$accessToken = $this->getData('access_token')?$this->getData('access_token'):self::ACCESS_TOKEN;
		$url = self::API_URL . "?access_token={$accessToken}";
		$result = json_decode($this->fetchData($url));
        if ($result) {
            return @$result->data;
        } else {
            return array();
        }
	}
	
	public function getTemplate()
    {
        if ($this->_template == null) {
			if($this->getData('custom_template')){
				$this->_template = $this->getData('custom_template');
			} else {
				$this->_template = 'codazon_flexibletheme/widget/instagramphotos/grid.phtml';
			}
		}
        
		return $this->_template;
    }
	
	public function getSliderData()
    {
        if (!$this->_sliderData) {
            $this->_sliderData = array(
                'nav'           => (bool)$this->getData('slider_nav'),
                'dots'          => (bool)$this->getData('slider_dots'),
                'loop'          => (bool)$this->getData('slider_loop'),
                'stagePadding'  => (float)$this->getData('stage_padding'),
                'lazyLoad'      => true,
                'margin'        => (float)$this->getData('slider_margin')
            );
            $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = ['items' => (float)$this->getData('items_' . $adapt)];
            }
        }
        return $this->_sliderData;
    }
}