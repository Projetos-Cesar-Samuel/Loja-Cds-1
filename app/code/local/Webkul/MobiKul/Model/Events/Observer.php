<?php

    Class Webkul_MobiKul_Model_Events_Observer {
        
        /**
         * This function deletes the entry from the customermobile model
         *
         * @param [type] $observer
         * @return void
         */
        public function deleteCustomer($observer)
            {
                $customerId = $observer->getCustomer()->getId();
                $collection = Mage::getModel("mobikul/customermobile")
                                            ->getCollection()
                                            ->addFieldToFilter("customer_id", $customerId);
                if ( $collection->getSize() > 0 ) {
                    foreach($collection as $customer){ 
                        $model = Mage::getModel("mobikul/customermobile")->load($customer->getId());
                        $model->delete();
                    }
                }
            }



        public function OrderStatusNotification($observer) {
            $order = $observer->getOrder();
            $storeId = $order->getStoreId();
            if ($order->getState() != "") {
                $canReorder = 0;
                $customerApi = Mage::getModel("mobikul/customer_api");
                if($customerApi->canReorder($order) == 1)
                    $canReorder = $customerApi->canReorder($order);
                $message = array(
                    "title"            => Mage::helper("mobikul")->translateInStore($storeId,"Order Status Changed!!"),
                    "message"          => Mage::helper("mobikul")->translateInStore($storeId,"Your order status changed to ").$order->getStatusLabel(),
                    "orderId"          => $order->getId(),
                    "incrementId"      => $order->getIncrementId(),
                    "notificationType" => "order",
                    "canReorder"       => $canReorder,
                    "body"             => Mage::helper("mobikul")->translateInStore($storeId,"Your order status changed to ").$order->getStatusLabel(),
                    "sound"            => "default"
                );
                if($order->getState() == "new"){
                    $message["title"]   = Mage::helper("mobikul")->translateInStore($storeId,"Order Placed Successfully!!");
                    $message["message"] = Mage::helper("mobikul")->translateInStore($storeId,"Your order status is ").$order->getStatusLabel();
                    $iosMsg["body"]     = Mage::helper("mobikul")->translateInStore($storeId,"Your order status is ").$order->getStatusLabel();
                }
                $url = "https://fcm.googleapis.com/fcm/send";
                $authKey = Mage::getStoreConfig("mobikul/notification/apikey");
                $headers = array(
                    "Authorization: key=".$authKey,
                    "Content-Type: application/json",
                );
                if($authKey != "" && !$order->getCustomerIsGuest()){
                    $customerId = 0;
                    if(!$order->getCustomerIsGuest())
                        $customerId = $order->getCustomerId();
                    $tokenCollection = Mage::getModel("mobikul/devicetoken")
                        ->getCollection()
                        ->addFieldToFilter("customer_id", $customerId);
                    foreach ($tokenCollection as $eachToken) {
                        $fields = array(
                            "to"                => $eachToken->getToken(),
                            "data"              => $message,
                            "priority"          => "high",
                            "content_available" => true,
                            "time_to_live"      => 30,
                            "delay_while_idle"  => true
                        );
                        if ($eachToken->getOs() != "android")
                                $fields["notification"] = $message;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, Mage::helper("core")->jsonEncode($fields));
                        $result = Mage::helper("core")->jsonDecode(curl_exec($ch));
                        curl_close($ch);
                        if($result["success"] == 0 && $result["failure"] == 1)
                            $eachToken->delete();
                    }
                }
            }
        }

        public function adminCustomerSaveAfter(){
            // Mage::log(__FUNCTION__);
            // Mage::log(Mage::app()->getRequest()->getParams());
        }

        public function customerSaveAfter($observer){
            if(Mage::getStoreConfig("mobikul/basic/enable_mobile_login") == 1){
                $customer = $observer->getCustomer();
                $params = Mage::app()->getRequest()->getParams();
                $mobileNumber = "";
                if(isset($params["account"]["mobile_number"]))
                    $mobileNumber = $params["account"]["mobile_number"];
                if(isset($params["mobile_number"]))
                    $mobileNumber = $params["mobile_number"];
                if ( $mobileNumber != '' ) {  
                    $customerId = 0;
                    if(Mage::getSingleton("customer/session")->isLoggedIn())
                        $customerId = Mage::getSingleton("customer/session")->getCustomer()->getId();
                    elseif($customer->getId() != 0)
                        $customerId = $customer->getId();
    // checking for existing mobile number //////////////////////////////////////////////////////////////////////////////////////////
                    $collection = Mage::getModel("mobikul/customermobile")
                            ->getCollection()
                            ->addFieldToFilter("mobile", $mobileNumber);
                    $data = $collection->getData();
                    $existingId = 0;
                    if(is_array($data))
                        $existingId = $data[0]["customer_id"];
                    if(count($collection) > 0 && $existingId != $customerId){
                        Mage::getSingleton("core/session")->addError("Mobile number already exist, please provide another number !!");
                    }
                    else
                    if(Mage::getSingleton("customer/session")->isLoggedIn()){
                        $collection = Mage::getModel("mobikul/customermobile")->getCollection()
                            ->addFieldToFilter("customer_id", $customerId);
                        if(count($collection) > 0){
                            foreach($collection as $each){
                                Mage::getModel("mobikul/customermobile")->setMobile($mobileNumber)->setId($each->getId())->save();
                            }
                        }
                        else{
                            Mage::getModel("mobikul/customermobile")->setMobile($mobileNumber)->setCustomerId($customerId)->save();
                        }
                    }
                    elseif($customer->getId() != 0 && !isset($params["mobile"])){
                        $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("customer_id", $customerId);
                        if(count($collection) > 0){
                            foreach($collection as $each){
                                Mage::getModel("mobikul/customermobile")->setMobile($mobileNumber)->setId($each->getId())->save();
                            }
                        }
                        else{
                            Mage::getModel("mobikul/customermobile")->setMobile($mobileNumber)->setCustomerId($customerId)->save();
                        }
                    }
                }
            }
        }

        public function customerRegister($observer){
            // Mage::log(__FUNCTION__);
            // Mage::log(Mage::app()->getRequest()->getParams());
        }

    }