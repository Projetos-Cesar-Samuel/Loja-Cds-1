<?php

    class Webkul_MobiKul_CheckoutController extends Mage_Core_Controller_Front_Action   {

        public function testAction()   { 
            // echo Mage::helper("core")->decrypt(Mage::getStoreConfig("payment/rm_pagseguro/token"));echo "<br>";
            // echo Mage::helper("core")->decrypt(Mage::getStoreConfig("payment/rm_pagseguro/sandbox_token"));die; 
            // $order = Mage::getModel('sales/order')->loadByIncrementId(100000575);
            // $payment = $order->getPayment();
            // echo "<pre>";
            // print_r($payment->getAdditionalInformation());

            $returnArray["liveEmail"]    = Mage::getStoreConfig("mobikul/pegseguro/merchant_email");
            $returnArray["liveToken"]    = Mage::helper("core")->decrypt(Mage::getStoreConfig("payment/rm_pagseguro/token"));
            $returnArray["sanboxEnable"] = Mage::getStoreConfigFlag("mobikul/pegseguro/sandbox");
            $returnArray["sandBoxEmail"] = Mage::getStoreConfig("mobikul/pegseguro/sandbox_merchant_email");
            $returnArray["sandBoxToken"] = Mage::helper("core")->decrypt(Mage::getStoreConfig("mobikul/pegseguro/sandbox_token"));
            $returnArray["paymentOptionName"] = "Boleto - Você será redirecionado para o site";
            $returnArray["showInstallmentTotal"] = Mage::getStoreConfigFlag("mobikul/pegseguro/show_total");
            echo "<pre>";
            print_r($returnArray);

            die;
        }
        public function cartDetailsAction()   {
            $returnArray = array();
            $returnArray["authKey"]                       = "";
            $returnArray["responseCode"]                  = 0;
            $returnArray["message"]                       = "";
            $returnArray["cartCount"]                     = 0;
            $returnArray["items"]                         = array();
            $returnArray["couponCode"]                    = "";
            $returnArray["isVirtual"]                     = false;
            $returnArray["subtotal"]["title"]             = "";
            $returnArray["subtotal"]["value"]             = "";
            $returnArray["subtotal"]["unformatedValue"]   = 0.0;
            $returnArray["discount"]["title"]             = "";
            $returnArray["discount"]["value"]             = "";
            $returnArray["discount"]["unformatedValue"]   = 0.0;
            $returnArray["shipping"]["title"]             = "";
            $returnArray["shipping"]["value"]             = "";
            $returnArray["shipping"]["unformatedValue"]   = 0.0;
            $returnArray["proceedToCheckout"]             = true;
            $returnArray["tax"]["title"]                  = "";
            $returnArray["tax"]["value"]                  = "";
            $returnArray["tax"]["unformatedValue"]        = 0.0;
            $returnArray["grandtotal"]["title"]           = "";
            $returnArray["grandtotal"]["value"]           = "";
            $returnArray["grandtotal"]["unformatedValue"] = 0.0;
            $returnArray["isAllowedGuestCheckout"]        = false;
            $returnArray["displayCartBothPrices"]         = 0;
            $returnArray["warning"]                       = '';
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $width        = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        $returnArray["displayCartBothPrices"] = Mage::helper("mobikul/catalog")->getDisplayCartBothPrices();
                        if($customerId != 0 || $quoteId != 0){
                            $quote->setStoreId($storeId)->collectTotals()->save();
                            $itemCollection = $quote->getAllVisibleItems();
                            foreach($itemCollection as $item) {
                                $eachItem = array();
                                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                                $eachItem["thresholdQty"] = Mage::getStoreConfig("cataloginventory/options/stock_threshold_qty");
                                $eachItem["remainingQty"] = $item->getProduct()->getStockItem()->getStockQty();
                                $imageData = Mage::helper("mobikul/image")->init($item->getProduct(), "thumbnail")->keepFrame(true)->resize($width/2.5)->__toString();
                                $eachItem["image"] = $imageData[0];
                                $eachItem["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                                $eachItem["name"] = Mage::helper("core")->stripTags($product->getName());
                                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                                if($item->getProduct()->getTypeId() == "configurable"){
                                    $configurableOptions = $options["attributes_info"];
                                    foreach($configurableOptions as $configurableOption) {
                                        $eachConfigurableOption = array();
                                        $eachConfigurableOption["label"] = $configurableOption["label"];
                                        $eachConfigurableOption["value"][] = $configurableOption["value"];
                                        $eachItem["options"][] = $eachConfigurableOption;
                                    }
                                }
                                if($item->getProduct()->getTypeId() == "bundle"){
                                    $bundleOptions = $options["bundle_options"];
                                    foreach($bundleOptions as $bundleOption) {
                                        $eachBundleOption = array();
                                        $eachBundleOption["label"] = $bundleOption["label"];
                                        foreach($bundleOption["value"] as $bundleOptionValue) {
                                            $price = 0;
                                            if($bundleOptionValue["price"] > 0)
                                                $price = $bundleOptionValue["price"]/$bundleOptionValue["qty"];
                                            $price = Mage::helper("core")->stripTags(Mage::helper("core")->currency($price));
                                            $eachBundleOptionValue = $bundleOptionValue["qty"]." x ".$bundleOptionValue["title"]." ".$price;
                                            $eachBundleOption["value"][] = $eachBundleOptionValue;
                                        }
                                        $eachItem["options"][] = $eachBundleOption;
                                    }
                                }
                                if($item->getProduct()->getTypeId() == "downloadable"){
                                    $links = Mage::helper("downloadable/catalog_product_configuration")->getLinks($item);
                                    if(count($links) > 0){
                                        $downloadOption = array();
                                        $titles = array();
                                        foreach($links as $link_id)
                                            $titles[] = $link_id->getTitle();
                                        $downloadOption["label"] = Mage::helper("downloadable/catalog_product_configuration")->getLinksTitle($item->getProduct());
                                        $downloadOption["value"] = $titles;
                                        $eachItem["options"][]   = $downloadOption;
                                    }
                                }
                                if(isset($options["options"]))  {
                                    $customOptions = $options["options"];
                                    foreach($customOptions as $customOption) {
                                        $eachCustomOption = array();
                                        $eachCustomOption["label"]   = $customOption["label"];
                                        $eachCustomOption["value"][] = $customOption["print_value"];
                                        $eachItem["options"][] = $eachCustomOption;
                                    }
                                }
                                $eachItem["sku"]       = Mage::helper("core")->stripTags(Mage::helper("core/string")->splitInjection($item->getSku()));
                                $eachItem["price"]     = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getPrice()));
                                $eachItem["qty"]       = $item->getQty()*1;
                                $eachItem["productId"] = $item->getProductId();
                                $eachItem["typeId"]    = $item->getProductType();
                                $eachItem["subTotal"]  = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getRowTotal()));
                                if ($returnArray["displayCartBothPrices"] == 2) {
                                    $eachItem["priceInclTax"]     = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getPriceInclTax()));
                                    $eachItem["subTotalInclTax"]  = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getRowTotalInclTax()));
                                }
                                $eachItem["id"]        = $item->getId();
                                $baseMessages          = $item->getMessage(false);
                                if($baseMessages) {
                                    foreach($baseMessages as $message) {
                                        $messages[] = array(
                                            "text" => $message,
                                            "type" => $item->getHasError() ? "error" : "notice"
                                        );
                                        $eachItem["messages"] = $messages;
                                    }
                                }
                                $returnArray["items"][] = $eachItem;
                            }
                            $returnArray["couponCode"]  = $quote->getCouponCode();
                            $returnArray["isVirtual"]   = $quote->isVirtual();
                            if(Mage::helper("checkout")->isAllowedGuestCheckout($quote))
                                $returnArray["isAllowedGuestCheckout"] = $quote->isAllowedGuestCheckout();
                            if($quote->getItemsQty()*1 > 0){
                                
                                $totals = $quote->getTotals();
                                $subtotal = array(); $discount = array(); $grandtotal = array();
                                if(isset($totals["subtotal"])){
                                    $subtotal = $totals["subtotal"];
                                    $returnArray["subtotal"]["title"] = $subtotal->getTitle();
                                    $returnArray["subtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($subtotal->getValue()));
                                    $returnArray["subtotal"]["unformatedValue"] = $subtotal->getValue();
                                }
                                if(isset($totals["discount"])){
                                    $discount = $totals["discount"];
                                    $returnArray["discount"]["title"] = $discount->getTitle();
                                    $returnArray["discount"]["value"] = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($discount->getValue()));
                                    $returnArray["discount"]["unformatedValue"] = $discount->getValue();
                                }
                                if(isset($totals["shipping"])){
                                    $shipping = $totals["shipping"];
                                    $returnArray["shipping"]["title"] = $shipping->getTitle();
                                    $returnArray["shipping"]["value"] = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($shipping->getValue()));
                                    $returnArray["shipping"]["unformatedValue"] = $shipping->getValue();
                                    $shipingAmt = $quote->getShippingAddress()->getBaseShippingInclTax();
                                    $returnArray["shipping"]["shippingInclTax"] = Mage::getStoreConfig("tax/cart_display/shipping");
                                    $returnArray["shipping"]["valueInclTax"] = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($shipingAmt));
                                    $returnArray["shipping"]["unformatedValueInclTax"] = $shipingAmt;
                                }
                                if(isset($totals["tax"])){
                                    $tax = $totals["tax"];
                                    $returnArray["tax"]["title"] = $tax->getTitle();
                                    $returnArray["tax"]["value"] = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($tax->getValue()));
                                    $returnArray["tax"]["unformatedValue"] = $tax->getValue();
                                }
                                if(isset($totals["grand_total"])){
                                    $grandtotal = $totals["grand_total"];
                                    $returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
                                    $returnArray["grandtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($grandtotal->getValue()));
                                    $returnArray["grandtotal"]["unformatedValue"] = $grandtotal->getValue();
                                }
                            }
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
                        if ($returnArray["cartCount"] > 0)
                        if (!$quote->validateMinimumAmount()) {
                            $minimumOrderAmount =  Mage::getStoreConfig('sales/minimum_order/amount');
                            $minimumOrderAmount = Mage::helper("core")->stripTags(Mage::helper("checkout")->formatPrice($minimumOrderAmount));
                            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                            Mage::getStoreConfig('sales/minimum_order/error_message') :
                            Mage::helper('checkout')->__('Minimum order amount is %s', $minimumOrderAmount);
                            $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumOrderAmount);
                            $returnArray["proceedToCheckout"] = false;
                            $returnArray["responseCode"] = $authData["responseCode"];
                            $returnArray["message"]      = $error;       
                            $returnArray["warning"]      = $warning;                       
                        } 
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function removeCartItemAction()   {
            $returnArray = array();
            $returnArray["authKey"]                       = "";
            $returnArray["responseCode"]                  = 0;
            $returnArray["success"]                       = false;
            $returnArray["message"]                       = "";
            $returnArray["cartCount"]                     = 0;
            $returnArray["subtotal"]["title"]             = "";
            $returnArray["subtotal"]["value"]             = "";
            $returnArray["subtotal"]["unformatedValue"]   = 0.0;
            $returnArray["discount"]["title"]             = "";
            $returnArray["discount"]["value"]             = "";
            $returnArray["discount"]["unformatedValue"]   = 0.0;
            $returnArray["shipping"]["title"]             = "";
            $returnArray["shipping"]["value"]             = "";
            $returnArray["shipping"]["unformatedValue"]   = 0.0;
            $returnArray["tax"]["title"]                  = "";
            $returnArray["tax"]["value"]                  = "";
            $returnArray["tax"]["unformatedValue"]        = 0.0;
            $returnArray["grandtotal"]["title"]           = "";
            $returnArray["grandtotal"]["value"]           = "";
            $returnArray["grandtotal"]["unformatedValue"] = 0.0;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $itemId       = isset($wholeData["itemId"])     ? $wholeData["itemId"]     : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        $quote->removeItem($itemId);
                        $quote->collectTotals()->save();
                        $totals = $quote->getTotals();
                        if(isset($totals["subtotal"])){
                            $subtotal = $totals["subtotal"];
                            $returnArray["subtotal"]["title"] = $subtotal->getTitle();
                            $returnArray["subtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($subtotal->getValue()));
                            $returnArray["subtotal"]["unformatedValue"] = $subtotal->getValue();
                        }
                        if(isset($totals["discount"])){
                            $discount = $totals["discount"];
                            $returnArray["discount"]["title"] = $discount->getTitle();
                            $returnArray["discount"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($discount->getValue()));
                            $returnArray["discount"]["unformatedValue"] = $discount->getValue();
                        }
                        if(isset($totals["shipping"])){
                            $shipping = $totals["shipping"];
                            $returnArray["shipping"]["title"] = $shipping->getTitle();
                            $returnArray["shipping"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($shipping->getValue()));
                            $returnArray["shipping"]["unformatedValue"] = $shipping->getValue();
                        }
                        if(isset($totals["tax"])){
                            $tax = $totals["tax"];
                            $returnArray["tax"]["title"] = $tax->getTitle();
                            $returnArray["tax"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($tax->getValue()));
                            $returnArray["tax"]["unformatedValue"] = $tax->getValue();
                        }
                        if(isset($totals["grand_total"])){
                            $grandtotal = $totals["grand_total"];
                            $returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
                            $returnArray["grandtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($grandtotal->getValue()));
                            $returnArray["grandtotal"]["unformatedValue"] = $grandtotal->getValue();
                        }
                        if($customerId != 0 || $quoteId != 0)
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function emptyCartAction()   {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        $quote->removeAllItems()->collectTotals()->save();
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function wishlistfromCartAction()   {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $itemId       = isset($wholeData["itemId"])     ? $wholeData["itemId"]     : 0;
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                        $quoteCollection->addFieldToFilter("customer_id", $customerId);
                        $quoteCollection->addFieldToFilter("is_active", 1);
                        $quoteCollection->addOrder("updated_at", "desc");
                        $quote = $quoteCollection->getFirstItem();
                        $wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
                        $item = $quote->getItemById($itemId);
                        $productId  = $item->getProductId();
                        $buyRequest = $item->getBuyRequest();
                        $wishlist->addNewItem($productId, $buyRequest);
                        $quote->removeItem($itemId);
                        $quote->collectTotals()->save();
                        $customer = Mage::getModel("customer/customer")->load($customerId);
                        $collection = $wishlist->getItemCollection()->setInStockFilter(true);
                        if(Mage::getStoreConfig("wishlist/wishlist_link/use_qty"))
                            $count = $collection->getItemsQty();
                        else
                            $count = $collection->getSize();
                        $session = Mage::getSingleton("customer/session")->setCustomer($customer);
                        $session->setWishlistDisplayType(Mage::getStoreConfig("wishlist/wishlist_link/use_qty"));
                        $session->setDisplayOutOfStockProducts(Mage::getStoreConfig("cataloginventory/options/show_out_of_stock"));
                        $session->setWishlistItemCount($count);
                        $wishlist->save();
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function applyCouponAction()   {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $quoteId      = isset($wholeData["quoteId"])      ? $wholeData["quoteId"]      : 0;
                        $storeId      = isset($wholeData["storeId"])      ? $wholeData["storeId"]      : 1;
                        $customerId   = isset($wholeData["customerId"])   ? $wholeData["customerId"]   : 0;
                        $couponCode   = isset($wholeData["couponCode"])   ? $wholeData["couponCode"]   : "";
                        $removeCoupon = isset($wholeData["removeCoupon"]) ? $wholeData["removeCoupon"] : false;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        if($removeCoupon)
                            $couponCode = "";
                        $oldCouponCode = $quote->getCouponCode();
                        $codeLength = strlen($couponCode);
                        $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->setCouponCode($isCodeLengthValid ? $couponCode : "")->collectTotals()->save();
                        if($codeLength) {
                            if($isCodeLengthValid && $couponCode == $quote->getCouponCode()){
                                $returnArray["success"] = true;
                                $returnArray["message"] = Mage::helper("core")->__("Coupon code '%s' was applied.", Mage::helper("core")->stripTags($couponCode));
                            }
                            else
                                $returnArray["message"] = Mage::helper("core")->__("Coupon code '%s' is not valid.", Mage::helper("core")->stripTags($couponCode));
                        }
                        else {
                            $returnArray["success"] = true;
                            $returnArray["message"] = Mage::helper("mobikul")->__("Coupon code was canceled.");
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function updateCartAction()   {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $itemIds      = isset($wholeData["itemIds"])    ? $wholeData["itemIds"]    : "[]";
                        $itemQtys     = isset($wholeData["itemQtys"])   ? $wholeData["itemQtys"]   : "[]";
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $itemIds      = Mage::helper("core")->jsonDecode($itemIds);
                        $itemQtys     = Mage::helper("core")->jsonDecode($itemQtys);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        $cartData = array();
                        foreach($itemIds as $key => $value)
                            $cartData[$value] = array("qty" => $itemQtys[$key]);
                        $filter = new Zend_Filter_LocalizedToNormalized(array("locale" => Mage::app()->getLocale()->getLocaleCode()));
                        foreach($cartData as $index => $eachData) {
                            if(isset($eachData["qty"]))
                                $cartData[$index]["qty"] = $filter->filter(trim($eachData["qty"]));
                        }
                        foreach($cartData as $itemId => $itemInfo) {
                            if(!isset($itemInfo["qty"]))
                                continue;
                            $qty = (float) $itemInfo["qty"];
                            $quoteItem = $quote->getItemById($itemId);
                            if(!$quoteItem)
                                continue;
                            $product = $quoteItem->getProduct();
                            if(!$product)
                                continue;
                            $stockItem = $product->getStockItem();
                            if(!$stockItem)
                                continue;
                            $quoteItem->setQty($qty)->save();
                            if ($qty == 0)
                                $quote->removeItem($itemId);
                            else
                                $quoteItem->setQty($qty)->save();
                        }
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->collectTotals()->save();
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function addtoCartAction()    {
            try{
                $returnArray = array();
                $returnArray["authKey"]      = "";
                $returnArray["responseCode"] = 0;
                $returnArray["message"]      = "";
                $returnArray["success"]      = false;
                $returnArray["cartCount"]    = 0;
                $returnArray["quoteId"]      = 0;
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $productId    = isset($wholeData["productId"])  ? $wholeData["productId"]  : 0;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $params       = isset($wholeData["params"])     ? $wholeData["params"]     : "{}";
                        $qty          = isset($wholeData["qty"])        ? $wholeData["qty"]        : 1;
                        $params       = Mage::helper("core")->jsonDecode($params);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $store = Mage::getSingleton("core/store")->load($storeId);
                        $quote = Mage::getModel("sales/quote");
                        if ($quoteId) {
                            $quote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
                            if (is_null($quote->getId())) {
                                $quoteId = 0;
                            }
                        }
                        if($customerId == 0 && $quoteId == 0){
                            $quote = Mage::getModel("sales/quote")
                                ->setStoreId($storeId)
                                ->setIsActive(true)
                                ->setIsMultiShipping(false)
                                ->save();
                            $quote->getBillingAddress();
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->collectTotals()->save();
                            $quoteId = (int)$quote->getId();
                            $returnArray["quoteId"] = $quoteId;
                        }
                        if($qty == 0)
                            $qty = 1;
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $quoteId = $quote->getId();
                            if($quote->getId() < 0 || !$quoteId){
                                $quote = Mage::getModel("sales/quote")
                                    ->setStoreId($storeId)
                                    ->setIsActive(true)
                                    ->setIsMultiShipping(false)
                                    ->save();
                                $quoteId = (int)$quote->getId();
                                $customer = Mage::getModel("customer/customer")->load($customerId);
                                $quote->assignCustomer($customer);
                                $quote->setCustomer($customer);
                                $quote->getBillingAddress();
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                                $quote->collectTotals()->save();
                            }
                        }
                        
                        $product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($productId);
                        if($qty) {
                            $stockData    = Mage::getModel("cataloginventory/stock_item")->loadByProduct($product);
                            $availableQty = $stockData->getQty();
                            if($qty <= $availableQty){
                                $filter = new Zend_Filter_LocalizedToNormalized(array("locale" => Mage::app()->getLocale()->getLocaleCode()));
                                $qty    = $filter->filter($qty);
                            }
                            else{
                                if(!in_array($product->getTypeId(), array("grouped", "configurable", "bundle")) && (bool)$stockData->getManageStock()){
                                    $returnArray["message"] = Mage::helper("mobikul")->__("The requested quantity is not available.");
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                            }
                        }
                        $filesToDelete = array();
                        $paramOption   = array();
                        if(isset($params["options"])){
                            $productOptions = $params["options"];
                            foreach($productOptions as $optionId => $values) {
                                $option     = Mage::getModel("catalog/product_option")->load($optionId);
                                $optionType = $option->getType();
                                if(in_array($optionType, array("multiple", "checkbox"))){
                                    foreach($values as $optionValue)
                                        $paramOption[$optionId][] = $optionValue;
                                }
                                else
                                if(in_array($optionType, array("radio", "drop_down", "area", "field"))){
                                    $paramOption[$optionId] = $values;
                                }
                                else
                                if($optionType == "file"){
// downloading file /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $base64_string = $productOptions[$optionId]["encodeImage"];
                                    $fileName = time().$productOptions[$optionId]["name"];
                                    $fileType = $productOptions[$optionId]["type"];
                                    $fileWithPath = Mage::getBaseDir().DS."media".DS.$fileName;
                                    $ifp = fopen($fileWithPath, "wb");
                                    fwrite($ifp, base64_decode($base64_string));
// assigning file to option /////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $fileOption = array(
                                        "type"       => $fileType,
                                        "title"      => $fileName,
                                        "quote_path" => DS."media".DS.$fileName,
                                        "fullpath"   => $fileWithPath,
                                        "secret_key" => substr(md5(file_get_contents($fileWithPath)), 0, 20)
                                    );
                                    $filesToDelete[] = $fileWithPath;
                                    $paramOption[$optionId] = $fileOption;
                                }
                                else
                                if($optionType == "date"){
                                    $paramOption[$optionId]["day"]      = $values["day"];
                                    $paramOption[$optionId]["year"]     = $values["year"];
                                    $paramOption[$optionId]["month"]    = $values["month"];
                                }
                                else
                                if($optionType == "date_time"){
                                    $paramOption[$optionId]["day"]      = $values["day"];
                                    $paramOption[$optionId]["year"]     = $values["year"];
                                    $paramOption[$optionId]["hour"]     = $values["hour"];
                                    $paramOption[$optionId]["month"]    = $values["month"];
                                    $paramOption[$optionId]["minute"]   = $values["minute"];
                                    $paramOption[$optionId]["day_part"] = $values["day_part"];
                                }
                                else
                                if($optionType == "time"){
                                    $paramOption[$optionId]["hour"]     = $values["hour"];
                                    $paramOption[$optionId]["minute"]   = $values["minute"];
                                    $paramOption[$optionId]["day_part"] = $values["day_part"];
                                }
                            }
                        }
                        if($product->getTypeId() == "downloadable"){
                            if(isset($params["links"]))
                                $params = array("related_product"=>null, "links"=>$params["links"], "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId);
                            else
                                $params = array("related_product"=>null, "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId);
                        }
                        else
                        if($product->getTypeId() == "grouped"){
                            if(isset($params["super_group"]))
                                $params = array("related_product"=>null, "super_group"=>$params["super_group"], "product_id"=>$productId);
                        }
                        else
                        if($product->getTypeId() == "configurable"){
                            if(isset($params["super_attribute"]))
                                $params = array("related_product"=>null, "super_attribute"=>$params["super_attribute"], "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId);
                        }
                        else
                        if($product->getTypeId() == "bundle"){
                            if(isset($params["bundle_option"]) && isset($params["bundle_option_qty"])){
                                Mage::register("product", $product);
                                $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                                    $product->getTypeInstance(true)->getOptionsIds($product), $product
                                );
                                foreach($selectionCollection as $option){
                                    $selection_qty = $option->selection_qty * 1;
                                    $key = $option->option_id;
                                    if(isset($params["bundle_option_qty"][$key]))
                                        $probablyRequestedQty = $params["bundle_option_qty"][$key];
                                    if($selection_qty > 1)
                                        $requestedQty = $selection_qty * $qty;
                                    elseif(isset($probablyRequestedQty))
                                        $requestedQty = $probablyRequestedQty * $qty;
                                    else
                                        $requestedQty = 1;
                                    $associateBundleProduct = Mage::getModel("catalog/product")->load($option->product_id);
                                    $availableQty = Mage::getModel("cataloginventory/stock_item")->loadByProduct($associateBundleProduct)->getQty();
                                    if($associateBundleProduct->getisSalable()){
                                        if($requestedQty > $availableQty){
                                            $returnArray["message"] = Mage::helper("mobikul")->__("The requested quantity of ").$option->name.Mage::helper("mobikul")->__(" is not available.");
                                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                            return;
                                        }
                                    }
                                }
                                $params = array("related_product"=>null, "bundle_option"=>$params["bundle_option"], "bundle_option_qty"=>$params["bundle_option_qty"], "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId);
                            }
                        }
                        else{
                            $params = array("related_product"=>null, "options"=>$paramOption, "qty"=>$qty, "product_id"=>$productId);
                        }
                        $productAdded = Mage::getModel("checkout/cart_product_api")->add($quoteId, array($params), $store);
                        if(!$productAdded){
                            $returnArray["message"] = Mage::helper("mobikul")->__("Unable to add product to cart.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        else {
                            $quote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
                        $quote->setIsActive(true)->save();
                        $returnArray["message"] = Mage::helper("core")->__("%s was added to your shopping cart.", Mage::helper("core")->stripTags($product->getName()));
// delete files uploaded for custom option //////////////////////////////////////////////////////////////////////////////////////
                        foreach($filesToDelete as $eachFile)
                            unlink($eachFile);
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            }
            catch(Mage_Core_Exception $e)   {
                if($e->getCustomMessage() != "")
                    $returnArray["message"] = $e->getCustomMessage();
                else
                    $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
            catch(Exception $e) {
                $returnArray["message"] = Mage::helper("mobikul")->__("Can't add the item to shopping cart.");
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function billingShippingInfoAction(){
            try{
                $returnArray = array();
                $returnArray["authKey"]             = "";
                $returnArray["responseCode"]        = 0;
                $returnArray["message"]             = "";
                $returnArray["success"]             = false;
                $returnArray["isVirtual"]           = false;
                $returnArray["address"]             = array();
                $returnArray["firstName"]           = "";
                $returnArray["lastName"]            = "";
                $returnArray["prefixValue"]         = "";
                $returnArray["middleName"]          = "";
                $returnArray["suffixValue"]         = "";
                $returnArray["countryData"]         = array();
                $returnArray["streetLineCount"]     = 2;
                $returnArray["isPrefixVisible"]     = false;
                $returnArray["isPrefixRequired"]    = false;
                $returnArray["prefixHasOptions"]    = false;
                $returnArray["prefixOptions"]       = array();
                $returnArray["isMiddlenameVisible"] = false;
                $returnArray["isSuffixVisible"]     = false;
                $returnArray["isSuffixRequired"]    = false;
                $returnArray["suffixHasOptions"]    = false;
                $returnArray["suffixOptions"]       = array();
                $returnArray["isDOBVisible"]        = false;
                $returnArray["isDOBRequired"]       = false;
                $returnArray["isTaxVisible"]        = false;
                $returnArray["isTaxRequired"]       = false;
                $returnArray["isGenderVisible"]     = false;
                $returnArray["isGenderRequired"]    = false;
                $returnArray["defaultCountryCode"]  = 'US';
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        $addressIds = array();
                        $returnArray["defaultCountryCode"] = Mage::getStoreConfig("general/country/default");
                        if($customerId != 0){
                            $customer = Mage::getModel("customer/customer")->load($customerId);
                            $address = $customer->getPrimaryBillingAddress();
                            if($address instanceof Varien_Object){
                                $tempbillingAddress = array();
                                $tempbillingAddress["value"] = preg_replace("/(<br\ ?\/?>)+/", ", ", rtrim(preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($address->format("html")))), "<br>"));
                                $tempbillingAddress["id"] = $address->getId();
                                if(!in_array($address->getId(), $addressIds)){
                                    $addressIds[] = $address->getId();
                                    $returnArray["address"][] = $tempbillingAddress;
                                }
                            }
                            $address = $customer->getPrimaryShippingAddress();
                            if($address instanceof Varien_Object){
                                $tempshippingAddress = array();
                                $tempshippingAddress["value"] = preg_replace("/(<br\ ?\/?>)+/", ", ", rtrim(preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($address->format("html")))), "<br>"));
                                $tempshippingAddress["id"] = $address->getId();
                                if(!in_array($address->getId(), $addressIds)){
                                    $addressIds[] = $address->getId();
                                    $returnArray["address"][] = $tempshippingAddress;
                                }
                            }
                            $additionalAddress = $customer->getAdditionalAddresses();
                            foreach($additionalAddress as $eachAdditionalAddress) {
                                if($eachAdditionalAddress instanceof Varien_Object){
                                    $eachAdditionalAddressArray = array();
                                    $eachAdditionalAddressArray["value"] = preg_replace("/(<br\ ?\/?>)+/", ", ", rtrim(preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($eachAdditionalAddress->format("html")))), "<br>"));
                                    $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                                    $returnArray["address"][] = $eachAdditionalAddressArray;
                                }
                            }
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["firstName"]   = $customer->getFirstname();
                            $returnArray["lastName"]    = $customer->getLastname();
                            $returnArray["prefixValue"] = is_null($customer->getPrefix())     ? "" : $customer->getPrefix();
                            $returnArray["middleName"]  = is_null($customer->getMiddlename()) ? "" : $customer->getMiddlename();
                            $returnArray["suffixValue"] = is_null($customer->getSuffix())     ? "" : $customer->getSuffix();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        $DOBVisible = Mage::getStoreConfig("customer/address/dob_show");
                        if($DOBVisible == "req"){
                            $returnArray["isDOBVisible"] = true;
                            $returnArray["isDOBRequired"] = true;
                        }
                        elseif($DOBVisible == "opt")
                            $returnArray["isDOBVisible"] = true;
                        $TaxVisible = Mage::getStoreConfig("customer/address/taxvat_show");
                        if($TaxVisible == "req"){
                            $returnArray["isTaxVisible"] = true;
                            $returnArray["isTaxRequired"] = true;
                        }
                        elseif($TaxVisible == "opt")
                            $returnArray["isTaxVisible"] = true;
                        $GenderVisible = Mage::getStoreConfig("customer/address/gender_show");
                        if($GenderVisible == "req"){
                            $returnArray["isGenderVisible"] = true;
                            $returnArray["isGenderRequired"] = true;
                        }
                        elseif($GenderVisible == "opt")
                            $returnArray["isGenderVisible"] = true;
                        $returnArray["dateFormat"] = Varien_Date::DATE_INTERNAL_FORMAT;
                        $returnArray["isVirtual"] = $quote->isVirtual();
                        $countryCollection = Mage::getModel("directory/country")->getResourceCollection()->loadByStore()->toOptionArray(true);
                        unset($countryCollection[0]);
                        foreach($countryCollection as $country) {
                            $eachCountry = array();
                            $eachCountry["country_id"] = $country["value"];
                            $eachCountry["name"] = $country["label"];
                            $country = Mage::getModel("directory/country")->loadByCode($country["value"]);
                            $result = array();
                            foreach ($country->getRegions() as $region) {
                                $eachRegion = array();
                                $eachRegion["region_id"] = $region->getRegionId();
                                $eachRegion["code"] = $region->getCode();
                                $eachRegion["name"] = $region->getDefaultName();
                                $result[] = $eachRegion;
                            }
                            if(count($result) > 0)
                                $eachCountry["states"] = $result;
                            $returnArray["countryData"][] = $eachCountry;
                        }
                        $returnArray["streetLineCount"] = Mage::helper("customer/address")->getStreetLines();
                        $showPrefix = Mage::getStoreConfig("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"] = true;
                            $returnArray["isPrefixRequired"] = true;
                        }
                        elseif($showPrefix == "opt")
                            $returnArray["isPrefixVisible"] = true;
                        $prefixOptions = Mage::getStoreConfig("customer/address/prefix_options");
                        if($prefixOptions != ""){
                            $returnArray["prefixHasOptions"] = true;
                            $returnArray["prefixOptions"] = explode(";", $prefixOptions);
                        }
                        $showMiddleName = Mage::getStoreConfig("customer/address/middlename_show");
                        if($showMiddleName == 1)
                            $returnArray["isMiddlenameVisible"] = true;
                        $showSuffix = Mage::getStoreConfig("customer/address/suffix_show");
                        if($showSuffix == "req"){
                            $returnArray["isSuffixVisible"] = true;
                            $returnArray["isSuffixRequired"] = true;
                        }
                        elseif($showSuffix == "opt")
                            $returnArray["isSuffixVisible"] = true;
                        $suffixOptions = Mage::getStoreConfig("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"] = explode(";", $suffixOptions);
                        }
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            }
            catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function shippingBillingMethodInfoAction()   {
            try{
                $returnArray                    = array();
                $returnArray["authKey"]         = "";
                $returnArray["responseCode"]    = 0;
                $returnArray["message"]         = "";
                $returnArray["success"]         = false;
                $returnArray["shippingMethods"] = array();
                $returnArray["paymentMethods"]  = array();
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $storeId        = isset($wholeData["storeId"])        ? $wholeData["storeId"]        : 1;
                        $customerId     = isset($wholeData["customerId"])     ? $wholeData["customerId"]     : 0;
                        $taxvat         = isset($wholeData["taxvat"])         ? $wholeData["taxvat"]         : "";
                        $quoteId        = isset($wholeData["quoteId"])        ? $wholeData["quoteId"]        : 0;
                        $checkoutMethod = isset($wholeData["checkoutMethod"]) ? $wholeData["checkoutMethod"] : "";
                        $billingData    = isset($wholeData["billingData"])    ? $wholeData["billingData"]    : "{}";
                        $shippingData   = isset($wholeData["shippingData"])   ? $wholeData["shippingData"]   : "{}";
                        $shippingData   = Mage::helper("core")->jsonDecode($shippingData);
                        $billingData    = Mage::helper("core")->jsonDecode($billingData);
                        $appEmulation   = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $extraInformation = "";
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $quoteId = $quote->getId();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                        $useForShipping = 0;
                        if (!empty($billingData)) {
                            $saveInAddressBook = 0;
                            if(isset($billingData["newAddress"]["saveInAddressBook"]))
                                $saveInAddressBook = $billingData["newAddress"]["saveInAddressBook"];
                            if($checkoutMethod == "register")
                                $saveInAddressBook = 1;
                            if($billingData["useForShipping"] != "")
                                $useForShipping = $billingData["useForShipping"];
                            $addressId = 0;
                            if($billingData["addressId"] != "")
                                $addressId = $billingData["addressId"];
                            $quote->setCheckoutMethod($checkoutMethod)->save();
                            $newAddress = array();
                            if($billingData["newAddress"] != "")
                                if(!empty($billingData["newAddress"]))
                                    $newAddress = $billingData["newAddress"];
                            $address = $quote->getBillingAddress();
                            $addressForm = Mage::getModel("customer/form");
                            $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                            if($addressId > 0) {
                                $customerAddress = Mage::getModel("customer/address")->load($addressId);
                                if($customerAddress->getId()) {
                                    if($customerAddress->getCustomerId() != $quote->getCustomerId()){
                                        $returnArray["message"] = Mage::helper("mobikul")->__("Customer Address is not valid.");
                                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                        return;
                                    }
                                    $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                                    $addressForm->setEntity($address);
                                    $addressErrors = $addressForm->validateData($address->getData());
                                    if($addressErrors !== true){
                                        $returnArray["message"] = implode(", ", $addressErrors);

                                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                        return;
                                    }
                                }
                            }
                            else {
                                
                                $addressForm->setEntity($address);
                                $addressData = array(
                                    "firstname"  => $newAddress["firstName"],
                                    "lastname"   => $newAddress["lastName"],
                                    "middlename" => isset($newAddress["middleName"]) ? $newAddress["middleName"] : "",
                                    "prefix"     => isset($newAddress["prefix"])     ? $newAddress["prefix"]     : "",
                                    "suffix"     => isset($newAddress["suffix"])     ? $newAddress["suffix"]     : "",
                                    "company"    => $newAddress["company"],
                                    "street"     => $newAddress["street"],
                                    "city"       => $newAddress["city"],
                                    "country_id" => $newAddress["country_id"],
                                    "region"     => $newAddress["region"],
                                    "region_id"  => $newAddress["region_id"],
                                    "postcode"   => $newAddress["postcode"],
                                    "telephone"  => $newAddress["telephone"],
                                    "fax"        => $newAddress["fax"],
                                    "taxvat"     => isset($newAddress["taxvat"])     ? $newAddress["taxvat"]     : "",
                                    "dob"        => isset($newAddress["dob"])        ? $newAddress["dob"]        : "",
                                    "gender"     => isset($newAddress["gender"])     ? $newAddress["gender"]     : ""
                                );
                                $addressErrors  = $addressForm->validateData($addressData);
                                if($addressErrors !== true){
                                    $returnArray["message"] = implode(", ", $addressErrors);
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                                $addressForm->compactData($addressData);
                                $address->setCustomerAddressId(null);
                                $address->setSaveInAddressBook($saveInAddressBook);
                                $quote->setCustomerFirstname($newAddress["firstName"])->setCustomerLastname($newAddress["lastName"]);
                            }
                            if(in_array($checkoutMethod, array("register", "guest"))){
                                $websiteId = Mage::getModel("core/store")->load($storeId)->getWebsiteId();
                                if(Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail(trim($newAddress["email"]))->getId() > 0 && $checkoutMethod != "guest"){
                                    $returnArray["message"] = "Email already exist";
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                                $quote->setCustomerEmail(trim($newAddress["email"]));
                                $quote->setCustomerTaxvat($taxvat);
                                $address->setEmail(trim($newAddress["email"]));
                            }
                            if(!$address->getEmail() && $quote->getCustomerEmail()){
                                $address->setEmail($quote->getCustomerEmail());
                            }
                            if(($validateRes = $address->validate()) !== true){
                                $returnArray["message"] = implode(",", $validateRes);
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                            $address->implodeStreetAddress();
                            if(true !== ($result = $this->_validateCustomerData($wholeData))) {
                                $returnArray["message"] = implode(",", $result);
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                            
                            if(!$quote->getCustomerId() && "register" == $quote->getCheckoutMethod()) {
                                if($this->_customerEmailExists($address->getEmail(), Mage::app()->getStore()->getWebsiteId())){
                                    $returnArray["message"] = Mage::helper("mobikul")->__("This email already exist.");
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                            }
                            if(!$quote->isVirtual()) {
                                $usingCase = isset($useForShipping) ? (int)$useForShipping : 0;
                                switch($usingCase) {
                                    case 0:
                                        $shipping = $quote->getShippingAddress();
                                        $shipping->setSameAsBilling(0);
                                        $setStepDataShipping = 0;
                                        break;
                                    case 1:
                                        $billing = clone $address;
                                        $billing->unsAddressId()->unsAddressType();
                                        $shipping = $quote->getShippingAddress();
                                        $shippingMethod = $shipping->getShippingMethod();
                                        $shipping->addData($billing->getData())
                                            ->setSameAsBilling(1)
                                            ->setSaveInAddressBook(0)
                                            ->setShippingMethod($shippingMethod)
                                            ->setCollectShippingRates(true);
                                        $setStepDataShipping = 1;
                                        break;
                                }
                            }
                            $quote->collectTotals()->save();
                            if(!$quote->isVirtual() && $setStepDataShipping)
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                        }
                        else{
                            $returnArray["message"] = Mage::helper("mobikul")->__("Invalid Billing data.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////                            //////////////////////////////////////////////////////
/////////////////////////////////////////////// step 4 process starts here //////////////////////////////////////////////////////
///////////////////////////////////////////////                            //////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if(!$quote->isVirtual()){
                            if($useForShipping == 0){
                                if($shippingData != ""){
                                    $sameAsBilling = 0;
                                    if($shippingData["sameAsBilling"] != "")
                                        $sameAsBilling = $shippingData["sameAsBilling"];
                                    $newAddress = array();
                                    if($shippingData["newAddress"] != "")
                                        if(!empty($shippingData["newAddress"]))
                                            $newAddress = $shippingData["newAddress"];
                                    $addressId = 0;
                                    if($shippingData["addressId"] != "")
                                        $addressId = $shippingData["addressId"];
                                    $saveInAddressBook = 0;
                                    if(isset($shippingData["newAddress"]["saveInAddressBook"]) && $shippingData["newAddress"]["saveInAddressBook"] != "")
                                        $saveInAddressBook = $shippingData["newAddress"]["saveInAddressBook"];
                                    $address = $quote->getShippingAddress();
                                    $addressForm = Mage::getModel("customer/form");
                                    $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                                    if($addressId > 0) {
                                        $customerAddress = Mage::getModel("customer/address")->load($addressId);
                                        if($customerAddress->getId()) {
                                            if($customerAddress->getCustomerId() != $quote->getCustomerId()){
                                                $returnArray["message"] = Mage::helper("mobikul")->__("Customer Address is not valid.");
                                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                                return;
                                            }
                                            $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                                            $addressForm->setEntity($address);
                                            $addressErrors  = $addressForm->validateData($address->getData());
                                            if($addressErrors !== true){
                                                $returnArray["message"] = implode(", ", $addressErrors);
                                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                                return;
                                            }
                                        }
                                    }
                                    else {
                                        $addressForm->setEntity($address);
                                        $addressData = array(
                                            "firstname"  => $newAddress["firstName"],
                                            "lastname"   => $newAddress["lastName"],
                                            "middlename" => isset($newAddress["middleName"]) ? $newAddress["middleName"] : "",
                                            "prefix"     => isset($newAddress["prefix"])     ? $newAddress["prefix"]     : "",
                                            "suffix"     => isset($newAddress["suffix"])     ? $newAddress["suffix"]     : "",
                                            "company"    => $newAddress["company"],
                                            "street"     => $newAddress["street"],
                                            "city"       => $newAddress["city"],
                                            "country_id" => $newAddress["country_id"],
                                            "region"     => $newAddress["region"],
                                            "region_id"  => $newAddress["region_id"],
                                            "postcode"   => $newAddress["postcode"],
                                            "telephone"  => $newAddress["telephone"],
                                            "fax"        => $newAddress["fax"],
                                            "taxvat"     => isset($newAddress["taxvat"])     ? $newAddress["taxvat"]     : "",
                                            "dob"        => isset($newAddress["dob"])        ? $newAddress["dob"]        : "",
                                            "gender"     => isset($newAddress["gender"])     ? $newAddress["gender"]     : ""
                                        );
                                        $addressErrors = $addressForm->validateData($addressData);
                                        if($addressErrors !== true){
                                            $returnArray["message"] = implode(", ", $addressErrors);
                                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                            return;
                                        }
                                        $addressForm->compactData($addressData);
                                        $address->setCustomerAddressId(null);
// Additional form data, not fetched by extractData (as it fetches only attributes) /////////////////////////////////////////////
                                        $address->setSaveInAddressBook($saveInAddressBook);
                                        $address->setSameAsBilling($sameAsBilling);
                                    }
                                    $address->implodeStreetAddress();
                                    $address->setCollectShippingRates(true);
                                    if(($validateRes = $address->validate()) !== true){
                                        $returnArray["message"] = implode(", ", $validateRes);
                                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                        return;
                                    }
                                    $quote->collectTotals()->save();
                                }
                                else{
                                    $returnArray["message"] = Mage::helper("mobikul")->__("Invalid Shipping data.");
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                            }
                            $quote->getShippingAddress()->collectShippingRates()->save();
                            $shippingRateGroups = $quote->getShippingAddress()->getGroupedAllShippingRates();
                            foreach($shippingRateGroups as $code => $rates) {
                                $oneShipping = array();
                                $oneShipping["title"] = Mage::helper("core")->stripTags(Mage::getStoreConfig("carriers/".$code."/title",$storeId));
                                foreach($rates as $rate){
                                    $oneMethod = array();
                                    if($rate->getErrorMessage())
                                        $oneMethod["error"] = $rate->getErrorMessage();
                                    $oneMethod["code"] = $rate->getCode();
                                    $oneMethod["label"] = $rate->getMethodTitle();
                                    $oneMethod["unformattedPrice"] = Mage::helper("core")->currency((float)$rate->getPrice());
                                    $oneMethod["price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency((float)$rate->getPrice()));
                                    $oneShipping["method"][] = $oneMethod;
                                }
                                $returnArray["shippingMethods"][] = $oneShipping;
                            }
                        }
                        foreach(Mage::helper("payment")->getStoreMethods($storeId, $quote) as $method) {
                            if($method->isApplicableToQuote($quote, 1|2|32) && $method->isApplicableToQuote($quote,128)) {
                                $oneMethod = array();
                                $oneMethod["code"] = $method->getCode();
                                $oneMethod["title"] = $method->getTitle();
                                $oneMethod["extraInformation"] = "";
                                if(in_array($method->getCode(), array("paypal_standard", "paypal_express"))){
                                    // if($method->getCode() == "paypal_express")
                                    //     $oneMethod["extraInformation"] = Mage::helper("paypal")->__("You will be redirected to the PayPal website.");
                                    // else
                                        $oneMethod["extraInformation"] = Mage::helper("paypal")->__("You will be redirected to the PayPal website.");
                                    $config = Mage::getModel("paypal/config")->setMethod($method->getCode());
                                    $locale = Mage::app()->getLocale();
                                    $oneMethod["title"] = "";
                                    $oneMethod["link"] = $config->getPaymentMarkWhatIsPaypalUrl($locale);
                                    $oneMethod["imageUrl"] = $config->getPaymentMarkImageUrl($locale->getLocaleCode());
                                }
                                else
                                if(in_array($method->getCode(), array("paypal_express_bml"))){
                                    $oneMethod["extraInformation"] = Mage::helper("paypal")->__("You will be redirected to the PayPal website.");
                                    $oneMethod["title"] = "";
                                    $oneMethod["link"] = "https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html";
                                    $oneMethod["imageUrl"] = "https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png";
                                }
                                else
                                if($method->getCode() == "checkmo"){
                                    if($method->getPayableTo())
                                        $extraInformationPrefix = Mage::helper("mobikul")->__("Make Check payable to:");
                                    else
                                        $extraInformationPrefix = Mage::helper("mobikul")->__("Send Check to:");
                                    $extraInformation = Mage::getStoreConfig("payment/".$method->getCode()."/mailing_address");
                                    if($extraInformation == "")
                                        $extraInformation = " xxxxxxx";
                                    $oneMethod["extraInformation"] = $extraInformationPrefix.$extraInformation;
                                }
                                else
                                if($method->getCode() == "banktransfer"){
                                    $extraInformation = Mage::getStoreConfig("payment/".$method->getCode()."/instructions");
                                    if($extraInformation == "")
                                        $extraInformation = "Bank Details are xxxxxxx";
                                    $oneMethod["extraInformation"] = $extraInformation;
                                }
                                else
                                if($method->getCode() == "cashondelivery"){
                                    $extraInformation = Mage::getStoreConfig("payment/".$method->getCode()."/instructions");
                                    if($extraInformation == "")
                                        $extraInformation = "Pay at the time of delivery";
                                    $oneMethod["extraInformation"] = $extraInformation;
                                }
                                $returnArray["paymentMethods"][] = $oneMethod;
                            }
                        }
                        $returnArray["liveEmail"]    = Mage::getStoreConfig("mobikul/pegseguro/merchant_email");
                        $returnArray["liveToken"]    = Mage::helper("core")->decrypt(Mage::getStoreConfig("payment/rm_pagseguro/token"));
                        $returnArray["sanboxEnable"] = Mage::getStoreConfigFlag("mobikul/pegseguro/sandbox");
                        $returnArray["sandBoxEmail"] = Mage::getStoreConfig("mobikul/pegseguro/sandbox_merchant_email");
                        $returnArray["sandBoxToken"] = Mage::helper("core")->decrypt(Mage::getStoreConfig("mobikul/pegseguro/sandbox_token"));
                        $returnArray["paymentOptionName"] = "Boleto - Você será redirecionado para o site";
                        $returnArray["showInstallmentTotal"] = Mage::getStoreConfigFlag("mobikul/pegseguro/show_total");
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            }
            catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

          public function updateResponseFromPagseguroSDKAction(){
            try{
                $returnArray                 = array();
                $returnArray["authKey"]      = "";
                $returnArray["message"]      = "";
                $returnArray["success"]      = false;
                $returnArray["responseCode"] = 0;
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
Mage::log('---------------------------------updateResponseFromPagseguroSDKAction-----------------------------------',null,'mobikulpagseguro.log');                
Mage::log('---------------------------------Headers-----------------------------------',null,'mobikulpagseguro.log');                
Mage::log($_SERVER,null,'mobikulpagseguro.log');
Mage::log('---------------------------------Params-----------------------------------',null,'mobikulpagseguro.log');              
Mage::log($wholeData,null,'mobikulpagseguro.log');
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $incrementId       = isset($wholeData["incrementId"])       ? $wholeData["incrementId"]       : 0;
                        $responseCode      = isset($wholeData["responseCode"])      ? $wholeData["responseCode"]      : 0;
                        $responseMessage   = isset($wholeData["responseMessage"])   ? $wholeData["responseMessage"]   : "";
                        $responseStatus    = isset($wholeData["responseStatus"])    ? $wholeData["responseStatus"]    : "";
                        $order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
                        $payment = $order->getPayment();
                        if($responseStatus == "FAIL"){
                            if ($order->canUnhold()) {
                                $order->unhold();
                            }
            
                            if ($order->canCancel()) {
                                $order->cancel();
                                $order->save();
                            } else {
                                $order->addStatusHistoryComment(
                                    'Devolvido: o valor foi devolvido ao comprador.'
                                )->save();
                            }
                        } 

                        if ($responseStatus == "SUCCESS") {
                            if(!$order->hasInvoices()){
                                $invoice = $order->prepareInvoice();
                                $invoice->register()->pay();
                                $msg = sprintf('Pagamento capturado. Identificador da Transação: %s', (string)$responseCode);
                                $invoice->addComment($msg);
                                $invoice->sendEmail(
                                    Mage::getStoreConfigFlag('payment/rm_pagseguro/send_invoice_email'),
                                    'Pagamento recebido com sucesso.'
                                );
            
                                // salva o transaction id na invoice
                                if (isset($responseCode)) {
                                    $invoice->setTransactionId((string)$responseCode)->save();
                                }
                                $payment = $order->getPayment();
                                $payment->setTransactionId($responseCode);
                                $payment->setAdditionalInformation(array_merge(array("transaction_id" => $responseCode),$payment->getAdditionalInformation()));
                                $transaction = $payment->addTransaction('capture', null, false, sprintf('Fatura #%s criada com sucesso.', $invoice->getIncrementId()));
                                $transaction->setParentTxnId($responseCode);
                                $transaction->setIsClosed($closed);
                                $transaction->setAdditionalInformation("transaction_id", sprintf('Fatura #%s criada com sucesso.', $invoice->getIncrementId()));
                                $transaction->save();
                                $payment->save();
                                // Mage::getModel('core/resource_transaction')
                                //     ->addObject($invoice)
                                //     ->addObject($invoice->getOrder())
                                //     ->save();
                                $order->addStatusHistoryComment(sprintf('Fatura #%s criada com sucesso.', $invoice->getIncrementId()));
                            } else {
                                $orderIncrementId = $order->getIncrementId();
                                // $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
                                $orderInvoice = '';
                                foreach ($order->getInvoiceCollection() as $invoiceModel) {
                                    $orderInvoice = $invoiceModel;
                                }
                                $invoice = $orderInvoice;
                                if ($invoice != ''){
                                    $order->addStatusHistoryComment(sprintf('Fatura #%s criada com sucesso.', $invoice->getIncrementId()));
                                    $payment = $order->getPayment();
                                    $payment->setTransactionId($responseCode);
                                    $payment->setAdditionalInformation(array_merge(array("transaction_id" => $responseCode),$payment->getAdditionalInformation()));
                                    $payment->save();
                                    $transaction = $payment->addTransaction('capture', null, false, sprintf('Fatura #%s criada com sucesso.', $invoice->getIncrementId()));
                                    $transaction->setParentTxnId($responseCode);
                                    $transaction->setIsClosed($closed);
                                    $transaction->setAdditionalInformation("transaction_id", sprintf('Fatura #%s criada com sucesso.', $invoice->getIncrementId()));
                                    $transaction->save();
                                    $order->save();
                                }
                            }
                            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                                ->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING)
                                    ->save();
                        }
                        $payment->save();
                        $order->save();
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
Mage::log('---------------------------------Response-----------------------------------',null,'mobikulpagseguro.log');              
Mage::log($returnArray,null,'mobikulpagseguro.log');
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
Mage::log('---------------------------------Response-----------------------------------',null,'mobikulpagseguro.log');              
Mage::log($returnArray,null,'mobikulpagseguro.log');
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
Mage::log('---------------------------------Response-----------------------------------',null,'mobikulpagseguro.log');              
Mage::log($returnArray,null,'mobikulpagseguro.log');
                    return;
                }
            }
            catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                 Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
Mage::log('---------------------------------Response-----------------------------------',null,'mobikulpagseguro.log');              
Mage::log($returnArray,null,'mobikulpagseguro.log');
                return;
            }
        }

        public function orderreviewInfoAction(){
            try{
                $returnArray                    = array();
                $returnArray["authKey"]         = "";
                $returnArray["responseCode"]    = 0;
                $returnArray["message"]         = "";
                $returnArray["success"]         = false;
                $returnArray["billingAddress"]  = "";
                $returnArray["shippingAddress"] = "";
                $returnArray["shippingMethod"]  = "";
                $returnArray["billingMethod"]   = "";
                $returnArray["orderReviewData"] = new stdClass();
                $returnArray["currencyCode"]    = "";
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $storeId        = isset($wholeData["storeId"])        ? $wholeData["storeId"]        : 1;
                        $width          = isset($wholeData["width"])          ? $wholeData["width"]          : 1000;
                        $customerId     = isset($wholeData["customerId"])     ? $wholeData["customerId"]     : 0;
                        $quoteId        = isset($wholeData["quoteId"])        ? $wholeData["quoteId"]        : 0;
                        $shippingMethod = isset($wholeData["shippingMethod"]) ? $wholeData["shippingMethod"] : "";
                        $method         = isset($wholeData["method"])         ? $wholeData["method"]         : "";
                        $cc_cid         = isset($wholeData["cc_cid"])         ? $wholeData["cc_cid"]         : "";
                        $cc_exp_month   = isset($wholeData["cc_exp_month"])   ? $wholeData["cc_exp_month"]   : "";
                        $cc_exp_year    = isset($wholeData["cc_exp_year"])    ? $wholeData["cc_exp_year"]    : "";
                        $cc_number      = isset($wholeData["cc_number"])      ? $wholeData["cc_number"]      : "";
                        $cc_type        = isset($wholeData["cc_type"])        ? $wholeData["cc_type"]        : "";
                        $appEmulation   = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
// saving shipping //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($shippingMethod != ""){
                            $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                            if(!$rate){
                                $returnArray["message"] = Mage::helper("mobikul")->__("Invalid shipping method.");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
                        }
//saving payment ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($method != ""){
                            $paymentData = array();
                            $paymentData["method"] = $method;
                            if($cc_cid != "")
                                $paymentData["cc_cid"] = $cc_cid;
                            if($cc_exp_month != "")
                                $paymentData["cc_exp_month"] = $cc_exp_month;
                            if($cc_exp_year != "")
                                $paymentData["cc_exp_year"] = $cc_exp_year;
                            if($cc_number != "")
                                $paymentData["cc_number"] = $cc_number;
                            if($cc_type != "")
                                $paymentData["cc_type"] = $cc_type;
                            if($quote->isVirtual())
                                $quote->getBillingAddress()->setPaymentMethod(isset($method) ? $method : null);
                            else
                                $quote->getShippingAddress()->setPaymentMethod(isset($method) ? $method : null);
                            if(!$quote->isVirtual() && $quote->getShippingAddress())
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                            $paymentData["checks"] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                            $payment = $quote->getPayment()->importData($paymentData);
                            $quote->save();
                        }
                        $orderReviewData = array();
                        foreach($quote->getAllVisibleItems() as $item) {
                            $eachItem = array();
                            $eachItem["productName"] = Mage::helper("core")->stripTags($item->getName());
                            $customoptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                            $result = array();
                            if($customoptions) {
                                if(isset($customoptions["options"]))
                                    $result = array_merge($result, $customoptions["options"]);
                                if(isset($customoptions["additional_options"]))
                                    $result = array_merge($result, $customoptions["additional_options"]);
                                if(isset($customoptions["attributes_info"]))
                                    $result = array_merge($result, $customoptions["attributes_info"]);
                            }
                            if($result){
                                foreach($result as $option){
                                    $eachOption = array();
                                    $eachOption["label"]  = Mage::helper("core")->stripTags($option["label"]);
                                    $eachOption["value"]  = $option["value"];
                                    $eachItem["option"][] = $eachOption;
                                }
                            }
                            $eachItem["price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getCalculationPrice()));
                            $eachItem["qty"] = $item->getQty();
                            $imageData = Mage::helper("mobikul/image")->init($item->getProduct(), "small_image")->keepFrame(true)->resize($width/2.5)->__toString();
                            $eachItem["thumbNail"] = $imageData[0];
                            $eachItem["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                            $eachItem["subTotal"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getRowTotal()));
                            $orderReviewData["items"][] = $eachItem;
                        }
                        $address = $quote->getBillingAddress();
                        if ($address instanceof Varien_Object)
                            $returnArray["billingAddress"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($address->format("html"))));
                        $returnArray["billingMethod"] = $quote->getPayment()->getMethodInstance()->getTitle();
                        if(!$quote->isVirtual()){
                            $address = $quote->getShippingAddress();
                            if ($address instanceof Varien_Object)
                                $returnArray["shippingAddress"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($address->format("html"))));
                            if ($shippingMethod = $quote->getShippingAddress()->getShippingDescription())
                                $returnArray["shippingMethod"] = Mage::helper("core")->stripTags($shippingMethod);
                        }
                        $totals = $quote->getTotals();
                        if(isset($totals["subtotal"])){
                            $subtotal = $totals["subtotal"];
                            $orderReviewData["subtotal"] = array(
                                "title" => $subtotal->getTitle(),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($subtotal->getValue())),
                                "unformatedValue" => $subtotal->getValue()
                            );
                        }
                        if(isset($totals["discount"])){
                            $discount = $totals["discount"];
                            $orderReviewData["discount"] = array(
                                "title" => $discount->getTitle(),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($discount->getValue())),
                                "unformatedValue" => $discount->getValue()
                            );
                        }
                        if(isset($totals["tax"])){
                            $tax = $totals["tax"];
                            $orderReviewData["tax"] = array(
                                "title" => $tax->getTitle(),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($tax->getValue())),
                                "unformatedValue" => $tax->getValue()
                            );
                        }
                        if(isset($totals["shipping"])){
                            $shipping = $totals["shipping"];
                            $orderReviewData["shipping"] = array(
                                "title" => $shipping->getTitle(),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($shipping->getValue())),
                                "unformatedValue" => $shipping->getValue()
                            );
                        }
                        if(isset($totals["grand_total"])){
                            $grandtotal = $totals["grand_total"];
                            $orderReviewData["grandtotal"] = array(
                                "title" => $grandtotal->getTitle(),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($grandtotal->getValue())),
                                "unformatedValue" => $grandtotal->getValue()
                            );
                        }
                        $returnArray["orderReviewData"] = $orderReviewData;
                        $returnArray["currencyCode"]    = Mage::app()->getStore()->getCurrentCurrencyCode();
                        $returnArray["success"]         = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            }
            catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function saveOrderAction(){
            try{
                $returnArray                 = array();
                $returnArray["authKey"]      = "";
                $returnArray["responseCode"] = 0;
                $returnArray["message"]      = "";
                $returnArray["success"]      = false;
                $returnArray["orderId"]      = 0;
                $returnArray["incrementId"]  = 0;
                $returnArray["canReorder"]   = false;
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $quote = new Varien_Object();
                        if($customerId != 0){
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $quoteId = $quote->getId();
                        }
                        if($quoteId != 0)
                            $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
                            
                        if($quote->getCheckoutMethod() == "customer")
                            $customer = Mage::getModel("customer/customer")->load($customerId);
                        if($quote->getCheckoutMethod() == Mage_Checkout_Model_Api_Resource_Customer::MODE_GUEST && !Mage::helper("checkout")->isAllowedGuestCheckout($quote, $quote->getStoreId())){
                            $returnArray["message"] = Mage::helper("mobikul")->__("Guest Checkout is not Enabled.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $customerResource = Mage::getModel("checkout/api_resource_customer");
                        $isNewCustomer = $customerResource->prepareCustomerForQuote($quote);
                        $quote->collectTotals();
                        $service = Mage::getModel("sales/service_quote", $quote);
                        $service->submitAll();
                        if($isNewCustomer)
                            $customerResource->involveNewCustomer($quote);
                        $order = $service->getOrder();
                        if($order) {
                            Mage::dispatchEvent("checkout_type_onepage_save_order_after", array("order"=>$order, "quote"=>$quote));
                            try {
                                $order->sendNewOrderEmail();
                            }
                            catch(Exception $e) {
                                Mage::logException($e);
                            }
                        }
                        Mage::dispatchEvent("checkout_submit_all_after", array("order" => $order, "quote" => $quote));
                        if($order->getPayment()->getMethodInstance()->getCode() == "paypal_standard")   {
                            $totals = $quote->getTotals();
                            if(isset($totals["discount"])){
                                $discount = $totals["discount"];
                                $returnArray["paypalData"]["discount"] = $discount->getValue();
                            }
                            if(isset($totals["tax"])){
                                $tax = $totals["tax"];
                                $returnArray["paypalData"]["tax"] = $tax->getValue();
                            }
                            if(isset($totals["shipping"])){
                                $shipping = $totals["shipping"];
                                $returnArray["paypalData"]["shipping"] = $shipping->getValue();
                            }
                            if(isset($totals["grand_total"])){
                                $grandtotal = $totals["grand_total"];
                                $returnArray["paypalData"]["grandTotal"] = $grandtotal->getValue();
                            }
                            $itemCollection = $quote->getAllVisibleItems();
                            foreach($itemCollection as $item) {
                                $eachItem          = array();
                                $eachItem["sku"]   = Mage::helper("core")->stripTags(Mage::helper("core/string")->splitInjection($item->getSku()));
                                $eachItem["qty"]   = $item->getQty()*1;
                                $eachItem["name"]  = Mage::helper("core")->stripTags($item->getName());
                                $eachItem["price"] = $item->getPrice();
                                $returnArray["paypalData"]["items"][] = $eachItem;
                            }
                            $returnArray["paypalData"]["merchantName"]      = Mage::getStoreConfig("mobikul/paypal/merchant_name");
                            $returnArray["paypalData"]["currencyCode"]    = Mage::app()->getStore()->getCurrentCurrencyCode();
                            $returnArray["paypalData"]["merchantClient"]  = Mage::getStoreConfig("mobikul/paypal/merchant_client");
                            $returnArray["paypalData"]["isSandBoxEnable"] = (bool)Mage::getStoreConfig("mobikul/paypal/sandbox_mode");
                        }
                        else{
                            $returnArray["paypalData"] = new stdClass();
                        }
                        $quote->removeAllItems();
                        $quote->save();
                        $quote->collectTotals()->save();
                        $helper = Mage::helper("mobikul");
                        if($helper->canReorder($order))
                            $returnArray["canReorder"] = $helper->canReorder($order);
                        $quote->collectTotals()->save();
                        $quote->setIsActive(0)->save();
                        $returnArray["orderId"] = $order->getId();
                        $returnArray["incrementId"] = $order->getIncrementId();
                        $returnArray["success"] = true;

                        $my_current_order = Mage::getModel("sales/order")->load($order->getId());
                        $customerData = Mage::getModel('customer/customer')->load($my_current_order->getCustomerId());
                        $returnArray["taxvat"]= $customerData->getData('taxvat');
                        $returnArray["email"]= $customerData->getData('email');
                        $billingData = $my_current_order->getBillingAddress();
                        $returnArray["telephone"] = $billingData->getData('telephone');
                        $streetData = $billingData->getStreet();
                        $returnArray["street1"] = $streetData[0];
                        $returnArray["street2"] = isset($streetData[1]) ? $streetData[1] : "";
                        $returnArray["street3"] = isset($streetData[2]) ? $streetData[2] : "";
                        $returnArray["street4"] = isset($streetData[3]) ? $streetData[3] : "";
                        $returnArray["city"]    = $billingData->getData('city');
                        $region_code            = $billingData-> getRegionCode(); 
                        $returnArray["state"]   = isSet($region_code) ? $region_code : $billingData->getData('region');
                        $returnArray["country"] = $billingData->getData('country_id');
                        $returnArray["postalCode"] = $billingData->getData('postcode');

                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            }
            catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function getTokenUrl() {
            $mode = Mage::getStoreConfig("mobikul/paypal/sandbox_mode");
            if ($mode) {
               return "https://api.sandbox.paypal.com/v1/oauth2/token";
            }
            return "https://api.paypal.com/v1/oauth2/token";
        }

        public function getPaymentUrl() {
            $mode = Mage::getStoreConfig("mobikul/paypal/sandbox_mode");
            if ($mode) {
               return "https://api.sandbox.paypal.com/v1/payments/payment/";
            }
            return "https://api.paypal.com/v1/payments/payment/";
        }


        public function changeOrderStatusAction(){
            try{
                $returnArray                 = array();
                $returnArray["authKey"]      = "";
                $returnArray["responseCode"] = 0;
                $returnArray["message"]      = "";
                $returnArray["success"]      = false;
                $this->getResponse()->setHeader("Content-type", "application/json");
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["authKey"]      = $authData["authKey"];
                        $storeId      = isset($wholeData["storeId"])     ? $wholeData["storeId"]     : 1;
                        $customerId   = isset($wholeData["customerId"])  ? $wholeData["customerId"]  : 0;
                        $incrementId  = isset($wholeData["incrementId"]) ? $wholeData["incrementId"] : "";
                        $status       = isset($wholeData["status"])      ? $wholeData["status"]      : 0;
                        $confirm      = isset($wholeData["confirm"])     ? $wholeData["confirm"]     : "{}";
                        $payId        = isset($wholeData["payId"])     ? $wholeData["payId"]     : "";
                        $state        = isset($wholeData["state"])     ? $wholeData["state"]     : "";
                        $confirm      = Mage::helper("core")->jsonDecode($confirm);
                        
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
                        $transactionId = "";
                        if (count($confirm)) {
                            $payId = $confirm["response"]["id"];
                            $state = $confirm["response"]["state"];
                        }
                        if ($payId != "") {
                            $ch = curl_init();
                            $url = $this->getTokenUrl();
                            $clientId = Mage::getStoreConfig("mobikul/paypal/merchant_client");
                            $secretkey = Mage::getStoreConfig("mobikul/paypal/merchant_secret");
                            
                            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                            curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" .$secretkey);
                            curl_setopt($ch, CURLOPT_URL, $url);            
                            curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: application/x-www-form-urlencoded"));
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS,
                                        "grant_type=client_credentials");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HEADER, false);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            $content= curl_exec($ch);
                            $result = json_decode($content, true);
                            $token = '';
                            curl_close($ch);
                            if (isset($result['access_token'])) {
                                $token = $result['access_token'];
                            }
                            
                            if ($token != '') {
                                $ch = curl_init();
                                $url = $this->getPaymentUrl().$payId;
                                curl_setopt($ch, CURLOPT_URL, $url);  
                                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);                
                                curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" .$secretkey);
                                curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-Type: application/x-www-form-urlencoded","Authorization : Bearer ".$token));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                
                                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                                $content= curl_exec($ch);
                                if (curl_errno($ch) != 0) {
                                    $transactionId = '';
                                } else {
                                    $content = json_decode($content);
                                    $transactionId = $content->transactions[0]->related_resources[0]->sale->id;
                                }
                            }
                        }
                        if ($status == 0){
                            if ($transactionId != "") {
                                $payment = $order->getPayment();
                                $payment->setTransactionId($transactionId)
                                    ->setPreparedMessage("status : ".$state)
                                    ->setShouldCloseParentTransaction(true)
                                    ->setIsTransactionClosed(0)
                                    ->registerCaptureNotification($order->getGrandTotal());
                                $order->save();
                                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                                    ->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING)
                                    ->save();
                            }
                        }
                        else{
                            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED)
                                ->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)
                                ->save();
                        }
                        if($order->canInvoice()){
                            $invoice = Mage::getModel("sales/service_order", $order)->prepareInvoice();
                            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                            $invoice->register();
                            $transactionSave = Mage::getModel("core/resource_transaction")
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder());
                            $transactionSave->save();
                        }
                        $comment = "status :".$confirm["response"]["state"]."<br>";
                        $comment .= "pay id :".$confirm["response"]["id"]."<br>";
                        $comment .= "transaction id :".$transactionId."<br>";
                        $comment .= "date :".$confirm["response"]["create_time"]."<br>";
                        $comment .= "from :".$confirm["client"]["product_name"]."<br>";
                        $order->setIsCustomerNotified(false);
                        $order->addStatusHistoryComment($comment);
                        $order->save();
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    else{
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
                else{
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            }
            catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        protected function _customerEmailExists($email, $websiteId = null)    {
            $customer = Mage::getModel("customer/customer");
            if($websiteId)
                $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($email);
            if($customer->getId())
                return $customer;
            return false;
        }

        protected function _validateCustomerData($data)    {
            $customerForm = Mage::getModel("customer/form")->setFormCode("checkout_register");
            $quote        = new Varien_Object();
            if($data["customerId"] != 0){
                $customerId      = $data["customerId"];
                $quoteCollection = Mage::getModel("sales/quote")
                    ->getCollection()
                    ->addFieldToFilter("customer_id", $customerId)
                    ->addOrder("updated_at", "desc");
                $quote = $quoteCollection->getFirstItem();
            }
            if($data["quoteId"] != 0)
                $quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($data["storeId"]))->load($data["quoteId"]);
            $customerData = array();
            if($quote->getCustomerId()) {
                $customer     = $quote->getCustomer();
                $customerForm->setEntity($customer);
                $customerData = $quote->getCustomer()->getData();
                $newAddress   = array();
                $billingData  = $data["billingData"];
                $billingData  = Mage::helper("core")->jsonDecode($billingData);
                if(isset($billingData["newAddress"])){
                    if(!empty($billingData["newAddress"])){
                        $newAddress = $billingData["newAddress"];
                        $customerData = array(
                            "firstname"  => $newAddress["firstName"],
                            "lastname"   => $newAddress["lastName"],
                            "middlename" => isset($newAddress["middleName"]) ? $newAddress["middleName"] : "",
                            "prefix"     => isset($newAddress["prefix"])     ? $newAddress["prefix"]     : "",
                            "suffix"     => isset($newAddress["suffix"])     ? $newAddress["suffix"]     : "",
                            "taxvat"     => isset($newAddress["taxvat"])     ? $newAddress["taxvat"]     : "",
                            "dob"        => isset($newAddress["dob"])        ? $newAddress["dob"]        : "",
                            "gender"     => isset($newAddress["gender"])     ? $newAddress["gender"]     : "",
                            "email"      => isset($newAddress["email"])      ? $newAddress["email"]      : ""
                        );
                    }
                }
            }
            else {
                $customer    = Mage::getModel("customer/customer");
                $customerForm->setEntity($customer);
                $newAddress  = array();
                $billingData = $data["billingData"];
                $billingData = Mage::helper("core")->jsonDecode($billingData);
                if(isset($billingData["newAddress"]))
                    if(!empty($billingData["newAddress"]))
                        $newAddress = $billingData["newAddress"];
                $customerData = array(
                    "dob"        => isset($newAddress["dob"])        ? $newAddress["dob"]        : "",
                    "email"      => isset($newAddress["email"])      ? $newAddress["email"]      : "",
                    "prefix"     => isset($newAddress["prefix"])     ? $newAddress["prefix"]     : "",
                    "suffix"     => isset($newAddress["suffix"])     ? $newAddress["suffix"]     : "",
                    "taxvat"     => isset($newAddress["taxvat"])     ? $newAddress["taxvat"]     : "",
                    "gender"     => isset($newAddress["gender"])     ? $newAddress["gender"]     : "",
                    "lastname"   => $newAddress["lastName"],
                    "firstname"  => $newAddress["firstName"],
                    "middlename" => isset($newAddress["middleName"]) ? $newAddress["middleName"] : ""
                );
            }
            $customerErrors = $customerForm->validateData($customerData);
            if($customerErrors !== true)
                return array("error" => 1, "message" => implode(", ", $customerErrors));
            if($quote->getCustomerId())
                return true;
            $customerForm->compactData($customerData);
            if($quote->getCheckoutMethod() == "register") {
                $customer->setPassword($data["password"]);
                $customer->setConfirmation($data["confirmPassword"]);
                $customer->setPasswordConfirmation($data["confirmPassword"]);
            }
            else {
                $password = $customer->generatePassword();
                $customer->setPassword($password);
                $customer->setConfirmation($password);
                $customer->setPasswordConfirmation($password);
                $customer->setGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            }
            $result = $customer->validate();
            if(true !== $result && is_array($result))
                return array("error" => -1, "message" => implode(", ", $result));
            if($quote->getCheckoutMethod() == "register")
                $quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
            $quote->getBillingAddress()->setEmail($customer->getEmail());
            $quote->setCustomer($customer);
            Mage::helper("core")->copyFieldset("customer_account", "to_quote", $customer, $quote);
            return true;
        }

    }