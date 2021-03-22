<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once 'Mage/Checkout/controllers/CartController.php';
 
class Codazon_Quickviewpro_CartController extends Mage_Checkout_CartController
{

    protected function _goBack($result = null, $product = null)
    {
        if ($result) {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $updatedBlocks = unserialize(Mage::getStoreConfig('cdz_ajax_block/ajax_cart/update_blocks'));
            $cart = $this->_getCart();
            $this->loadLayout();
            $layout = $this->getLayout();
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
            if ($product) {
                if ($value = $layout->getBlock('ajax_result_content')) {
                    $result['ajax_result_content'] = $value->setProduct($product)->setMessage($result['message'])->toHtml();
                }
            }
            $result['qty'] = $cart->getSummaryQty();
            $result['items_count'] = $cart->getItemsCount();
            $result['subtotal'] = Mage::helper('checkout')->formatPrice($cart->getQuote()->getSubtotal());
            
            return $this->getResponse()->setBody(json_encode($result));
        } else {
            $returnUrl = $this->getRequest()->getParam('return_url');
            if ($returnUrl) {

                if (!$this->_isUrlInternal($returnUrl)) {
                    throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
                }

                $this->_getSession()->getMessages(true);
                $this->getResponse()->setRedirect($returnUrl);
            } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
                && !$this->getRequest()->getParam('in_cart')
                && $backUrl = $this->_getRefererUrl()
            ) {
                $this->getResponse()->setRedirect($backUrl);
            } else {
                if (
                    (strtolower($this->getRequest()->getActionName()) == 'add')
                    && !$this->getRequest()->getParam('in_cart')
                ) {
                    $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
                }
                $this->_redirect('checkout/cart');
            }
            return $this;
        }
    }


    public function addAction()
    {
        $params = $this->getRequest()->getParams();
        $isAjax = isset($params['is_ajax']) ? true : false;
        if (!$this->_validateFormKey()) {
            if ($isAjax) {
                $result['success'] = false;
                $result['message'] = $this->__('Error occurred. Try to refresh page.');
                return $this->_goBack($result);
            } else {
                $this->_goBack();
                return;
            }
        }
        $cart   = $this->_getCart();
        
        unset($params['is_ajax']);
        
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack(['message' => $this->__('Product not exist.')]);
                return;
            }

            $cart->addProduct($product, $params);
            
            
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if ($isAjax) {
                $result = array();
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    
                    $result['success'] = true;
                    $result['message'] = $message;
                } else {
                    $result['success'] = false;
                }
                $this->_goBack($result, $product);
            } else {
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()) {
                        $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($message);
                    }
                    $this->_goBack();
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($isAjax) {
                $result = array();
                $message = Mage::helper('core')->escapeHtml($e->getMessage());
                $result['success'] = false;
                $result['message'] = $message;
                $this->_goBack($result);
            } else {
                if ($this->_getSession()->getUseNotice(true)) {
                    $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                    }
                }

                $url = $this->_getSession()->getRedirectUrl(true);
                if ($url) {
                    $this->getResponse()->setRedirect($url);
                } else {
                    $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
                }
            }
        } catch (Exception $e) {
            if ($isAjax) {
                $result = array();
                $message = Mage::helper('core')->escapeHtml($this->__('Cannot add the item to shopping cart.'));
                $result['success'] = false;
                $result['message'] = $message;
                $this->_goBack($result);
            } else {
                $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                Mage::logException($e);
                $this->_goBack();
            }
        }
    }
    
    

    
    public function viewAction()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }
    
}