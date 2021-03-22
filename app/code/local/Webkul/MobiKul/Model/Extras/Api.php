<?php

	class Webkul_MobiKul_Model_Extras_Api extends Mage_Api_Model_Resource_Abstract    {

		public function getnotificationList($data)    {
			$returnArray = array();
			$data = json_decode($data);
			$storeId = $data->storeId;
			$appEmulation = Mage::getSingleton("core/app_emulation");
			$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
			$notificationCollection = Mage::getModel("mobikul/notification")->getCollection()->addFieldToFilter("status",1)
				->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
				->setOrder("update_time", "DESC");
			foreach($notificationCollection as $notification){
				$eachNotification = array();
				$eachNotification["id"] = $notification->getId();
				$eachNotification["content"] = $notification->getContent();
				$eachNotification["notificationType"] = $notification->getType();
				$eachNotification["title"] = $notification->getTitle();
				if($notification->getType() == 2){  //for category
					$category = Mage::getModel("catalog/category")->load($notification->getProCatId());
					$eachNotification["categoryName"] = $category->getName();
					$eachNotification["categoryId"] = $notification->getProCatId();
				}
				else
				if($notification->getType() == 1){  //for product
					$product = Mage::getModel("catalog/product")->load($notification->getProCatId());
					$eachNotification["productName"] = $product->getName();
					$eachNotification["productType"] = $product->getTypeId();
					$eachNotification["productId"] = $notification->getProCatId();
				}
				$returnArray[] = $eachNotification;
			}
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			return Mage::helper("core")->jsonEncode($returnArray);
		}

		// public function getrecentNotifications($data)    {
		//     $returnArray = array();
		//     $data = json_decode($data);
		//     $storeId = $data->storeId;
		//     $appEmulation = Mage::getSingleton("core/app_emulation");
		//     $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		//     $dateFrom = date("Y-m-d H:i:s", strtotime("-70 minutes"));
		//     $notificationCollection = Mage::getModel("mobikul/notification")->getCollection()->addFieldToFilter("status",1)
		//         ->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
		//         ->setOrder("update_time","DESC")
		//         ->addFieldToFilter("update_time", array("from" => $dateFrom));
		//     foreach($notificationCollection as $notification){
		//         $eachNotification = array();
		//         $eachNotification["id"] = $notification->getId();
		//         $eachNotification["content"] = $notification->getContent();
		//         $eachNotification["notificationType"] = $notification->getType();
		//         $eachNotification["title"] = $notification->getTitle();
		//         if($notification->getType() == 2){  //for category
		//             $eachNotification["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($notification->getProCatId(), "name", $storeId);
		//             $eachNotification["categoryId"] = $notification->getProCatId();
		//         }
		//         else
		//         if($notification->getType() == 1){  //for product
		//             $eachNotification["productName"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($notification->getProCatId(), "name", $storeId);
		//             $eachNotification["productType"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($notification->getProCatId(), "type_id", $storeId);
		//             $eachNotification["productId"] = $notification->getProCatId();
		//         }
		//         $returnArray[] = $eachNotification;
		//     }
		//     $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		//     return Mage::helper("core")->jsonEncode($returnArray);
		// }

		public function getsearchTerms($data){
			$returnArray = array();
			$data = json_decode($data);
			$storeId = $data->storeId;
			$appEmulation = Mage::getSingleton("core/app_emulation");
			$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
			$termBlock = new Mage_CatalogSearch_Block_Term();
			if(sizeof($termBlock->getTerms()) > 0){
				foreach($termBlock->getTerms() as $_term){
					$eachTerm = array();
					$eachTerm["ratio"] = $_term->getRatio()*70+75;
					$eachTerm["term"] = Mage::helper("core")->stripTags($_term->getName());
					$returnArray[] = $eachTerm;
				}
			}
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			return Mage::helper("core")->jsonEncode($returnArray);
		}

		public function subscribetoNewsletter($data){
			$returnArray = array();
			$data = json_decode($data);
			$websiteId = $data->websiteId;
			$storeId = $data->storeId;
			$email = $data->email;
			$isLoggedIn = $data->isLoggedIn;
			$customerId = $data->customerId;
			$error = 0;$message = "";
			$appEmulation = Mage::getSingleton("core/app_emulation");
			$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
			$ownerId = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($email)->getId();
			if(!Zend_Validate::is($email, "EmailAddress")) {
				$error = 1;
				$message = Mage::helper("mobikul")->__("Please enter a valid email address.");
			}
			else
			if(Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && !$isLoggedIn()) {
				$error = 1;
				$message = Mage::helper("mobikul")->__("Sorry, but administrator denied subscription for guests.");
			}
			else
			if($ownerId !== null && $ownerId != $customerId) {
				$error = 1;
				$message = Mage::helper("mobikul")->__("This email address is already assigned to another user.");
			}
			else{
				$status = Mage::getModel("newsletter/subscriber")->subscribe($email);
				if($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE)
					$message = Mage::helper("mobikul")->__("Confirmation request has been sent.");
				else
					$message = Mage::helper("mobikul")->__("Thank you for your subscription.");
			}
			$returnArray["errorCode"] = $error;
			$returnArray["message"] = $message;
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			return Mage::helper("core")->jsonEncode($returnArray);
		}

		public function registerDevice($data){
			$returnArray = array();
			$data = json_decode($data);
			if(isset($data->token) && $data->token != ""){
				$deviceTokenModel = Mage::getModel("mobikul/devicetoken");
				$deviceCollection = $deviceTokenModel->getCollection()->addFieldToFilter("token", $data->token);
				if($deviceCollection->getSize() == 0){
					$deviceTokenModel->setToken($data->token)->save();
					$returnArray["message"] = Mage::helper("mobikul")->__("Device Registered Succesfully");
					$returnArray["error"] = 0;
					$returnArray["isToken"] = 1;
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else{
					$returnArray["message"] = Mage::helper("mobikul")->__("Device Already Registered");
					$returnArray["error"] = 1;
					$returnArray["isToken"] = 1;
					return Mage::helper("core")->jsonEncode($returnArray);
				}
			}
			else{
				$returnArray["message"] = Mage::helper("mobikul")->__("Please Provide Token");
				$returnArray["error"] = 1;
				$returnArray["isToken"] = 1;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
		}

	}