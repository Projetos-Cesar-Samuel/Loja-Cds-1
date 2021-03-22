<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Block_Catalog_Product_List_Related extends Amasty_Mostviewed_Block_Catalog_Product_List_Related_Pure
{
    protected $_productItems = array();

    protected function _prepareData()
    {
        if (!Mage::getStoreConfig('ammostviewed/related_products/enabled')
            || (Mage::getStoreConfig('ammostviewed/related_products/show_for_out_of_stock_only')
                && Mage::registry('product')->getIsInStock())
        ) {
            return parent::_prepareData();
        }

        $product = Mage::registry('product');
        if (!$product) {
            return parent::_prepareData();
        }
        $currentProductId = $product->getId();
        $manuallyAdded = (int) Mage::getStoreConfig('ammostviewed/related_products/manually');

        switch ($manuallyAdded) {
            case Amasty_Mostviewed_Model_Source_Manually::APPEND:
                parent::_prepareData();
                if (Mage::getStoreConfig('ammostviewed/related_products/size') > $this->_itemCollection->getSize()) {
                    $collection = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'related_products');
                    if (0 < $collection->getSize()) {
                        $this->_itemCollection = $collection;
                    }
                }
                break;
            case Amasty_Mostviewed_Model_Source_Manually::REPLACE:
                $this->_itemCollection = Mage::helper('ammostviewed')
                    ->getViewedWith($currentProductId, 'related_products');
                if (!$this->_itemCollection->getSize()) {
                    parent::_prepareData();
                }
                break;
            case Amasty_Mostviewed_Model_Source_Manually::NOTHING:
                parent::_prepareData();
                if (!$this->_itemCollection->getSize()) {
                    $this->_itemCollection = Mage::helper('ammostviewed')
                        ->getViewedWith($currentProductId, 'related_products');
                }
                break;
        }

        return $this;
    }

    /**
     * Method is used for Enterprise version to get items for related products and up-sells
     *
     * @return Catalog_Product_Model_Product[]
     */
    public function getItemCollection()
    {
        if (!Mage::getStoreConfig('ammostviewed/related_products/enabled') ||
            Mage::getEdition() != Mage::EDITION_ENTERPRISE) {
            return parent::getItemCollection();
        }
        if (!$this->_productItems) {
            $product = Mage::registry('product');
            if (!$product) {
                return parent::getItemCollection();
            }
            $currentProductId = $product->getId();
            $manuallyAdded = (int)Mage::getStoreConfig('ammostviewed/related_products/manually');

            switch ($manuallyAdded) {
                case Amasty_Mostviewed_Model_Source_Manually::APPEND:
                    $this->_productItems = parent::getItemCollection();
                    if (Mage::getStoreConfig('ammostviewed/related_products/size') > count($this->_productItems)) {
                        $relatedItems = Mage::helper('ammostviewed')
                            ->getViewedWith($currentProductId, 'related_products')->getItems();
                        if ($relatedItems) {
                            $this->_productItems = $relatedItems;
                        }
                    }
                    break;
                case Amasty_Mostviewed_Model_Source_Manually::REPLACE:
                    $this->_productItems = Mage::helper('ammostviewed')
                        ->getViewedWith($currentProductId, 'related_products')->getItems();
                    if (!$this->_productItems) {
                        $this->_productItems = parent::getItemCollection();
                    }
                    break;
                case Amasty_Mostviewed_Model_Source_Manually::NOTHING:
                    $items = parent::getItemCollection();
                    if (!$items) {
                        $this->_productItems = Mage::helper('ammostviewed')
                            ->getViewedWith($currentProductId, 'related_products')->getItems();
                    }
                    break;
            }
        }

        return $this->_productItems;
    }
}
