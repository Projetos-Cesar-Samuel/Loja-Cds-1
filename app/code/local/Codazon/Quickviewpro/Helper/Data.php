<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Quickviewpro_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $store;
    
    protected function getStore()
    {
        if ($this->store === null) {
            $this->store = Mage::app()->getStore();
        }
        return $this->store;
    }
    
    public function getWidgetParams($product)
    {
        return array(
            'url' => $this->getStore()->getUrl('quickviewpro/index/view', array('id' => $product->getId()))
        );
    }
    
    protected function getLabel()
    {
        return $this->__('Quick View');
    }
    
    public function getQuickShopButton($product)
    {
        return '<a class="qs-button js-quickview" data-quickview=\''.  json_encode($this->getWidgetParams($product))  .'\' href="javascript:void(0)" title="'.$this->getLabel().'"><span><span>'.$this->getLabel().'</span></span></a>';
    }
    
    
}