<?php

	class Webkul_MobiKul_Model_Customer_Api extends Mage_Api_Model_Resource_Abstract    {

		public function logIn($data)    {
			try{
				$data = json_decode($data);
				$username = $data->username;
				$password = $data->password;
				$storeId = $data->storeId;
				$websiteId = $data->websiteId;
				$error = 0;
				$customerModel = Mage::getModel("customer/customer");
				$customer = $customerModel->setWebsiteId($websiteId)->loadByEmail($username);
				if($customer->getId() > 0)  {
					$customer = $customerModel->setWebsiteId($websiteId);
					if($customerModel->getConfirmation() && $customerModel->isConfirmationRequired()) {
						$returnArray["status"] = "false";
						$returnArray["customerName"] = "";
						$returnArray["customerEmail"] = "";
						$returnArray["customerId"] = "";
						$returnArray["cartCount"] = 0;
						$returnArray["message"] = Mage::helper("mobikul")->__("This account is not confirmed.");
						return Mage::helper("core")->jsonEncode($returnArray);
					}
					$hash = $customerModel->getPasswordHash();
					$validatePassword = 0;
					if(!$hash)
						$validatePassword = false;
					$validatePassword = Mage::helper("core")->validateHash($password, $hash);
					if(!$validatePassword) {
						$returnArray["status"] = "false";
						$returnArray["customerName"] = "";
						$returnArray["customerEmail"] = "";
						$returnArray["customerId"] = "";
						$returnArray["cartCount"] = 0;
						$returnArray["message"] = Mage::helper("mobikul")->__("Invalid login or password.");
						return Mage::helper("core")->jsonEncode($returnArray);
					}
					$returnArray = array();
					$returnArray["status"] = "true";
					$returnArray["customerName"] = $customer->getFirstname()." ".$customer->getLastname();
					$returnArray["customerEmail"] = $customer->getEmail();
					$returnArray["customerId"] = $customer->getId();
					$returnArray["cartCount"] = Mage::getModel("sales/quote")->setStoreId($storeId)->loadByCustomer($customer->getId())->getItemsQty()*1;

					if(isset($data->width))
						$width = $data->width;
					else
						$width = 1000;
					$height = $width/2;
					$collection = Mage::getModel("mobikul/userimage")->getCollection()->addFieldToFilter("customer_id", $customer->getId());
					$returnArray["customerBannerImage"] = "";
					$returnArray["customerProfileImage"] = "";
					if($collection->getSize() > 0){
						foreach($collection as $value) {
							if($value->getBanner() != ""){
								$base_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS.$value->getBanner();
								$new_url = "";
								if(file_exists($base_path)){
									$new_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS.$width."x".$height.DS.$value->getBanner();
									$new_url = Mage::getBaseUrl("media")."customerpicture".DS.$customer->getId().DS.$width."x".$height.DS.$value->getBanner();
									if(!file_exists($new_path)) {
										$imageObj = new Varien_Image($base_path);
										$imageObj->keepAspectRatio(false);
										$imageObj->backgroundColor(array(255,255,255));
										$imageObj->keepFrame(false);
										$imageObj->resize($width, $height);
										$imageObj->save($new_path);
									}
								}
								$returnArray["customerBannerImage"] = $new_url;
							}
							if($value->getProfile() != ""){
								$base_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS.$value->getProfile();
								$new_url = "";
								if(file_exists($base_path)){
									$new_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS."100x100".DS.$value->getProfile();
									$new_url = Mage::getBaseUrl("media")."customerpicture".DS.$customer->getId().DS."100x100".DS.$value->getProfile();
									if(!file_exists($new_path)) {
										$imageObj = new Varien_Image($base_path);
										$imageObj->keepAspectRatio(false);
										$imageObj->backgroundColor(array(255,255,255));
										$imageObj->keepFrame(false);
										$imageObj->resize(70, 70);
										$imageObj->save($new_path);
									}
								}
								$returnArray["customerProfileImage"] = $new_url;
							}
						}
					}
					if(isset($data->quoteId)){
						$store = Mage::getSingleton("core/store")->load($storeId);
						$guestQuote = Mage::getModel("sales/quote")->setStore($store)->load($data->quoteId);
						$customerQuote = Mage::getModel("sales/quote")->setStoreId($storeId)->loadByCustomer($customer->getId());
						if($customerQuote->getId() > 0){
							$customerQuote->merge($guestQuote);
							$customerQuote->collectTotals()->save();
						}
						else{
							$guestQuote->assignCustomer($customer);
							$guestQuote->setCustomer($customer);
							$guestQuote->getShippingAddress()->setCollectShippingRates(true);
							$guestQuote->collectTotals()->save();
						}
					}
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else
					$error = 1;
				if($error == 1){
					$returnArray = array();
					$returnArray["status"] = "false";
					$returnArray["customerName"] = "";
					$returnArray["customerEmail"] = "";
					$returnArray["customerId"] = "";
					$returnArray["cartCount"] = 0;
					$returnArray["message"] = Mage::helper("mobikul")->__("Invalid login or password.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function createPost($data)    {
			$data = json_decode($data);
			$returnArray = array();
			$firstName = $data->firstName;
			$lastName  = $data->lastName;
			$emailAddr = $data->emailAddr;
			$password  = $data->password;
			$websiteId = $data->websiteId;
			$storeId   = $data->storeId;
			try{
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				if(!Zend_Validate::is($emailAddr, "EmailAddress")) {
					$returnArray["status"] = "false";
					$returnArray["customerMsg"] = Mage::helper("mobikul")->__("Invalid email address.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				$customerCheck = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($emailAddr);
				if($customerCheck->getId() > 0){
					$returnArray["status"] = "false";
					$returnArray["customerMsg"] = Mage::helper("mobikul")->__("There is already an account with this email address.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				$customer = Mage::getModel("customer/customer");
				$customerForm = Mage::getModel("customer/form");
				$customerForm->setFormCode("customer_account_create");
				$customerForm->setEntity($customer);
				$customerData = array(
					"email" 	 => $emailAddr,
					"firstname"  => $firstName,
					"lastname" 	 => $lastName,
					"password" 	 => $password,
					"website_id" => $websiteId,
					"group_id" 	 => Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId)
				);
				$customerId = Mage::getModel("customer/customer_api")->create($customerData);
				$customer = $customer->load($customerId);
				if($data->isChecked == "true" && isset($data->isChecked))
					$customer->setIsSubscribed(1)->save();
				if(isset($data->quoteId)){
					$store = Mage::getSingleton("core/store")->load($storeId);
					$guestQuote = Mage::getModel("sales/quote")->setStore($store)->load($data->quoteId);
					$customerQuote = Mage::getModel("sales/quote")->setStoreId($storeId)->loadByCustomer($customerId);
					if($customerQuote->getId() > 0){
						$customerQuote->merge($guestQuote);
						$customerQuote->collectTotals()->save();
					}
					else{
						$guestQuote->assignCustomer($customer);
						$guestQuote->setCustomer($customer);
						$guestQuote->getShippingAddress()->setCollectShippingRates(true);
						$guestQuote->collectTotals()->save();
					}
				}
				$returnArray["status"] = "true";
				$returnArray["customerId"] = $customerId;
				$returnArray["customerName"] = $firstName." ".$lastName;
				$returnArray["customerEmail"] = $emailAddr;
				$returnArray["customerMsg"] = Mage::helper("mobikul")->__("Your Account has been successfully created");
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e) {
				Mage::log($e);
			}
		}

		public function forgotpasswordPost($data)   {
			try{
				$data = json_decode($data);
				$emailAddress = $data->emailAddress;
				$storeId = $data->storeId;
				$websiteId = $data->websiteId;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				if($emailAddress) {
					if(!Zend_Validate::is($emailAddress, "EmailAddress")) {
						$returnArray["success"] = 0;
						$returnArray["message"] = Mage::helper("mobikul")->__("Invalid email address.");
						return Mage::helper("core")->jsonEncode($returnArray);
					}
					$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($emailAddress);
					if($customer->getId()) {
						try {
							$newResetPasswordLinkToken = Mage::helper("customer")->generateResetPasswordLinkToken();
							$customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
							$customer->sendPasswordResetConfirmationEmail();
						}
						catch(Exception $exception) {
							$returnArray["success"] = 0;
							$returnArray["message"] = $exception->getMessage();
							return Mage::helper("core")->jsonEncode($returnArray);
						}
					}
					$returnArray["success"] = 1;
					$returnArray["message"] = Mage::helper("customer")->__("If there is an account associated with %s you will receive an email with a link to reset your password.", $emailAddress);
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else {
					$returnArray["success"] = 0;
					$returnArray["message"] = Mage::helper("mobikul")->__("Please enter your email.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function editPost($data){
			try{
				$data = json_decode($data);
				$firstName = $data->firstName;
				$lastName  = $data->lastName;
				$emailAddress = $data->emailAddress;
				$doChangePassword = $data->doChangePassword;
				$customerId = $data->customerId;
				$storeId = $data->storeId;
				$error = 0;$message = "";
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$customer = Mage::getModel("customer/customer")->load($customerId);
				$customerForm = Mage::getModel("customer/form");
				$customerForm->setFormCode("customer_account_edit")->setEntity($customer);
				$customerData = array("firstname" => $firstName,"lastname" => $lastName,"email" => $emailAddress);
				$errors = array();
				$customerErrors = $customerForm->validateData($customerData);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////			   //////////////////////////////////////////////////////////
//////////////////////////////////////////////// For Demo Only //////////////////////////////////////////////////////////
////////////////////////////////////////////////			   //////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				if($customerId == 1){
					$error = 1;
					$message = Mage::helper("mobikul")->__("Sorry you can't change demo account");
					return Mage::helper("core")->jsonEncode(array("error" => $error, "message" => $message));
				}

				if($customerErrors !== true)
					$errors = array_merge($customerErrors, $errors);
				else {
					$customerForm->compactData($customerData);
					$errors = array();
					if($doChangePassword){
						$currentPassword = $data->currentPassword;
						$newPassword = $data->newPassword;
						$confirmPassword = $data->confirmPassword;
						$oldPassword = $customer->getPasswordHash();
						if(Mage::helper("core/string")->strpos($oldPassword, ":") === true)
							list($_salt, $salt) = explode(":", $oldPassword);
						else
							$salt = false;
						if($customer->hashPassword($currentPassword, $salt) == $oldPassword) {
							if(strlen($newPassword)) {
								$customer->setPassword($newPassword);
								$customer->setConfirmation($confirmPassword);
							}
						}
						else{
							$error = 1;
							$message = Mage::helper("mobikul")->__("Invalid current password");
						}
					}
					$customerErrors = $customer->validate();
					if(is_array($customerErrors))
						$errors = array_merge($errors, $customerErrors);
				}
				if($error == 0){
					$customer->setConfirmation(null);
					$customer->save();
					$message = Mage::helper("mobikul")->__("The account information has been saved.");
				}
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode(array("error" => $error, "message" => $message));
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getdashboardData($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$storeId = $data->storeId;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$returnArray["welcomeMsg"] = "Hello ".$customer->getFirstname()." ".$customer->getLastname()."!";
				$orders = Mage::getResourceModel("sales/order_collection")
					->addAttributeToSelect("*")
					->joinAttribute("shipping_firstname", "order_address/firstname", "shipping_address_id", null, "left")
					->joinAttribute("shipping_lastname", "order_address/lastname", "shipping_address_id", null, "left")
					->addAttributeToFilter("customer_id", $customer->getId())
					->addAttributeToFilter("state", array("in" => Mage::getSingleton("sales/order_config")->getVisibleOnFrontStates()))
					->addAttributeToSort("created_at", "desc")
					->setPageSize(5)
					->load();
				$recentOrders = array();
				foreach($orders as $key => $_order) {
					$eachRecentOrder = array();
					$eachRecentOrder["order_id"] = $_order->getRealOrderId();
					$eachRecentOrder["date"] = Mage::helper("core")->formatDate($_order->getCreatedAtStoreDate());
					$eachRecentOrder["ship_to"] = $_order->getShippingAddress() ? Mage::helper("core")->stripTags($_order->getShippingAddress()->getName()) : " ";
					$eachRecentOrder["order_total"] = Mage::helper("core")->stripTags($_order->formatPrice($_order->getGrandTotal()));
					$eachRecentOrder["status"] = $_order->getStatusLabel();
					if($this->canReorder($_order) == 1)
						$eachRecentOrder["canReorder"] = $this->canReorder($_order);
					else
						$eachRecentOrder["canReorder"] = 0;
					$recentOrders[] = $eachRecentOrder;
				}
				$returnArray["recentOrders"] = $recentOrders;
				$returnArray["customerName"] = $customer->getFirstname()." ".$customer->getLastname();
				$returnArray["customerEmail"] = $customerEmail;
				$isSubscribed = Mage::getModel("newsletter/subscriber")->loadByCustomer($customer)->isSubscribed();
				if($isSubscribed)
					$returnArray["subscriptionMsg"] = Mage::helper("mobikul")->__("You are currently subscribed to 'General Subscription'.");
				else
					$returnArray["subscriptionMsg"] = Mage::helper("mobikul")->__("You are currently not subscribed to any newsletter.");
				$address = $customer->getPrimaryBillingAddress();
				if($address instanceof Varien_Object){
					$returnArray["billingAddress"] = $address->getFirstname()." ".$address->getLastname()."\n";
					foreach($address->getStreet() as $street)
						$returnArray["billingAddress"] .= $street."\n";
					$returnArray["billingAddress"] .= $address->getCity().", ".$address->getRegion().", ".$address->getPostcode()."\n".Mage::getModel("directory/country")->load($address->getCountryId())->getName()."\n"."T:".$address->getTelephone();
					$returnArray["billingId"] = $address->getId();
				}
				else{
					$returnArray["billingAddress"] = Mage::helper("mobikul")->__("You have not set a default billing address.");
					$returnArray["billingId"] = "";
				}
				$address = $customer->getPrimaryShippingAddress();
				if($address instanceof Varien_Object){
					$returnArray["shippingAddress"] = $address->getFirstname()." ".$address->getLastname()."\n";
					foreach($address->getStreet() as $street)
						$returnArray["shippingAddress"] .= $street."\n";
					$returnArray["shippingAddress"] .= $address->getCity().", ".$address->getRegion().", ".$address->getPostcode()."\n".Mage::getModel("directory/country")->load($address->getCountryId())->getName()."\n"."T:".$address->getTelephone();
					$returnArray["shippingId"] = $address->getId();
				}
				else{
					$returnArray["shippingAddress"] = Mage::helper("mobikul")->__("You have not set a default shipping address.");
					$returnArray["shippingId"] = "";
				}
				$reviewCollection = Mage::getModel("review/review")->getProductCollection()
					->addStoreFilter($storeId)
					->addCustomerFilter($customer->getId())
					->setDateOrder()
					->setPageSize(5)
					->load()
					->addReviewSummary();
				$recentReviews = array();
				foreach($reviewCollection as $key => $_review){
					$eachRecentReview = array();
					$eachRecentReview["name"] = Mage::helper("core")->stripTags($_review->getName());
					if($_review->getCount() > 0)
						$eachRecentReview["rating"] = number_format((5*($_review->getSum() / $_review->getCount()))/100, 2, ".", "");
					else
						$eachRecentReview["rating"] = 0;
					$recentReviews[] = $eachRecentReview;
				}
				$returnArray["recentReview"] = $recentReviews;
				if(isset($data->width))
					$width = $data->width;
				else
					$width = 1000;
				$height = $width/2;
				$collection = Mage::getModel("mobikul/userimage")->getCollection()->addFieldToFilter("customer_id", $customer->getId());
				$returnArray["customerBannerImage"] = "";
				$returnArray["customerProfileImage"] = "";
				if($collection->getSize() > 0){
					foreach($collection as $value) {
						if($value->getBanner() != ""){
							$base_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS.$value->getBanner();
							if(file_exists($base_path)){
								$new_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS.$width."x".$height.DS.$value->getBanner();
								$new_url = Mage::getBaseUrl("media")."customerpicture".DS.$customer->getId().DS.$width."x".$height.DS.$value->getBanner();
								if(!file_exists($new_path)) {
									$imageObj = new Varien_Image($base_path);
									$imageObj->keepAspectRatio(false);
									$imageObj->backgroundColor(array(255,255,255));
									$imageObj->keepFrame(false);
									$imageObj->resize($width, $height);
									$imageObj->save($new_path);
								}
							}
							$returnArray["customerBannerImage"] = $new_url;
						}
						if($value->getProfile() != ""){
							$base_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS.$value->getProfile();
							if(file_exists($base_path)){
								$new_path = Mage::getBaseDir("media").DS."customerpicture".DS.$customer->getId().DS."100x100".DS.$value->getProfile();
								$new_url = Mage::getBaseUrl("media")."customerpicture".DS.$customer->getId().DS."100x100".DS.$value->getProfile();
								if(!file_exists($new_path)) {
									$imageObj = new Varien_Image($base_path);
									$imageObj->keepAspectRatio(false);
									$imageObj->backgroundColor(array(255,255,255));
									$imageObj->keepFrame(false);
									$imageObj->resize($width, $height);
									$imageObj->save($new_path);
								}
							}
							$returnArray["customerProfileImage"] = $new_url;
						}
					}
				}
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getaccountinfoData($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$returnArray["firstName"] = $customer->getFirstname();
				$returnArray["lastName"] = $customer->getLastname();
				$returnArray["email"] = $customer->getEmail();
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getaddrbookData($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$address = $customer->getPrimaryBillingAddress();
				if($address instanceof Varien_Object){
					$returnArray["billingAddress"]["value"] = $address->getFirstname()." ".$address->getLastname()."\n";
					foreach($address->getStreet() as $street)
						$returnArray["billingAddress"]["value"] .= $street."\n";
					$returnArray["billingAddress"]["value"] .= $address->getCity().", ".$address->getRegion().", ".$address->getPostcode()."\n".Mage::getModel("directory/country")->load($address->getCountryId())->getName()."\n"."T:".$address->getTelephone();
					$returnArray["billingAddress"]["id"] = $address->getId();
				}
				else{
					$returnArray["billingAddress"]["value"] = Mage::helper("mobikul")->__("You have not set a default billing address.");
					$returnArray["billingAddress"]["id"] = "";
				}
				$address = $customer->getPrimaryShippingAddress();
				if($address instanceof Varien_Object){
					$returnArray["shippingAddress"]["value"] = $address->getFirstname()." ".$address->getLastname()."\n";
					foreach($address->getStreet() as $street)
						$returnArray["shippingAddress"]["value"] .= $street."\n";
					$returnArray["shippingAddress"]["value"] .= $address->getCity().", ".$address->getRegion().", ".$address->getPostcode()."\n".Mage::getModel("directory/country")->load($address->getCountryId())->getName()."\n"."T:".$address->getTelephone();
					$returnArray["shippingAddress"]["id"] = $address->getId();
				}
				else{
					$returnArray["shippingAddress"]["value"] = Mage::helper("mobikul")->__("You have not set a default shipping address.");
					$returnArray["shippingAddress"]["id"] = "";
				}
				$additionalAddress = $customer->getAdditionalAddresses();
				foreach($additionalAddress as $key => $eachAdditionalAddress) {
					if($eachAdditionalAddress instanceof Varien_Object){
						$eachAdditionalAddressArray = array();
						$eachAdditionalAddressArray["value"] = $eachAdditionalAddress->getFirstname()." ".$eachAdditionalAddress->getLastname()."\n";
						foreach($eachAdditionalAddress->getStreet() as $street)
							$eachAdditionalAddressArray["value"] .= $street."\n";
						$eachAdditionalAddressArray["value"] .= $eachAdditionalAddress->getCity().", ".$eachAdditionalAddress->getRegion().", ".$eachAdditionalAddress->getPostcode()."\n".Mage::getModel("directory/country")->load($eachAdditionalAddress->getCountryId())->getName()."\n"."T:".$eachAdditionalAddress->getTelephone();
						$eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
					}
					else{
						$eachAdditionalAddressArray["value"] = Mage::helper("mobikul")->__("You have no additional address.");
						$eachAdditionalAddressArray["id"] = "";
					}
					$returnArray["additionalAddress"][] = $eachAdditionalAddressArray;
				}
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function canReorder(Mage_Sales_Model_Order $order)    {
			if (!Mage::getStoreConfig("sales/reorder/allow", $order->getStore()))
				return 0;
			if(1)   //customer always login
				return $order->canReorder();
			else
				return 1;
		}

		public function getallOrders($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$storeId = $data->storeId;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$orders = Mage::getResourceModel("sales/order_collection")
					->addFieldToSelect("*")
					->addFieldToFilter("customer_id", $customer->getId())
					->addFieldToFilter("state", array("in" => Mage::getSingleton("sales/order_config")->getVisibleOnFrontStates()))
					->setOrder("created_at", "DESC");
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $orders->getSize();
					$orders->setPageSize(16)->setCurPage($pageNumber);
				}
				$allOrders = array();
				foreach($orders as $key => $_order) {
					$eachOrder = array();
					$eachOrder["id"] = $key;
					$eachOrder["order_id"] = $_order->getRealOrderId();
					$eachOrder["date"] = Mage::helper("core")->formatDate($_order->getCreatedAtStoreDate());
					$eachOrder["ship_to"] = $_order->getShippingAddress() ? Mage::helper("core")->stripTags($_order->getShippingAddress()->getName()) : " ";
					$eachOrder["order_total"] = Mage::helper("core")->stripTags($_order->formatPrice($_order->getGrandTotal()));
					$eachOrder["status"] = $_order->getStatusLabel();
					if($this->canReorder($_order) == 1)
						$eachOrder["canReorder"] = $this->canReorder($_order);
					else
						$eachOrder["canReorder"] = 0;
					$allOrders[] = $eachOrder;
				}
				$returnArray["allOrders"] = $allOrders;
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getproReviews($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$storeId = $data->storeId;
				$websiteId = $data->websiteId;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$reviews =  Mage::getModel("review/review")->getProductCollection()
								->addStoreFilter($storeId)
								->addCustomerFilter($customer->getId())
								->setDateOrder();
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $reviews->getSize();
					$reviews->setPageSize(16)->setCurPage($pageNumber);
				}
				$allReviews = array();
				foreach($reviews as $key => $_review){
					$eachReview = array();
					$eachReview["date"] = Mage::helper("core")->formatDate($_review->getReviewCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
					$eachReview["id"] = $key;
					if(isset($data->width)){
						$width = $data->width;
						$_product = Mage::getModel("catalog/product")->load($_review->getId());
						$eachReview["thumbNail"] = Mage::helper("catalog/image")->init($_product, "small_image")->keepFrame(true)->resize($width/3)->__toString();
					}
					$eachReview["typeId"] = $_review->getTypeId();
					$eachReview["productId"] = $_review->getId();
					$eachReview["proName"] = Mage::helper("core")->stripTags($_review->getName());
					$eachReview["details"] = Mage::helper("core/string")->truncate(Mage::helper("core")->stripTags($_review->getDetail()),50);
					$allReviews[] = $eachReview;
				}
				$returnArray["allReviews"] = $allReviews;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getwishlistData($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$storeId = $data->storeId;
				$width = $data->width;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer, true);
				$wishListItemCollection = $wishlist->getItemCollection();
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $wishListItemCollection->getSize();
					$wishListItemCollection->setPageSize(16)->setCurPage($pageNumber);
				}
				$wishlistData = array();
				foreach($wishListItemCollection as $item)    {
					$eachWishData = array();
					$eachWishData["id"] = $item->getId();
					$eachWishData["name"] = $item->getProduct()->getName();
					$eachWishData["description"] = $item->getDescription();
					$eachWishData["sku"] = $item->getProduct()->getSku();
					$eachWishData["productId"] = $item->getProduct()->getId();
					$eachWishData["typeId"] = $item->getProduct()->getTypeId();
					$eachWishData["qty"] = $item->getQty()*1;
					$eachWishData["price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getProduct()->getPrice()));
					$eachWishData["thumbNail"] = Mage::helper("catalog/image")->init($item->getProduct(), "small_image")->keepFrame(true)->resize($width/3)->__toString();
					$customoptions = Mage::helper("catalog/product_configuration")->getOptions($item);
					if(count($customoptions) > 0)
						$eachWishData["option"] = $customoptions;
					$reviews = Mage::getModel("review/review")->getResourceCollection()->addStoreFilter($storeId)
							->addEntityFilter("product", $item->getProduct()->getId())->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
							->setDateOrder()->addRateVotes();
					$ratings = array();
					if(count($reviews) > 0) {
						foreach($reviews->getItems() as $review) {
							foreach($review->getRatingVotes() as $vote)
								$ratings[] = $vote->getPercent();
						}
					}
					if(count($ratings) > 0)
						$rating = number_format((5*(array_sum($ratings) / count($ratings)))/100, 2, ".", "");
					else
						$rating = 0;
					$eachWishData["rating"] = $rating;
					$wishlistData[] = $eachWishData;
				}
				$returnArray["wishlistData"] = $wishlistData;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function updatewishList($data)	{
			try{
				$data = json_decode($data);
				$customerId = $data->customerId;
				$itemData = $data->itemData;
				$wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
				$updatedItems = 0;
				foreach($itemData as $eachItem) {
					$item = Mage::getModel("wishlist/item")->load($eachItem->id);
					if($item->getWishlistId() != $wishlist->getId())
						continue;
					$description = (string)$eachItem->description;
					if($description == Mage::helper("wishlist")->defaultCommentString())
						$description = "";
					else
					if(!strlen($description))
						$description = $item->getDescription();
					$qty = null;
					if(isset($eachItem->qty))
						$qty = $eachItem->qty;
					if(is_null($qty)) {
						$qty = $item->getQty();
						if(!$qty)
							$qty = 1;
					}
					else
					if(0 == $qty) {
						try {
							$item->delete();
						}
						catch(Exception $e) {
							$returnArray = array();
							$returnArray["success"] = 0;
							$returnArray["message"] = Mage::helper("mobikul")->__("Can't delete item from wishlist");
							return Mage::helper("core")->jsonEncode($returnArray);
						}
					}
					if(($item->getDescription() == $description) && ($item->getQty() == $qty))
						continue;
					try {
						$item->setDescription($description)->setQty($qty)->save();
						$updatedItems++;
					}
					catch(Exception $e) {
						$returnArray = array();
						$returnArray["success"] = 0;
						$returnArray["message"] = Mage::helper("core")->__("Can't save description %s", Mage::helper("core")->escapeHtml($description));
						return Mage::helper("core")->jsonEncode($returnArray);
					}
				}
				if($updatedItems) {
					try {
						$wishlist->save();
						Mage::helper("wishlist")->calculate();
					}
					catch(Exception $e) {
						$returnArray = array();
						$returnArray["success"] = 0;
						$returnArray["message"] = Mage::helper("mobikul")->__("Can't update wishlist");
						return Mage::helper("core")->jsonEncode($returnArray);
					}
				}
				$returnArray["success"] = 1;
				$returnArray["message"] = Mage::helper("mobikul")->__("Wishlist updated successfully");
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function isSubscribed($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$returnArray["isSubscribed"] = Mage::getModel("newsletter/subscriber")->loadByCustomer($customer)->isSubscribed();
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getmyDownloads($data){
			try{
				$data = json_decode($data);
				$customerEmail = $data->customerEmail;
				$websiteId = $data->websiteId;
				$customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($customerEmail);
				$returnArray = array();
				$purchased = Mage::getResourceModel("downloadable/link_purchased_collection")
					->addFieldToFilter("customer_id", $customer->getId())
					->addOrder("created_at", "desc");
				$purchasedIds = array();
				foreach($purchased as $_item)
					$purchasedIds[] = $_item->getId();
				if(empty($purchasedIds))
					$purchasedIds = array(null);
				$purchasedItems = Mage::getResourceModel("downloadable/link_purchased_item_collection")
					->addFieldToFilter("purchased_id", array("in" => $purchasedIds))
					->addFieldToFilter("status", array("nin" => array(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT,Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW)))
					->setOrder("item_id", "desc");
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $purchasedItems->getSize();
					$purchasedItems->setPageSize(16)->setCurPage($pageNumber);
				}
				foreach ($purchasedItems as $item)
					$item->setPurchased($purchased->getItemById($item->getPurchasedId()));
				$allDownloads = array();
				foreach($purchasedItems as $key => $downloads) {
					$eachDownloads = array();
					$eachDownloads["incrementId"] = $incrementId = $downloads->getPurchased()->getOrderIncrementId();
					$_order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
					if($_order->getRealOrderId() > 0)
						$eachDownloads["isOrderExist"] = 1;
					else{
						$eachDownloads["isOrderExist"] = 0;
						$eachDownloads["message"] = Mage::helper("mobikul")->__("Sorry This Order Does not Exist!!");
					}
					$eachDownloads["hash"] = $downloads->getLinkHash();
					$eachDownloads["date"] = Mage::helper("core")->formatDate($downloads->getPurchased()->getCreatedAt());
					$eachDownloads["proName"] = Mage::helper("core")->stripTags($downloads->getPurchased()->getProductName());
					$eachDownloads["status"] = $downloads->getStatus();
					if($downloads->getNumberOfDownloadsBought())
						$eachDownloads["remainingDownloads"] = $downloads->getNumberOfDownloadsBought() - $downloads->getNumberOfDownloadsUsed();
					else
						$eachDownloads["remainingDownloads"] = Mage::helper("mobikul")->__("Unlimited");
					if($this->canReorder($_order) == 1)
						$eachDownloads["canReorder"] = $this->canReorder($_order);
					else
						$eachDownloads["canReorder"] = 0;
					$allDownloads[] = $eachDownloads;
				}
				$returnArray["allDownloads"] = $allDownloads;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function downloadProduct($data){
			try{
				$data = json_decode($data);
				$customerId = $data->customerId;
				$sessionId = $data->sessionId;
				$hash = $data->hash;
				$returnArray = array();
				$linkPurchasedItem = Mage::getModel("downloadable/link_purchased_item")->load($hash, "link_hash");
				if(!$linkPurchasedItem->getId()) {
					$returnArray["success"] = 0;
					$returnArray["message"] = Mage::helper("mobikul")->__("Requested link does not exist.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				if(!Mage::helper("downloadable")->getIsShareable($linkPurchasedItem)) {
					$linkPurchased = Mage::getModel("downloadable/link_purchased")->load($linkPurchasedItem->getPurchasedId());
					if($linkPurchased->getCustomerId() != $customerId) {
						$returnArray["success"] = 0;
						$returnArray["message"] = Mage::helper("mobikul")->__("Requested link does not exist.");
						return Mage::helper("core")->jsonEncode($returnArray);
					}
				}
				$downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();
				$status = $linkPurchasedItem->getStatus();
				if($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)) {
					$resource = "";
					$resourceType = "";
					if($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
						$returnArray["url"] = $linkPurchasedItem->getLinkUrl();
						$fileArray = explode(DS, $linkPurchasedItem->getLinkUrl());
						$returnArray["fileName"] = end($fileArray);
					}
					else
					if($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
						$linkFile = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile());
						if(file_exists($linkFile)){
							$returnArray["url"] = Mage::getUrl("mobikul/download/index", array("hash" => $hash, "sessionId" => $sessionId));
							$fileArray = explode(DS, $linkFile);
							$returnArray["fileName"] = end($fileArray);
						}
						else{
							$returnArray["success"] = 0;
							$returnArray["message"] = Mage::helper("mobikul")->__("An error occurred while getting the requested content. Please contact the store owner.");
							return Mage::helper("core")->jsonEncode($returnArray);
						}
					}
				}
				else
				if($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
					$returnArray["success"] = 0;
					$returnArray["message"] = Mage::helper("mobikul")->__("The link has expired.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else
				if($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING || $status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW) {
					$returnArray["success"] = 0;
					$returnArray["message"] = Mage::helper("mobikul")->__("The link is not available.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else {
					$returnArray["success"] = 0;
					$returnArray["message"] = Mage::helper("mobikul")->__("An error occurred while getting the requested content. Please contact the store owner.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				$returnArray["success"] = 1;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getorderDetail($data){
			try{
				$data = json_decode($data);
				$incrementId = $data->incrementId;
				$_order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
				$returnArray = array();
				$returnArray["incrementId"] = $_order->getRealOrderId();
				$returnArray["statusLabel"] = $_order->getStatusLabel();
				$returnArray["orderDate"] = Mage::helper("core")->formatDate($_order->getCreatedAtStoreDate(), "long");
				if($_order->getShippingAddressId()){
					// shipping address
					$shippingAddress = Mage::getModel("sales/order_address")->load($_order->getShippingAddressId());
					$shippingAddressData[] = $shippingAddress->getFirstname()." ".$shippingAddress->getLastname();
					$shippingStreet = $shippingAddress->getStreet();
					$shippingAddressData[] = $shippingStreet[0];
					if(count($shippingStreet) > 1)
						if($shippingStreet[1])
							$shippingAddressData[] = $shippingStreet[1];
					$shippingAddressData[] = $shippingAddress->getCity().", ".$shippingAddress->getRegion().", ".$shippingAddress->getPostcode();
					$shippingAddressData[] = Mage::getModel("directory/country")->load($shippingAddress->getCountryId())->getName();
					$shippingAddressData[] = "T: ".$shippingAddress->getTelephone();
					$returnArray["shippingAddress"] = $shippingAddressData;
					if($_order->getShippingDescription())
						$returnArray["shippingMethod"] = Mage::helper("core")->stripTags($_order->getShippingDescription());
					else
						$returnArray["shippingMethod"] = Mage::helper("mobikul")->__("No shipping information available");
				}
				// billing address
				$billingAddress = Mage::getModel("sales/order_address")->load($_order->getBillingAddressId());
				$billingAddressData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
				$billingStreet = $billingAddress->getStreet();
				$billingAddressData[] = $billingStreet[0];
				if(count($billingStreet) > 1)
					if($billingStreet[1])
						$billingAddressData[] = $billingStreet[1];
				$billingAddressData[] = $billingAddress->getCity().", ".$billingAddress->getRegion().", ".$billingAddress->getPostcode();
				$billingAddressData[] = Mage::getModel("directory/country")->load($billingAddress->getCountryId())->getName();
				$billingAddressData[] = "T: ".$billingAddress->getTelephone();
				$returnArray["billingAddress"] = $billingAddressData;
				$returnArray["billingMethod"] = $_order->getPayment()->getMethodInstance()->getTitle();
				$itemCollection = $_order->getAllVisibleItems();
				foreach($itemCollection as $item) {
					$eachItem = array();
					$eachItem["name"] = $item->getName();
					$result = array();
					if($options = $item->getProductOptions()) {
						if(isset($options["options"]))
							$result = array_merge($result, $options["options"]);
						if(isset($options["additional_options"]))
							$result = array_merge($result, $options["additional_options"]);
						if(isset($options["attributes_info"]))
							$result = array_merge($result, $options["attributes_info"]);
					}
					if($result){
						foreach($result as $_option){
							$eachOption = array();
							$eachOption["label"] = Mage::helper("core")->stripTags($_option["label"]);
							$eachOption["value"] = $_option["value"];
							$eachItem["option"][] = $eachOption;
						}
					}
					$eachItem["sku"] = Mage::helper("core")->stripTags(Mage::helper("core/string")->splitInjection($item->getSku()));
					$eachItem["price"] = Mage::helper("core")->stripTags($_order->formatPrice($item->getPrice()));
					$eachItem["qty"]["Ordered"] = $item->getQtyOrdered()*1;
					$eachItem["qty"]["Shipped"] = $item->getQtyShipped()*1;
					$eachItem["qty"]["Canceled"] = $item->getQtyCanceled()*1;
					$eachItem["qty"]["Refunded"] = $item->getQtyRefunded()*1;
					$eachItem["subTotal"] = Mage::helper("core")->stripTags($_order->formatPrice($item->getRowTotal()));
					$returnArray["items"][] = $eachItem;
				}
				$_totals = array();
				$_totals["subtotal"] = array(
					"value" => Mage::helper("core")->stripTags($_order->formatPrice($_order->getSubtotal())),
					"label" => Mage::helper("mobikul")->__("Subtotal"));
				if(!$_order->getIsVirtual() && ((float) $_order->getShippingAmount() || $_order->getShippingDescription()))       {
					$_totals["shipping"] = array(
						"value" => Mage::helper("core")->stripTags($_order->formatPrice($_order->getShippingAmount())),
						"label" => Mage::helper("mobikul")->__("Shipping & Handling")
					);
				}
				if(((float)$_order->getDiscountAmount()) != 0) {
					if ($_order->getDiscountDescription()) 
						$discountLabel = Mage::helper("core")->__("Discount (%s)", $_order->getDiscountDescription());
					else
						$discountLabel = Mage::helper("mobikul")->__("Discount");
					$_totals["discount"] = array(
						"value" => Mage::helper("core")->stripTags($_order->formatPrice($_order->getDiscountAmount())),
						"label" => $discountLabel
					);
				}
				if($_order->getTaxAmount()){
					$_totals["tax"] = array(
						"value" => Mage::helper("core")->stripTags($_order->formatPrice($_order->getTaxAmount())),
						"label" => Mage::helper("mobikul")->__("Tax")
					);
				}
				$_totals["grandTotal"] = array(
					"value" => Mage::helper("core")->stripTags($_order->formatPrice($_order->getGrandTotal())),
					"label" => Mage::helper("mobikul")->__("Grand Total")
				);
				if($_order->isCurrencyDifferent()) {
					$_totals["baseGrandtotal"] = array(
						"value" => Mage::helper("core")->stripTags($_order->formatBasePrice($_order->getBaseGrandTotal())),
						"label" => Mage::helper("mobikul")->__("Grand Total to be Charged")
					);
				}
				$returnArray["totals"] = $_totals;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function reOrder($data){
			try{
				$data = json_decode($data);
				$incrementId = $data->incrementId;
				$storeId = $data->storeId;
				$customerId = $data->customerId;
				$returnArray = array();
				$returnArray["error"] = 0;$returnArray["message"] = Mage::helper("mobikul")->__("Product(s) Has been Added to cart.");
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				
				$_order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
				$cart = Mage::getSingleton("checkout/cart");
				$cart->init();
				$customer = Mage::getModel("customer/customer")->load($customerId);
				Mage::getSingleton("customer/session")->setCustomer($customer);

				$quote = Mage::getSingleton("sales/quote")->setStoreId($storeId);
				$quote->assignCustomer($customer);
				$quote->save();

				$itemCollection = $quote->getAllVisibleItems();
				foreach($itemCollection as $cartItem){
					$thisItem = $quote->getItemById($cartItem->getId());
					$thisItem->save();
				}
				$checkoutSession = Mage::getSingleton("checkout/session")->setCustomer($customer);
				$checkoutSession->setCartWasUpdated(true);
				Mage::getSingleton("checkout/cart")->save();

				$outOfStockSignal = 0;$outOfStockMesssage = "";
				foreach($_order->getItemsCollection() as $_item) {
					if(is_null($_item->getParentItem())) {
						$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($_item->getProductId());
						$info = $_item->getProductOptionByCode("info_buyRequest");
						$stockItem = $product->getStockItem();
						if($stockItem->getQty() < $_item->getQtyOrdered()){
							$outOfStockMesssage .= $_item->getName().", ";
							$outOfStockSignal = 1;
							continue;
						}
						$info = new Varien_Object($info);
						$info->setQty($_item->getQtyOrdered());
						$item = $quote->addProductAdvanced($product, $info);
						if($item instanceof Mage_Sales_Model_Quote_Address_Item)
							$quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
						else
							$quoteItem = $item;
						$_product = $quoteItem->getProduct();
						$_product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());
						if($item->getQuote()->getIsSuperMode()) {
							if(!$_product) {
								return false;
							}
						}
						else {
							if(!$_product || !$_product->isVisibleInCatalog()) {
								return false;
							}
						}
						if($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
							$finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
							   $quoteItem->getParentItem()->getProduct(),
							   $quoteItem->getParentItem()->getQty(),
							   $quoteItem->getProduct(),
							   $quoteItem->getQty()
							);
							$item->setPrice($finalPrice)->setBaseOriginalPrice($finalPrice);
							$item->calcRowTotal();
						}
						else if(!$quoteItem->getParentItem()) {
							$finalPrice = $_product->getFinalPrice($quoteItem->getQty());
							$item->setPrice($finalPrice)->setBaseOriginalPrice($finalPrice);
							$item->calcRowTotal();
							$address = Mage::getSingleton("sales/quote_address")->setQuote($quote);
							$address->setTotalQty($address->getTotalQty() + $item->getQty());
						}
						$item->save();
					}
				}
				$quote->collectTotals()->save();
				$checkoutSession = Mage::getSingleton("checkout/session")->setCustomer($customer);
				$checkoutSession->setCartWasUpdated(true);
				$cart->save();

				if($outOfStockSignal){
					$outOfStockMesssage = rtrim($outOfStockMesssage, ", ");
					$returnArray["message"] = Mage::helper("mobikul")->__("Following Products")."(".$outOfStockMesssage.") ".Mage::helper("mobikul")->__("can't be added to cart as they are Out Of Stock");
				}
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Mage_Core_Exception $e){
				$returnArray["error"] = 1;
				$returnArray["message"] = $e->getMessage();
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e) {
				$returnArray["error"] = 1;
				$returnArray["message"] = Mage::helper("mobikul")->__("Can't add the item to shopping cart.");
				return Mage::helper("core")->jsonEncode($returnArray);
			}
		}

		public function getreviewDetail($data){
			try{
				$data = json_decode($data);
				$reviewId = $data->reviewId;
				$width = $data->width;
				$storeId = $data->storeId;
				$returnArray = array();$ratingArray = array();
				$_review = Mage::getModel("review/review")->load($reviewId);
				$_product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($_review->getEntityPkValue());
				$returnArray["name"] = Mage::helper("core")->stripTags($_product->getName());
				$returnArray["image"] = Mage::helper("catalog/image")->init($_product, "small_image")->keepFrame(true)->resize($width/2)->__toString();
				$ratingCollection = Mage::getModel("rating/rating_option_vote")
					->getResourceCollection()
					->setReviewFilter($reviewId)
					->addRatingInfo($storeId)
					->setStoreFilter($storeId)
					->load();
				foreach($ratingCollection as $_rating){
					$eachRating = array();
					$eachRating["ratingCode"] = Mage::helper("core")->stripTags($_rating->getRatingCode());
					$eachRating["ratingValue"] = number_format($_rating->getPercent(), 2, ".", "");
					$ratingArray[] = $eachRating;
				}
				$returnArray["ratingData"] = $ratingArray;
				$returnArray["reviewDate"] = Mage::helper("core")->__("Your Review (submitted on %s)", Mage::helper("core")->formatDate($_review->getCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_LONG));
				$returnArray["reviewDetail"] = Mage::helper("core")->stripTags($_review->getDetail());
				$reviews = Mage::getModel("review/review")->getResourceCollection()->addStoreFilter($storeId)
						->addEntityFilter("product", $_product->getId())->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
						->setDateOrder()->addRateVotes();
				$ratings = array();
				if(count($reviews) > 0) {
					foreach($reviews->getItems() as $review) {
						foreach($review->getRatingVotes() as $vote)
							$ratings[] = $vote->getPercent();
					}
				}
				if(count($ratings) > 0)
					$rating = number_format((5*(array_sum($ratings) / count($ratings)))/100, 2, ".", "");
				else
					$rating = 0;
				$returnArray["rating"] = $rating;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function saveReview($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$id = $data->id;
				$title = $data->title;
				$detail = $data->detail;
				$nickname = $data->nickname;
				$customerId = $data->customerId;
				if(!$customerId > 0)
					$customerId = NULL;
				$ratingsObj = $data->ratings;
				$ratings = array();
				foreach($ratingsObj as $key => $value)
					$ratings[$key] = $value;
				$review = Mage::getModel("review/review");
				$review->setEntityPkValue($id);
				$review->setStatusId(Mage_Review_Model_Review::STATUS_PENDING);
				$review->setTitle($title);
				$review->setDetail($detail);
				$review->setEntityId(1);
				$review->setStoreId($storeId);
				$review->setCustomerId($customerId);
				$review->setNickname($nickname);
				$review->setReviewId($review->getId());
				$review->setStores(array($storeId));
				$review->save();
				foreach($ratings as $ratingId => $optionId) {
					Mage::getModel("rating/rating")
						->setRatingId($ratingId)
						->setReviewId($review->getId())
						->setCustomerId($customerId)
						->addOptionVote($optionId, $id);
				}
				$review->aggregate();
				$returnArray["message"] = Mage::helper("mobikul")->__("Your review has been accepted for moderation.");
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function subscribetoNewsletter($data){
			try{
				$data = json_decode($data);
				$isSubscribed = $data->isSubscribed;
				if($isSubscribed == "true")
					$isSubscribed = 1;
				else
					$isSubscribed = 0;
				$storeId = $data->storeId;
				$customerId = $data->customerId;
				$returnArray = array();
				$customer = Mage::getModel("customer/customer")->load($customerId)
					->setStoreId($storeId)
					->setIsSubscribed((boolean)$isSubscribed)
					->save();
				if((boolean)$isSubscribed)
					$returnArray["message"] = Mage::helper("mobikul")->__("The subscription has been saved.");
				else
					$returnArray["message"] = Mage::helper("mobikul")->__("The subscription has been removed.");
				$returnArray["status"] = 1;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function removefromWishlist($data){
			try{
				$data = json_decode($data);
				$customerId = $data->customerId;
				$storeId = $data->storeId;
				$itemId = $data->itemId;
				$returnArray = array();
				$returnArray["error"] = 0;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$item = Mage::getModel("wishlist/item")->load($itemId);
				if(!$item->getId())
					$returnArray["error"] = 1;
				$wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
				if(!$wishlist)
					$returnArray["error"] = 1;
				$item->delete();
				$wishlist->save();
				if($returnArray["error"] == 1)
					$returnArray["message"] = Mage::helper("mobikul")->__("An error occurred while deleting the item from wishlist.");
				else
					$returnArray["message"] = Mage::helper("mobikul")->__("Item successfully deleted from wishlist.");
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function wishlistaddtoCart($data){
			$data = json_decode($data);
			$qty = $data->qty;
			$customerId = $data->customerId;
			$productId = $data->productId;
			$storeId = $data->storeId;
			$itemId = (int)$data->itemId;
			try {
				$item = Mage::getModel('wishlist/item')->loadWithOptions($itemId);
				$buyRequest = $item->getBuyRequest()->getData();
				$productId = $item->getProductId();
				$cart = Mage::getSingleton("checkout/cart");
				$cart->init();
				$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($productId);
				$customer = Mage::getModel("customer/customer")->load($customerId);
				$quote = Mage::getSingleton("sales/quote")->setStoreId($storeId)->loadByCustomer($customer);
				$item = $quote->addProductAdvanced($product, $buyRequest);
				$item->calcRowTotal();
				$address = Mage::getSingleton("sales/quote_address")->setQuote($quote);
				$address->setTotalQty($address->getTotalQty() * $qty);
				$item->save();
				$quote->collectTotals()->save();
				$checkoutSession = Mage::getSingleton("checkout/session")->setCustomer($customer);
				$checkoutSession->setCartWasUpdated(true);
				$cart->save();
			}
			catch(Mage_Core_Exception $e) {
				if($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
					$returnArray = array();
					$returnArray["success"] = 0;
					$returnArray["message"] = Mage::helper("core")->__("This product(s) is currently out of stock");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else
				if($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
					$returnArray = array();
					$returnArray["success"] = 0;
					$returnArray["message"] = $e->getMessage();
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else {
					$returnArray = array();
					$returnArray["success"] = 0;
					$returnArray["message"] = $e->getMessage();
					return Mage::helper("core")->jsonEncode($returnArray);
				}
			}
			catch(Exception $e) {
				$returnArray = array();
				$returnArray["success"] = 0;
				$returnArray["message"] = $e;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			Mage::helper("wishlist")->calculate();
			$returnArray = array();
			$returnArray["success"] = 1;
			$returnArray["message"] = Mage::helper("core")->__("Product(s) has successfully moved to cart.");
			return Mage::helper("core")->jsonEncode($returnArray);
		}

		public function addressformData($data){
			try{
				$data = json_decode($data);
				$returnArray = array();
				if(isset($data->addressId)){
					$addressId = $data->addressId;
					if($addressId > 0){
						$address = Mage::getModel("customer/address")->load($addressId);
						if(isset($data->customerId)){
							$customer = Mage::getModel("customer/customer")->load($data->customerId);
							if($customer->getDefaultBilling() == $addressId)
								$returnArray["addressData"]["isDefaultBilling"] = "true";
							else
								$returnArray["addressData"]["isDefaultBilling"] = "false";
							if($customer->getDefaultShipping() == $addressId)
								$returnArray["addressData"]["isDefaultShipping"] = "true";
							else
								$returnArray["addressData"]["isDefaultShipping"] = "false";
						}

						$addressData = $address->getData();
						foreach($addressData as $key => $addata) {
							if($addata != "")
								$returnArray["addressData"][$key] = $addata;
							else
								$returnArray["addressData"][$key] = "";
						}
						$returnArray["addressData"]["street"] = $address->getStreet();
					}
				}
				$countryCollection = Mage::getModel("directory/country")->getResourceCollection()->loadByStore()->toOptionArray(true);
				unset($countryCollection[0]);
				foreach($countryCollection as $country) {
					$eachCountry = array();
					$eachCountry["country_id"] = $country["value"];
					$eachCountry["name"] = $country["label"];
					$regionCollection = Mage::getModel("directory/region_api")->items($eachCountry["country_id"]);
					if(count($regionCollection) > 0)
						$eachCountry["states"] = $regionCollection;
					$returnArray["countryData"][] = $eachCountry;
				}
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function addressSave($data){
			try{
				$data = json_decode($data);
				$customerId = $data->customerId;
				$storeId = $data->storeId;
				$addressDataObject = $data->addressData;
				$addressData = array();
				foreach($addressDataObject as $key => $addressValue)
					$addressData[$key] = $addressValue;
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$customer = Mage::getModel("customer/customer")->load($customerId);
				$customerSession = Mage::getSingleton("customer/session")->setCustomer($customer);
				$address  = Mage::getModel("customer/address");
				if(isset($data->addressId)) {
					$addressId = $data->addressId;
					$existsAddress = $customer->getAddressById($addressId);
					if($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId())
						$address->setId($existsAddress->getId());
				}
				$errors = array();
				$addressForm = Mage::getModel("customer/form");
				$addressForm->setFormCode("customer_address_edit")->setEntity($address);
				$addressErrors  = $addressForm->validateData($addressData);
				if($addressErrors !== true)
					$errors = $addressErrors;
				$addressForm->compactData($addressData);
				$address->setCustomerId($customer->getId())->setIsDefaultBilling($addressData["default_billing"])->setIsDefaultShipping($addressData["default_shipping"]);
				$addressErrors = $address->validate();
				$address->save();
				$errorMessage = Mage::helper("mobikul")->__("The address has been saved.");
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode(array("status" => 1, "message" => $errorMessage));
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function addressDelete($data){
			try{
				$data = json_decode($data);
				$addressId = $data->addressId;
				$returnArray = array();
				$address = Mage::getModel("customer/address")->load($addressId);
				$address->delete();
				$returnArray["message"] = Mage::helper("mobikul")->__("The address has been deleted.");
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

	}