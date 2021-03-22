<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once 'Mage/Catalog/controllers/Product/CompareController.php';
 
class Codazon_Quickviewpro_CompareController extends Mage_Catalog_Product_CompareController
{
    public function addAction()
    {
        $result = array(
            'success'   => false,
            'message'   => ''
        );
        if (!$this->_validateFormKey()) {
            $result = array(
                'success'   => false,
                'message'   => $this->__('Inavalid form key.')
            );
        } else {
            $result = $this->_addItemToCompare();
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($result));
    }
    
    public function removeAction()
    {
        $result = array(
            'success'   => false,
            'message'   => $this->__('An error occurred while clearing comparison list.')
        );
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if($product->getId()) {
                /** @var $item Mage_Catalog_Model_Product_Compare_Item */
                $item = Mage::getModel('catalog/product_compare_item');
                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
                } elseif ($this->_customerId) {
                    $item->addCustomerData(
                        Mage::getModel('customer/customer')->load($this->_customerId)
                    );
                } else {
                    $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
                }

                $item->loadByProduct($product);

                if($item->getId()) {
                    $item->delete();
                    $this->loadLayout();
                    $result['success'] = true;
                    $result['ajax_result_content'] = $this->getLayout()->createBlock('core/template')
                        ->setTemplate('codazon_quickviewpro/compare/ajax-result-content.phtml')
                        ->setMessage('%s has been removed from comparison list.')
                        ->setProduct($product)
                        ->toHtml();
                     if ($updatedBlocks = Mage::getStoreConfig('cdz_ajax_block/ajax_compare/update_blocks')) {
                        $layout = $this->getLayout();
                        $updatedBlocks = unserialize($updatedBlocks);
                        $blocks = array();
                        foreach ($updatedBlocks['id'] as $index => $block) {
                            $value = $layout->getBlock($updatedBlocks['xml'][$index]);
                            if ($value) {
                                $tmp['key'] = $block;
                                $tmp['value'] = $value->toHtml();
                                $blocks[] = $tmp;
                            }
                        }
                        $result['update_blocks'] = $blocks;
                    }
                    Mage::dispatchEvent('catalog_product_compare_remove_product', array('product'=>$item));
                    Mage::helper('catalog/product_compare')->calculate();
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->__('Cannot specify item.');
                }
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($result));
    }
    
    public function clearAction()
    {
        $result = array(
            'success'   => false,
            'message'   => ''
        );
        $items = Mage::getResourceModel('catalog/product_compare_item_collection');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        } elseif ($this->_customerId) {
            $items->setCustomerId($this->_customerId);
        } else {
            $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        /** @var $session Mage_Catalog_Model_Session */
        $session = Mage::getSingleton('catalog/session');
        try {
            $items->clear();
            $result['success'] = true;
            $result['message'] = $this->__('The comparison list was cleared.');
            Mage::helper('catalog/product_compare')->calculate();
            if ($updatedBlocks = Mage::getStoreConfig('cdz_ajax_block/ajax_compare/update_blocks')) {
                $this->loadLayout();
                $layout = $this->getLayout();
                $updatedBlocks = unserialize($updatedBlocks);
                $blocks = array();
                foreach ($updatedBlocks['id'] as $index => $block) {
                    $value = $layout->getBlock($updatedBlocks['xml'][$index]);
                    if ($value) {
                        $tmp['key'] = $block;
                        $tmp['value'] = $value->toHtml();
                        $blocks[] = $tmp;
                    }
                }
                $result['update_blocks'] = $blocks;
            }
        } catch (Mage_Core_Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = $this->__('An error occurred while clearing comparison list.');
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($result));
    }
    
    protected function _addItemToCompare()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId
            && (Mage::getSingleton('log/visitor')->getId() || Mage::getSingleton('customer/session')->isLoggedIn())
        ) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product' => $product));
            }
            
            Mage::helper('catalog/product_compare')->calculate();
            $this->loadLayout();
            $result['success'] = true;
            $result['ajax_result_content'] = $this->getLayout()->createBlock('core/template')
                ->setTemplate('codazon_quickviewpro/compare/ajax-result-content.phtml')
                ->setProduct($product)
                ->toHtml();
            if ($updatedBlocks = Mage::getStoreConfig('cdz_ajax_block/ajax_compare/update_blocks')) {
                $layout = $this->getLayout();
                $updatedBlocks = unserialize($updatedBlocks);
                $blocks = array();
                foreach ($updatedBlocks['id'] as $index => $block) {
                    $value = $layout->getBlock($updatedBlocks['xml'][$index]);
                    if ($value) {
                        $tmp['key'] = $block;
                        $tmp['value'] = $value->toHtml();
                        $blocks[] = $tmp;
                    }
                }
                $result['update_blocks'] = $blocks;
            }
        } else {
            $result = array(
                'success'   => false,
                'message'   => $this->__('Cannot specify product.')
            );
        }
        return $result;
    }
}