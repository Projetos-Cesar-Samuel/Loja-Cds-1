<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once 'Mage/Wishlist/controllers/IndexController.php';

class Codazon_Quickviewpro_WishlistController extends Mage_Wishlist_IndexController
{
    public function preDispatch()
    {
        return Mage_Wishlist_Controller_Abstract::preDispatch();
    }
    public function addAction()
    {
        $result = array();
        if (!(Mage::getSingleton('customer/session')->isLoggedIn())) {
            $this->loadLayout();
            $result = array(
                'success' => false,
                'message' => $this->__('You need to log in first.')
            );
            if ($block = $this->getLayout()->getBlock('cdz_wishlist.login_form')) {
                $result['ajax_result_content'] = '<div class="mini-wishlist-container"><div class="wl-state wl-no-login">' . $block->toHtml() . '</div></div>';
                $result['popup_width'] = 500;
            }
            Mage::getSingleton('customer/session')->setBeforeWishlistRequest($this->getRequest()->getParams());
        } else {
            if (!$this->_validateFormKey()) {
                $result = array(
                    'success' => false,
                    'message' => $this->__('Inavalid form key.')
                );
            } else {
                $result = $this->_addItemToWishList();
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($result));
    }
    
    protected function _addItemToWishList()
    {   
        $result = array();
    
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $result['success'] = false;
            $result['message'] = $this->__('Cannot specify wishlist.');
            return $result;
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $result['success'] = false;
            $result['message'] = $this->__('Product ID is empty.');
            return $result;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $result['success'] = false;
            $result['message'] = $this->__('Cannot specify product.');
            return $result;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $rs = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($rs)) {
                Mage::throwException($rs);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );
            Mage::helper('wishlist')->calculate();
            
            $this->loadLayout();
            $layout = $this->getLayout();
            $result['success'] = true;
            $result['ajax_result_content'] = $this->getLayout()->createBlock('core/template')
                ->setTemplate('codazon_quickviewpro/wishlist/ajax-result-content.phtml')
                ->setProduct($product)
                ->toHtml();
            $result['wishlist_count'] = $wishlist->getItemsCount();
            $updatedBlocks = unserialize(Mage::getStoreConfig('cdz_ajax_block/ajax_wishlist/update_blocks'));
            if ($updatedBlocks) {
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
            $result['message'] = $this->__('An error occurred while adding item to wishlist: %s', $e->getMessage());
        }
        catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = $this->__('An error occurred while adding item to wishlist.');
        }
        return $result;
    }
    
    /**
     * Remove item
     */
    public function removeAction()
    {
        $result = $this->_removeWishlistItem();
        $this->getResponse()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($result));
    }
    protected function _removeWishlistItem()
    {
        $result = array();
        if (!$this->_validateFormKey()) {
            $result['success'] = false;
            $result['message'] = $this->__('Error occurred. Try to refresh page.');
            return $result;
        }
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('wishlist/item')->load($id);
        if (!$item->getId()) {
            $result['success'] = false;
            $result['message'] = $this->__('Cannot specify item.');
            return $result;
        }
        $wishlist = $this->_getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $result['success'] = false;
            $result['message'] = $this->__('Cannot specify wishlist.');
            return $result;
        }
        try {
            $product = $item->getProduct();
            $item->delete();
            $wishlist->save();
            
            $this->loadLayout();
            $layout = $this->getLayout();
            $result['success'] = true;
            $result['message'] = $this->getLayout()->createBlock('core/template')
                ->setTemplate('codazon_quickviewpro/wishlist/ajax-result-content.phtml')
                ->setMessage('%s was removed from your wishlist')
                ->setProduct($product)
                ->toHtml();
            Mage::helper('wishlist')->calculate();
            $result['wishlist_count'] = $wishlist->getItemsCount();
            $updatedBlocks = unserialize(Mage::getStoreConfig('cdz_ajax_block/ajax_wishlist/update_blocks'));
            if ($updatedBlocks) {
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
            $result['message'] = $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage());
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = $this->__('An error occurred while deleting the item from wishlist.');
        }
        return $result;
    }
    
}