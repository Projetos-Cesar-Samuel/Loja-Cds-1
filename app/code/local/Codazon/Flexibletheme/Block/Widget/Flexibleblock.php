<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_Widget_Flexibleblock extends Codazon_Flexibletheme_Block_Content implements Mage_Widget_Block_Interface
{
   
    public function _construct()
    {
        parent::_construct();
        $this->setNeedFilterHtml(true);
        $this->setTemplate('codazon_flexibletheme/widget/flexibleblock.phtml');
        return $this;
    }
    
    public function getMainContent()
    {
        if ($this->_mainContentModel === false) {
            $identifier = $this->getData('block_identifier');
            $this->_mainContentModel = Mage::getModel('flexibletheme/content')
                ->getCollection()
                ->setStoreId($this->helper->getStoreId())
                ->addFieldToFilter('identifier', $identifier)
                ->addFieldToFilter('is_active', 1)
                ->addAttributeToSelect(['content', 'layout_xml'])
                ->getFirstItem();
        }
        return $this->_mainContentModel;
    }
    
    public function getCssUrl()
    {
        return str_replace(['https://', 'http://'], ['//', '//'], $this->getMediaUrl() . $this->getMainContent()->getMainCssFileRelativePath()) . '?version='. $this->getMainContent()->getVersion();
    }
}