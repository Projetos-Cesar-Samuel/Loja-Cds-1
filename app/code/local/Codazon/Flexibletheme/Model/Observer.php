<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Observer
{
    
    public function generateLayout(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('flexibletheme');
        $themeId = $helper->getCurrentThemeId();
        if ($themeId != 0) {
            $layout = $observer->getLayout();
            
            $update = $layout->getUpdate();
            
            $handes = $update->getHandles();
            $layoutXml = '';
            //$layoutXml .= file_get_contents(__dir__ . '/' . 'test.xml');
            
            if ($header = $helper->getHeader()) {
                $layoutXml .= $header->getData('layout_xml');
            }
            if ($footer = $helper->getFooter()) {
                $layoutXml .= $footer->getData('layout_xml');
            }
            
            //if (in_array('default', $handes)) {
                $update->addUpdate($layoutXml);
            //}
        }
        return $this;
    }
    
    public function modifyBlocks(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('flexibletheme');
        $themeId = $helper->getCurrentThemeId();
        if ($themeId != 0) {
            $layout = $observer->getLayout();
            $helper->addScripts($layout);
        }
        return $this;
    }
    
    protected function addThemeScripts($head)
    {
        
    }
    
    public function beforeCategoryViewRender()
    {
        $controller = Mage::app()->getFrontController();
        $request = $controller->getRequest();
        $layout = Mage::app()->getLayout();
        
        if (Mage::helper('flexibletheme')->getCurrentThemeId()) {
            $enablePriceSlider = Mage::getStoreConfig('codazon_ajaxlayerednavpro/general/enable_price_slider');
            $enableAjaxLayer = Mage::getStoreConfig('codazon_ajaxlayerednavpro/general/enable');
            if ($enablePriceSlider && $enableAjaxLayer) {
                if ($block = $layout->getBlock('catalog.leftnav')) {
                    if ($block instanceof Mage_Catalog_Block_Layer_View) {
                        if ($block->getChild('price_filter')) {
                            $block->getChild('price_filter')
                                ->setTemplate('catalog/layer/filter/price.phtml');
                        }
                    }
                }
            }
        }
        
        
        if ($request->getParam('ajax_nav')) {
            $result = array();
            
            // $layout->setDirectOutput(false);
            // $output = $layout->getOutput();
            // Mage::getSingleton('core/translate_inline')->processResponseBody($output);
            
            
            if ($block = $layout->getBlock('category.products')) {
                $result['category_products'] = $block->toHtml();
            }
            if ($block = $layout->getBlock('catalog.leftnav')) {
                $result['catalog_leftnav'] = $block->toHtml();
            }
            $controller->getResponse()->setHeader('Content-type', 'application/json');
            $controller->getResponse()->setBody(json_encode($result));
            $controller->getResponse()->sendHeaders();
            $controller->getResponse()->outputBody();
            die();
        }
    }
}