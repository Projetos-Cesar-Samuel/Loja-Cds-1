<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Block_Checkout_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
{
    public function getItems()
    {
        $items = $this->getData('items');
        if (!is_null($items)) {
            return $items;
        }

        $alreadyInCartIds = $this->_getCartProductIds();
        if (!$alreadyInCartIds) {
            return parent::getItems();
        }

        if (!Mage::getStoreConfig('ammostviewed/cross_sells/enabled')) {
             return parent::getItems();
        }

        $currentProductId = (int) $this->_getLastAddedProductId();
        if (!$currentProductId) {
            $currentProductId = current($alreadyInCartIds);
        }

        $items = array();
        $manuallyAdded = (int) Mage::getStoreConfig('ammostviewed/cross_sells/manually');

        switch ($manuallyAdded) {
            case Amasty_Mostviewed_Model_Source_Manually::APPEND:
                $items = parent::getItems();
                if (Mage::getStoreConfig('ammostviewed/cross_sells/size') > count($items)) {
                    $resultItems = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'cross_sells', $alreadyInCartIds);
                    if (0 < count($resultItems)) {
                        $items = $resultItems;
                    }
                }
                break;
            case Amasty_Mostviewed_Model_Source_Manually::REPLACE:
                $items = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'cross_sells', $alreadyInCartIds);
                if (empty($items)) {
                    $items = parent::getItems();
                }
                break;
            case Amasty_Mostviewed_Model_Source_Manually::NOTHING:
                $items = parent::getItems();
                if (empty($items)) {
                    $items = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'cross_sells', $alreadyInCartIds);
                }
                break;
        }

        $this->setData('items', $items);

        return $items;
    }
}
