<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Form_Renderer_Fieldset_Element extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
{
    public function getScopeLabel()
    {
        $html = '';
        $attribute = $this->getElement()->getEntityAttribute();
        if (!$attribute || Mage::app()->isSingleStoreMode() || $attribute->getFrontendInput()=='gallery') {
            return Mage::helper('adminhtml')->__('[GLOBAL]');
        }

        /*
         * Check if the current attribute is a 'price' attribute. If yes, check
         * the config setting 'Catalog Price Scope' and modify the scope label.
         */
        $isGlobalPriceScope = false;
        if ($attribute->getFrontendInput() == 'price') {
            $priceScope = Mage::getStoreConfig('catalog/price/scope');
            if ($priceScope == 0) {
                $isGlobalPriceScope = true;
            }
        }

        if ($attribute->isScopeGlobal() || $isGlobalPriceScope) {
            $html .= Mage::helper('adminhtml')->__('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= Mage::helper('adminhtml')->__('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= Mage::helper('adminhtml')->__('[STORE VIEW]');
        }

        return $html;
    }
}
