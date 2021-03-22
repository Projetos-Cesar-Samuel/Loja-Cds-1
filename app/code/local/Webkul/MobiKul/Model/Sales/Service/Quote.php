<?php

    class Webkul_MobiKul_Model_Sales_Service_Quote    {

        protected $_quote;

        protected $_convertor;

        protected $_orderData = array();

        protected $_recurringPaymentProfiles = array();

        protected $_order = null;

        protected $_shouldInactivateQuote = true;

        public function __construct(Mage_Sales_Model_Quote $quote)        {
            $this->_quote       = $quote;
            $this->_convertor   = Mage::getModel("sales/convert_quote");
        }

        public function setConvertor(Mage_Sales_Model_Convert_Quote $convertor)        {
            $this->_convertor = $convertor;
            return $this;
        }

        public function getQuote()        {
            return $this->_quote;
        }

        public function setOrderData(array $data)        {
            $this->_orderData = $data;
            return $this;
        }

        public function submit()        {
            return $this->submitOrder();
        }

        public function submitOrder()        {
            $this->_deleteNominalItems();
            $this->_validate();
            $quote = $this->_quote;
            $isVirtual = $quote->isVirtual();

            $transaction = Mage::getModel("core/resource_transaction");
            if ($quote->getCustomerId()) {
                $transaction->addObject($quote->getCustomer());
            }
            $transaction->addObject($quote);

            $quote->reserveOrderId();
            if ($isVirtual) {
                $order = $this->_convertor->addressToOrder($quote->getBillingAddress());
            } else {
                $order = $this->_convertor->addressToOrder($quote->getShippingAddress());
            }
            $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
            if ($quote->getBillingAddress()->getCustomerAddress()) {
                $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
            }
            if (!$isVirtual) {
                $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
                if ($quote->getShippingAddress()->getCustomerAddress()) {
                    $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
                }
            }
            $order->setPayment($this->_convertor->paymentToOrderPayment($quote->getPayment()));
            foreach ($this->_orderData as $key => $value) {
                $order->setData($key, $value);
            }
            foreach ($quote->getAllVisibleItems() as $item) {
                $orderItem = $this->_convertor->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $order->addItem($orderItem);
            }
            $order->setQuote($quote);
            $transaction->addObject($order);
            $transaction->addCommitCallback(array($order, "place"));
            $transaction->addCommitCallback(array($order, "save"));
/**
* We can use configuration data for declare new order status
*/
            Mage::dispatchEvent("checkout_type_onepage_save_order", array("order" => $order, "quote" => $quote));
            Mage::dispatchEvent("sales_model_service_quote_submit_before", array("order" => $order, "quote" => $quote));
            try {
                $transaction->save();
                $this->_inactivateQuote();
                Mage::dispatchEvent("sales_model_service_quote_submit_success", array("order" => $order, "quote" => $quote));
            } catch (Exception $e) {
                if (!Mage::getSingleton("customer/session")->isLoggedIn()) {
// reset customer ID"s on exception, because customer not saved /////////////////////////////////////////////////////////////////
                    $quote->getCustomer()->setId(null);
                }
// reset order ID"s on exception, because order not saved ///////////////////////////////////////////////////////////////////////
                $order->setId(null);
/** @var $item Mage_Sales_Model_Order_Item */////////////////////////////////////////////////////////////////////////////////////
                foreach ($order->getItemsCollection() as $item) {
                    $item->setOrderId(null);
                    $item->setItemId(null);
                }
                Mage::dispatchEvent("sales_model_service_quote_submit_failure", array("order" => $order, "quote" => $quote));
                throw $e;
            }
            Mage::dispatchEvent("sales_model_service_quote_submit_after", array("order" => $order, "quote" => $quote));
            $this->_order = $order;
            return $order;
        }

        public function submitNominalItems()        {
            $this->_validate();
            $this->_submitRecurringPaymentProfiles();
            $this->_inactivateQuote();
            $this->_deleteNominalItems();
        }

        public function submitAll()        {
// don"t allow submitNominalItems() to inactivate quote /////////////////////////////////////////////////////////////////////////
            $shouldInactivateQuoteOld = $this->_shouldInactivateQuote;
            $this->_shouldInactivateQuote = false;
            try {
                $this->submitNominalItems();
                $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
            } catch (Exception $e) {
                $this->_shouldInactivateQuote = $shouldInactivateQuoteOld;
                throw $e;
            }
// no need to submit the order if there are no normal items remained ////////////////////////////////////////////////////////////
            if (!$this->_quote->getAllVisibleItems()) {
                $this->_inactivateQuote();
                return;
            }
            $this->submitOrder();
        }

        public function getRecurringPaymentProfiles()       {
            return $this->_recurringPaymentProfiles;
        }

        public function getOrder()        {
            return $this->_order;
        }

        protected function _inactivateQuote()        {
            if ($this->_shouldInactivateQuote) {
                $this->_quote->setIsActive(false);
            }
            return $this;
        }

        protected function _validate()        {
            if (!$this->getQuote()->isVirtual()) {
                $address = $this->getQuote()->getShippingAddress();
                $addressValidation = $address->validate();
                if ($addressValidation !== true) {
                    Mage::throwException(
                        Mage::helper("sales")->__("Please check shipping address information. %s", implode(" ", $addressValidation))
                    );
                }
                $method= $address->getShippingMethod();
                $rate  = $address->getShippingRateByCode($method);
                if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
                    Mage::throwException(Mage::helper("sales")->__("Please specify a shipping method."));
                }
            }
            $addressValidation = $this->getQuote()->getBillingAddress()->validate();
            if ($addressValidation !== true) {
                Mage::throwException(
                    Mage::helper("sales")->__("Please check billing address information. %s", implode(" ", $addressValidation))
                );
            }
            if (!($this->getQuote()->getPayment()->getMethod())) {
                Mage::throwException(Mage::helper("sales")->__("Please select a valid payment method."));
            }
            return $this;
        }

        protected function _submitRecurringPaymentProfiles()        {
            $profiles = $this->_quote->prepareRecurringPaymentProfiles();
            foreach ($profiles as $profile) {
                if (!$profile->isValid()) {
                    Mage::throwException($profile->getValidationErrors(true, true));
                }
                $profile->submit();
            }
            $this->_recurringPaymentProfiles = $profiles;
        }

        protected function _deleteNominalItems()        {
            foreach ($this->_quote->getAllVisibleItems() as $item) {
                if ($item->isNominal()) {
                    $item->isDeleted(true);
                }
            }
        }

    }