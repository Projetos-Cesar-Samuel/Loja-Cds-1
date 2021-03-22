<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_Header extends Codazon_Flexibletheme_Block_AbstractFlexibletheme
{
    protected function _toHtml()
    {
        return parent::_toHtml();
    }
    
    public function getElementHtml()
    {
        $header = $this->helper->getHeader();
		$type = $this->getData('attribute_code') ? : 'content';
		$content = $header->getData($type);
        if ($content) {
            return $this->filter($content);
        } else {
            return '';
        }
    }
}