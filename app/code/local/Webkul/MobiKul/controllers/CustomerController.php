<?php

    class Webkul_MobiKul_CustomerController extends Mage_Core_Controller_Front_Action    {

        /**
         * Validate mobile number and send OTP
         *
         * @return void
         */
         public function numberVerifyAction()     {
            $returnArray                  = array();
            $returnArray["authKey"]       = "";
            $returnArray["responseCode"]  = 0;
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
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
                        $mobile     = isset($wholeData["mobile"])     ? $wholeData["mobile"]     : "";
                        if ( $mobile == "" ) {
                            $returnArray["message"] = Mage::helper("mobikul")->__("Please enter the mobile number.");$this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        else {
                            $collection = Mage::getResourceModel('customer/customer_collection')
                                            ->addNameToSelect()
                                            ->addAttributeToSelect('mobile_number')
                                            ->addAttributeToFilter('mobile_number',$mobile);
                            if ( $collection->getSize() > 0 ){
                                $returnArray["message"] = Mage::helper("mobikul")->__("Mobile number already exist, please provide another number !!");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            } else {

                                $returnArray["success"] = true;
                                $returnArray["message"] = Mage::helper("mobikul")->__("OTP Sent!!");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }                            
                        }
                    }
                }
            } catch(Exception $e){
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
         }

        public function logInAction()     {
            $returnArray                  = array();
            $returnArray["authKey"]       = "";
            $returnArray["responseCode"]  = 0;
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["customerName"]  = "";
            $returnArray["customerEmail"] = "";
            $returnArray["customerId"]    = 0;
            $returnArray["cartCount"]     = 0;
            $returnArray["bannerImage"]   = "";
            $returnArray["profileImage"]  = "";
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
                        $mobile       = isset($wholeData["mobile"])    ? $wholeData["mobile"]    : 0;
                        $width        = isset($wholeData["width"])     ? $wholeData["width"]     : 1000;
                        $quoteId      = isset($wholeData["quoteId"])   ? $wholeData["quoteId"]   : 0;
                        $username     = isset($wholeData["username"])  ? $wholeData["username"]  : "";
                        $password     = isset($wholeData["password"])  ? $wholeData["password"]  : "";
                        $storeId      = isset($wholeData["storeId"])   ? $wholeData["storeId"]   : 1;
                        $websiteId    = isset($wholeData["websiteId"]) ? $wholeData["websiteId"] : 1;
                        $token        = isset($wholeData["token"])     ? $wholeData["token"]     : "";
                        $mFactor      = isset($wholeData["mFactor"])   ? $wholeData["mFactor"]   : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $customerModel    = Mage::getModel("customer/customer");
                        $customer         = new Varien_Object();
                        if ($mobile != 0 && Mage::getStoreConfig("mobikul/basic/enable_mobile_login") == 1) {
                            $collection   = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("mobile", $mobile);
                            foreach ($collection as $each)
                                $customer = $customerModel->load($each->getCustomerId());
                        } else
                            $customer = $customerModel->setWebsiteId($websiteId)->loadByEmail($username);
                        if ($customer->getId() > 0) {
                            $customer = $customerModel->setWebsiteId($websiteId);
                            if ($customerModel->getConfirmation() && $customerModel->isConfirmationRequired()) {
                                $returnArray["message"] = Mage::helper("mobikul")->__("This account is not confirmed.");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                            $hash = $customerModel->getPasswordHash();
                            $validatePassword = 0;
                            if (!$hash)
                                $validatePassword = false;
                            $validatePassword = Mage::helper("core")->validateHash($password, $hash);
                            if (!$validatePassword) {
                                $returnArray["message"] = Mage::helper("mobikul")->__("Invalid login or password.");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                            $returnArray["customerName"]  = $customer->getName();
                            $returnArray["customerEmail"] = $customer->getEmail();
                            $returnArray["customerId"]    = $customer->getId();
                            $quote = Mage::getModel("sales/quote")->getCollection()
                                ->addFieldToFilter("customer_id", $customer->getId())
                                ->addFieldToFilter("is_active", true)
                                ->addOrder("updated_at", "desc")
                                ->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
// saving token /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                            Mage::helper("mobikul/token")->saveToken($customer->getId(), $token);
// creating or adding to group //////////////////////////////////////////////////////////////////////////////////////////////////
                            // Mage::helper("mobikul/gcm")->addToGroup($customer->getId(), $token);
                            $width *= $mFactor;
                            $height = ($width/2)*$mFactor;
                            $profileHeight = $profileWidth = 96 * $mFactor;
                            $collection = Mage::getModel("mobikul/userimage")->getCollection()->addFieldToFilter("customer_id", $customer->getId());
                            if ($collection->getSize() > 0) {
                                $time = time();
                                foreach ($collection as $value) {
                                    if ($value->getBanner() != "") {
                                        if ($value->getIsSocial() == 1)
                                            $returnArray["bannerImage"] = $value->getBanner();
                                        else {
                                            $basePath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customer->getId().DS.$value->getBanner();
                                            $newUrl = "";
                                            if (is_file($basePath)) {
                                                $newPath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customer->getId().DS.$width."x".$height.DS.$value->getBanner();
                                                Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $width, $height);
                                                $newUrl = Mage::getBaseUrl("media")."mobikul".DS."customerpicture".DS.$customer->getId().DS.$width."x".$height.DS.$value->getBanner();
                                                $returnArray["bannerDominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath); 
                                            }
                                            $returnArray["bannerImage"] = $newUrl."?".$time;
                                        }
                                    }
                                    if ($value->getProfile() != "") {
                                        if ($value->getIsSocial() == 1)
                                            $returnArray["profileImage"] = $value->getProfile();
                                        else {
                                            $basePath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customer->getId().DS.$value->getProfile();
                                            $newUrl = "";
                                            if (is_file($basePath)) {
                                                $newPath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customer->getId().DS.$profileWidth."x".$profileHeight.DS.$value->getProfile();
                                                Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $profileWidth, $profileHeight);
                                                $newUrl = Mage::getBaseUrl("media")."mobikul".DS."customerpicture".DS.$customer->getId().DS.$profileWidth."x".$profileHeight.DS.$value->getProfile();
                                                $returnArray["profileDominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath); 
                                            }
                                            $returnArray["profileImage"] = $newUrl."?".$time;
                                        }
                                    }
                                }
                            }
                            if ($quoteId != 0) {
                                $store = Mage::getSingleton("core/store")->load($storeId);
                                $guestQuote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
                                $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                                $quoteCollection->addFieldToFilter("customer_id", $customer->getId());
                                $quoteCollection->addOrder("updated_at", "desc");
                                $customerQuote = $quoteCollection->getFirstItem();
                                if ($customerQuote->getId() > 0) {
                                    $customerQuote->merge($guestQuote);
                                    $customerQuote->collectTotals()->setIsActive(true)->save();
                                } else {
                                    $guestQuote->assignCustomer($customer);
                                    $guestQuote->setCustomer($customer);
                                    $guestQuote->getShippingAddress()->setCollectShippingRates(true);
                                    $guestQuote->collectTotals()->save();
                                }
                            }
                            $returnArray["success"] = true;
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        } else
                            $error = 1;
                        if ($error == 1) {
                            $returnArray["message"] = Mage::helper("mobikul")->__("Invalid login or password.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function createAccountFormDataAction(){
            $returnArray                        = array();
            $returnArray["authKey"]             = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["success"]             = false;
            $returnArray["message"]             = "";
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
            $returnArray["isMobileVisible"]     = false;
            $returnArray["isMobileRequired"]    = false;
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
                        $storeId = isset($wholeData["storeId"]) ? $wholeData["storeId"] : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $showPrefix = Mage::getStoreCOnfig("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"] = true;
                            $returnArray["isPrefixRequired"] = true;
                        }
                        elseif($showPrefix == "opt"){
                            $returnArray["isPrefixVisible"] = true;
                        }
                        $prefixOptions = Mage::getStoreCOnfig("customer/address/prefix_options");
                        if($prefixOptions != ""){
                            $returnArray["prefixHasOptions"] = true;
                            $returnArray["prefixOptions"] = explode(";", $prefixOptions);
                        }
                        $showMiddleName = Mage::getStoreCOnfig("customer/address/middlename_show");
                        if($showMiddleName == 1)
                            $returnArray["isMiddlenameVisible"] = true;
                        $showSuffix = Mage::getStoreCOnfig("customer/address/suffix_show");
                        if($showSuffix == "req"){
                            $returnArray["isSuffixVisible"] = true;
                            $returnArray["isSuffixRequired"] = true;
                        }
                        elseif($showSuffix == "opt"){
                            $returnArray["isSuffixVisible"] = true;
                        }
                        $suffixOptions = Mage::getStoreCOnfig("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"] = explode(";", $suffixOptions);
                        }
                        $mobileStatus = Mage::getStoreCOnfig("mobikul/basic/enable_mobile_login");
                        if($mobileStatus == 1)  {
                            $returnArray["isMobileVisible"]  = true;
                            $returnArray["isMobileRequired"] = true;
                        }
                        $DOBVisible = Mage::getStoreCOnfig("customer/address/dob_show");
                        if($DOBVisible == "req"){
                            $returnArray["isDOBVisible"] = true;
                            $returnArray["isDOBRequired"] = true;
                        }
                        elseif($DOBVisible == "opt"){
                            $returnArray["isDOBVisible"] = true;
                        }
                        $TaxVisible = Mage::getStoreCOnfig("customer/address/taxvat_show");
                        if($TaxVisible == "req"){
                            $returnArray["isTaxVisible"] = true;
                            $returnArray["isTaxRequired"] = true;
                        }
                        elseif($TaxVisible == "opt"){
                            $returnArray["isTaxVisible"] = true;
                        }
                        $GenderVisible = Mage::getStoreCOnfig("customer/address/gender_show");
                        if($GenderVisible == "req"){
                            $returnArray["isGenderVisible"] = true;
                            $returnArray["isGenderRequired"] = true;
                        }
                        elseif($GenderVisible == "opt"){
                            $returnArray["isGenderVisible"] = true;
                        }
                        $returnArray["dateFormat"] = Varien_Date::DATE_INTERNAL_FORMAT;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        /**
         * This function merges the quest quote with logged in user quote after register
         */
         public function setQuote($quoteId,$store,$customerId){
            $guestQuote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
            
            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
            $quoteCollection->addFieldToFilter("customer_id", $customerId);
            $quoteCollection->addFieldToFilter("is_active", 1);
            $quoteCollection->addOrder("updated_at", "desc");
            $customerQuote = $quoteCollection->getFirstItem();
            $customer = Mage::getModel("customer/customer");
            $customer = $customer->load($customerId);
            if ($customerQuote->getId() > 0) {
                $customerQuote->merge($guestQuote);
                $customerQuote->collectTotals()->save();
            } else {
                $guestQuote->assignCustomer($customer);
                $guestQuote->setCustomer($customer);
                $guestQuote->getShippingAddress()->setCollectShippingRates(true);
                $guestQuote->collectTotals()->save();
            }
        }

        public function createAccountAction()     {
            $returnArray                  = array();
            $returnArray["authKey"]       = "";
            $returnArray["responseCode"]  = 0;
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["customerId"]    = 0;
            $returnArray["customerName"]  = "";
            $returnArray["customerEmail"] = "";
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
                        $mobile     = isset($wholeData["mobile"])     ? $wholeData["mobile"]     : "";
                        $isSocial   = isset($wholeData["isSocial"])   ? $wholeData["isSocial"]   : 0;
                        $quoteId    = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $firstName  = isset($wholeData["firstName"])  ? $wholeData["firstName"]  : "";
                        $lastName   = isset($wholeData["lastName"])   ? $wholeData["lastName"]   : "";
                        $email      = isset($wholeData["email"])      ? $wholeData["email"]      : "";
                        $password   = isset($wholeData["password"])   ? $wholeData["password"]   : "";
                        $websiteId  = isset($wholeData["websiteId"])  ? $wholeData["websiteId"]  : "";
                        $pictureURL = isset($wholeData["pictureURL"]) ? $wholeData["pictureURL"] : "";
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $token      = isset($wholeData["token"])      ? $wholeData["token"]      : "";
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        if (!Zend_Validate::is($email, "EmailAddress") && $email != "") {
                            $returnArray["message"] = Mage::helper("mobikul")->__("Invalid email address.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $customerCheck = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($email);
                        if ($isSocial == 1) {
                            if ($customerCheck->getId() > 0) {
                                if ($customerCheck->getConfirmation() && $customerCheck->isConfirmationRequired()) {
                                    $customerCheck->sendNewAccountEmail("confirmation", Mage::getSingleton("customer/session")->getBeforeAuthUrl(), $storeId);
                                    $returnArray["message"] = Mage::helper("mobikul")->__("Account confirmation is required. Please, check your email for the confirmation link.");
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                                $store = Mage::getSingleton("core/store")->load($storeId);
                                $this->setQuote($quoteId,$store,$customerCheck->getId());
                                
                                $returnArray["success"] = true;
                                $returnArray["customerId"] = $customerCheck->getId();
                                $returnArray["customerName"] = $customerCheck->getName();
                                $returnArray["customerEmail"] = $customerCheck->getEmail();
                                $returnArray["message"] = Mage::helper("mobikul")->__("Your are now Loggedin");
                                Mage::helper("mobikul/token")->saveToken($customerCheck->getId(), $token);
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
                        else{
                            if ($customerCheck->getId() > 0) {
                                $returnArray["message"] = Mage::helper("mobikul")->__("There is already an account with this email address.");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
                        $customer = Mage::getModel("customer/customer");
                        $customerForm = Mage::getModel("customer/form");
                        $customerForm->setFormCode("customer_account_create");
                        $customerForm->setEntity($customer);
                        $customerData = array(
                            "firstname"  => $firstName,
                            "middlename" => isset($wholeData["middleName"]) ? $wholeData["middleName"] : "",
                            "lastname"   => $lastName,
                            "prefix"     => isset($wholeData["prefix"])     ? $wholeData["prefix"]     : "",
                            "dob"        => isset($wholeData["dob"])        ? $wholeData["dob"]        : "",
                            "suffix"     => isset($wholeData["suffix"])     ? $wholeData["suffix"]     : "",
                            "taxvat"     => isset($wholeData["taxvat"])     ? $wholeData["taxvat"]     : "",
                            "gender"     => isset($wholeData["gender"])     ? $wholeData["gender"]     : "",
                            "email"      => $email,
                            "password"   => $password,
                            "website_id" => $websiteId,
                            "group_id"   => Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId)
                        );
                        $customerErrors = $customerForm->validateData($customerData);
                        if ($customerErrors !== true) {
                            $returnArray["message"] = implode(", ", $customerErrors);
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
// Checking for existing mobile number //////////////////////////////////////////////////////////////////////////////////////////
                        if(Mage::getStoreConfig("mobikul/basic/enable_mobile_login") == 1  && $mobile != "" ){
                            $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("mobile", $mobile);
                            if(count($collection) > 0)  {
                                $returnArray["message"] = Mage::helper("mobikul")->__("Mobile number already exist, please provide another number !!");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
// Creating Customer ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $customerId = Mage::getModel("customer/customer_api")->create($customerData);
// Saving Mobile number /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if(Mage::getStoreConfig("mobikul/basic/enable_mobile_login") == 1 && $mobile != "" )
                            Mage::getModel("mobikul/customermobile")->setMobile($mobile)->setCustomerId($customerId)->save();
// Setting Social Data //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($isSocial == 1) {
                            Mage::getModel("mobikul/userimage")
                                ->setBanner($pictureURL)
                                ->setCustomerId($customerId)
                                ->setIsSocial(1)
                                ->save();
                            $templateVariable = array();
                            $emailTemplate = Mage::getModel("core/email_template")->loadDefault("random_generated_password_mail");
                            $templateVariable["customer_name"] = $firstName." ".$lastName;
                            $templateVariable["generated_password"] = $password;
                            $templateVariable["contactus_link"] = Mage::getUrl("contacts");
                            $emailTemplate->getProcessedTemplate($templateVariable);
                            $emailTemplate->setSenderName(Mage::getStoreConfig("trans_email/ident_general/name"));
                            $emailTemplate->setSenderEmail(Mage::getStoreConfig("trans_email/ident_general/email"));
                            $emailTemplate->send($email, $firstName." ".$lastName, $templateVariable);
                            $returnArray["success"]       = true;
                            $returnArray["customerId"]    = $customerId;
                            $returnArray["customerName"]  = $firstName." ".$lastName;
                            $returnArray["customerEmail"] = $email;
                            $returnArray["message"]       = Mage::helper("customer")->__("Thank you for registering with %s.", Mage::app()->getStore()->getFrontendName());
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $customer = $customer->load($customerId);
                        if ($quoteId != 0) {
                            $store = Mage::getSingleton("core/store")->load($storeId);
                            $guestQuote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $customerQuote = $quoteCollection->getFirstItem();
                            if ($customerQuote->getId() > 0) {
                                $customerQuote->merge($guestQuote);
                                $customerQuote->collectTotals()->save();
                            } else {
                                $guestQuote->assignCustomer($customer);
                                $guestQuote->setCustomer($customer);
                                $guestQuote->getShippingAddress()->setCollectShippingRates(true);
                                $guestQuote->collectTotals()->save();
                            }
                        }
                        if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
                            $customer->sendNewAccountEmail("confirmation", Mage::getSingleton("customer/session")->getBeforeAuthUrl(), $storeId);
                            $returnArray["message"] = Mage::helper("mobikul")->__("Account confirmation is required. Please, check your email for the confirmation link.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        Mage::dispatchEvent("customer_register_success", array("account_controller"=>$this, "customer"=>$customer));
                        $customer->sendNewAccountEmail("registered", "", $storeId);
                        $returnArray["success"]       = true;
                        $returnArray["customerId"]    = $customerId;
                        $returnArray["customerName"]  = $customer->getName();
                        $returnArray["customerEmail"] = $email;
                        Mage::helper("mobikul/token")->saveToken($customerId, $token);
                        $returnArray["message"]       = Mage::helper("customer")->__("Thank you for registering with %s.", Mage::app()->getStore()->getFrontendName());
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function forgotpasswordAction()     {
            $returnArray                 = array();
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
                        $storeId      = isset($wholeData["storeId"])   ? $wholeData["storeId"]   : 1;
                        $email        = isset($wholeData["email"])     ? $wholeData["email"]     : "";
                        $websiteId    = isset($wholeData["websiteId"]) ? $wholeData["websiteId"] : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        if (!Zend_Validate::is($email, "EmailAddress")) {
                            $returnArray["message"] = Mage::helper("mobikul")->__("Invalid email address.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($email);
                        if ($customer->getId() > 0) {
                            try {
                                $newResetPasswordLinkToken = Mage::helper("customer")->generateResetPasswordLinkToken();
                                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                                $customer->setRpToken($newResetPasswordLinkToken);
                                $currentDate = Varien_Date::now();
                                $customer->setRpTokenCreatedAt($currentDate);
                                $customer->sendPasswordResetConfirmationEmail();
                            } catch (Exception $exception) {
                                $returnArray["message"] = $exception->getMessage();
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
                        $returnArray["success"] = true;
                        $returnArray["message"] = Mage::helper("customer")->__("If there is an account associated with %s you will receive an email with a link to reset your password.", $email);
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function accountInfoDataAction()     {
            $returnArray                        = array();
            $returnArray["authKey"]             = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["success"]             = false;
            $returnArray["message"]             = "";
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
            $returnArray["isMobileVisible"]     = false;
            $returnArray["isMobileRequired"]    = false;
            $returnArray["mobile"]              = "";
            $returnArray["prefixValue"]         = "";
            $returnArray["middleName"]          = "";
            $returnArray["suffixValue"]         = "";
            $returnArray["DOBValue"]            = "";
            $returnArray["taxValue"]            = "";
            $returnArray["genderValue"]         = 1;
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
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $customer = Mage::getModel("customer/customer")->load($customerId);
                        $returnArray["firstName"]  = $customer->getFirstname();
                        $returnArray["lastName"]   = $customer->getLastname();
                        $returnArray["email"]      = $customer->getEmail();
                        $addressPrimary            = $customer->getPrimaryBillingAddress();
                        if ($addressPrimary)
                            $returnArray["mobile"] = $addressPrimary->getTelephone();
                        $showPrefix = Mage::getStoreCOnfig("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"]  = true;
                            $returnArray["isPrefixRequired"] = true;
                            $returnArray["prefixValue"]      = is_null($customer->getPrefix()) ? "" : $customer->getPrefix();
                        }
                        elseif($showPrefix == "opt"){
                            $returnArray["isPrefixVisible"] = true;
                            $returnArray["prefixValue"]     = is_null($customer->getPrefix()) ? "" : $customer->getPrefix();
                        }
                        $prefixOptions = Mage::getStoreCOnfig("customer/address/prefix_options");
                        if($prefixOptions != ""){
                            $returnArray["prefixHasOptions"] = true;
                            $returnArray["prefixOptions"]    = explode(";", $prefixOptions);
                        }
                        $showMiddleName = Mage::getStoreCOnfig("customer/address/middlename_show");
                        if($showMiddleName == 1){
                            $returnArray["middleName"]          = is_null($customer->getMiddlename()) ? "" : $customer->getMiddlename();
                            $returnArray["isMiddlenameVisible"] = true;
                        }
                        $showSuffix = Mage::getStoreCOnfig("customer/address/suffix_show");
                        if($showSuffix == "req"){
                            $returnArray["isSuffixVisible"]  = true;
                            $returnArray["isSuffixRequired"] = true;
                            $returnArray["suffixValue"]      = is_null($customer->getSuffix()) ? "" : $customer->getSuffix();
                        }
                        elseif($showSuffix == "opt"){
                            $returnArray["isSuffixVisible"] = true;
                            $returnArray["suffixValue"]     = is_null($customer->getSuffix()) ? "" : $customer->getSuffix();
                        }
                        $suffixOptions = Mage::getStoreCOnfig("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"]    = explode(";", $suffixOptions);
                        }
                        $mobileStatus = Mage::getStoreCOnfig("mobikul/basic/enable_mobile_login");
                        if($mobileStatus == 1)  {
                            $returnArray["isMobileVisible"]  = true;
                            $returnArray["isMobileRequired"] = true;
                            $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("customer_id", $customerId);
                            foreach ($collection as $each) {
                                $returnArray["mobile"] = $each->getMobile();
                            }
                        }
                        $DOBVisible = Mage::getStoreCOnfig("customer/address/dob_show");
                        if($DOBVisible == "req"){
                            $returnArray["isDOBVisible"]  = true;
                            $returnArray["isDOBRequired"] = true;
                            $returnArray["DOBValue"]      = is_null($customer->getDob()) ? "" : $customer->getDob();
                        }
                        elseif($DOBVisible == "opt"){
                            $returnArray["isDOBVisible"] = true;
                            $returnArray["DOBValue"]     = is_null($customer->getDob()) ? "" : $customer->getDob();
                        }
                        $TaxVisible = Mage::getStoreCOnfig("customer/address/taxvat_show");
                        if($TaxVisible == "req"){
                            $returnArray["isTaxVisible"]  = true;
                            $returnArray["isTaxRequired"] = true;
                            $returnArray["taxValue"]      = is_null($customer->getTaxvat()) ? "" : $customer->getTaxvat();
                        }
                        elseif($TaxVisible == "opt"){
                            $returnArray["isTaxVisible"] = true;
                            $returnArray["taxValue"]     = is_null($customer->getTaxvat()) ? "" : $customer->getTaxvat();
                        }
                        $GenderVisible = Mage::getStoreCOnfig("customer/address/gender_show");
                        if($GenderVisible == "req"){
                            $returnArray["isGenderVisible"]  = true;
                            $returnArray["isGenderRequired"] = true;
                            $returnArray["genderValue"]      = is_null($customer->getGender()) ? 0 : $customer->getGender();
                        }
                        elseif($GenderVisible == "opt"){
                            $returnArray["isGenderVisible"] = true;
                            $returnArray["genderValue"]     = is_null($customer->getGender()) ? 0 : $customer->getGender();
                        }
                        $returnArray["dateFormat"] = Varien_Date::DATE_INTERNAL_FORMAT;
                        $returnArray["success"]    = true;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function saveAccountInfoAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["customerName"] = "";
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
                        $mobile           = isset($wholeData["mobile"])           ? $wholeData["mobile"]           : "";
                        $storeId          = isset($wholeData["storeId"])          ? $wholeData["storeId"]          : 1;
                        $customerId       = isset($wholeData["customerId"])       ? $wholeData["customerId"]       : 0;
                        $firstName        = isset($wholeData["firstName"])        ? $wholeData["firstName"]        : "";
                        $lastName         = isset($wholeData["lastName"])         ? $wholeData["lastName"]         : "";
                        $email            = isset($wholeData["email"])            ? $wholeData["email"]            : "";
                        $doChangePassword = isset($wholeData["doChangePassword"]) ? $wholeData["doChangePassword"] : 0;
                        $currentPassword  = isset($wholeData["currentPassword"])  ? $wholeData["currentPassword"]  : "";
                        $newPassword      = isset($wholeData["newPassword"])      ? $wholeData["newPassword"]      : "";
                        $confirmPassword  = isset($wholeData["confirmPassword"])  ? $wholeData["confirmPassword"]  : "";
                        $middleName       =   isset($wholeData["middleName"])     ? $wholeData["middleName"]       : "";
                        $prefix           = isset($wholeData["prefix"])           ? $wholeData["prefix"]           : "";
                        $dob              = isset($wholeData["dob"])              ? $wholeData["dob"]              : "";
                        $suffix           = isset($wholeData["suffix"])           ? $wholeData["suffix"]           : "";
                        $taxvat           = isset($wholeData["taxvat"])           ? $wholeData["taxvat"]           : "";
                        $gender           = isset($wholeData["gender"])           ? $wholeData["gender"]           : "";
                        $appEmulation     = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $error            = 0;
                        $customer         = Mage::getModel("customer/customer")->load($customerId);
                        $customerForm     = Mage::getModel("customer/form");
                        $customerForm->setFormCode("customer_account_edit")->setEntity($customer);
                        $customerData = array(
                            "firstname"  => $firstName,
                            "middlename" => $middleName,
                            "lastname"   => $lastName,
                            "prefix"     => $prefix,
                            "dob"        => $dob,
                            "suffix"     => $suffix,
                            "taxvat"     => $taxvat,
                            "gender"     => $gender,
                            "email"      => $email
                        );

// Checking for existing mobile number //////////////////////////////////////////////////////////////////////////////////////////
                        if(Mage::getStoreConfig("mobikul/basic/enable_mobile_login") == 1){
                            $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("mobile", $mobile);
                            $data = $collection->getData();
                            $existingId = $data[0]["customer_id"];
                            if(count($collection) > 0 && $existingId != $customerId) {
                                $returnArray["message"] = Mage::helper("mobikul")->__("Mobile number already exist, please provide another number !!");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
// Saving Mobile number /////////////////////////////////////////////////////////////////////////////////////////////////////////
                            if ($mobile != "") {
                                $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("customer_id", $customerId);
                                if(count($collection) > 0)  {
                                    foreach($collection as $each)   {
                                        Mage::getModel("mobikul/customermobile")->setMobile($mobile)->setId($each->getId())->save();
                                    }
                                }
                                else{
                                    Mage::getModel("mobikul/customermobile")->setMobile($mobile)->setCustomerId($customerId)->save();
                                }
                            }
                        }
                        $errors = array();
                        $customerErrors = $customerForm->validateData($customerData);
                        if ($customerErrors !== true) {
                            $errors = array_merge($customerErrors, $errors);
                            $error = 1;
                            $message = implode(", ", $errors);
                        } else {
                            $customerForm->compactData($customerData);
                            if ($doChangePassword == 1) {
                                $oldPassword = $customer->getPasswordHash();
                                if (Mage::helper("core/string")->strpos($oldPassword, ":") == true)
                                    list($_salt, $salt) = explode(":", $oldPassword);
                                else
                                    $salt = false;
                                if ($customer->hashPassword($currentPassword, $salt) == $oldPassword) {
                                    if (strlen($newPassword)) {
                                        $customer->setPassword($newPassword);
                                        $customer->setConfirmation($confirmPassword);
                                        $customer->setPasswordConfirmation($confirmPassword);
                                    }
                                } else {
                                    $error = 1;
                                    $message = Mage::helper("mobikul")->__("Invalid current password");
                                }
                            }
                            $customerErrors = $customer->validate();
                            if (is_array($customerErrors)) {
                                $errors = array_merge($errors, $customerErrors);
                                $error = 1;
                                $message = implode(", ", $errors);
                            }
                        }
                        if ($error == 0) {
                            $customer->setConfirmation(null);
                            $customer->save();
                            $message = Mage::helper("mobikul")->__("The account information has been saved.");
                            $returnArray["success"]  = true;
                        }
                        $returnArray["customerName"] = $customer->getName();
                        $returnArray["message"]      = $message;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function addressBookDataAction()     {
            $returnArray                             = array();
            $returnArray["authKey"]                  = "";
            $returnArray["responseCode"]             = 0;
            $returnArray["success"]                  = false;
            $returnArray["message"]                  = "";
            $returnArray["billingAddress"]["value"]  = "";
            $returnArray["billingAddress"]["id"]     = 0;
            $returnArray["shippingAddress"]["value"] = "";
            $returnArray["shippingAddress"]["id"]    = 0;
            $returnArray["additionalAddress"]        = array();
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
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $customer = Mage::getModel("customer/customer")->load($customerId);
                        $address = $customer->getPrimaryBillingAddress();
                        if ($address instanceof Varien_Object) {
                            $returnArray["billingAddress"]["value"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($address->format("html"))));
                            $returnArray["billingAddress"]["id"] = $address->getId();
                        } else {
                            $returnArray["billingAddress"]["value"] = Mage::helper("mobikul")->__("You have not set a default billing address.");
                            $returnArray["billingAddress"]["id"] = 0;
                        }
                        $address = $customer->getPrimaryShippingAddress();
                        if ($address instanceof Varien_Object) {
                            $returnArray["shippingAddress"]["value"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($address->format("html"))));
                            $returnArray["shippingAddress"]["id"] = $address->getId();
                        } else {
                            $returnArray["shippingAddress"]["value"] = Mage::helper("mobikul")->__("You have not set a default shipping address.");
                            $returnArray["shippingAddress"]["id"] = 0;
                        }
                        $additionalAddress = $customer->getAdditionalAddresses();
                        foreach ($additionalAddress as $key => $eachAdditionalAddress) {
                            if ($eachAdditionalAddress instanceof Varien_Object) {
                                $eachAdditionalAddressArray = array();
                                $eachAdditionalAddressArray["value"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($eachAdditionalAddress->format("html"))));
                                $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                            } else {
                                $eachAdditionalAddressArray["value"] = Mage::helper("mobikul")->__("You have no additional address.");
                                $eachAdditionalAddressArray["id"] = 0;
                            }
                            $returnArray["additionalAddress"][] = $eachAdditionalAddressArray;
                        }
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function orderListAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["totalCount"]   = 0;
            $returnArray["orderList"]    = array();
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
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $pageNumber = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $orderCollection = Mage::getResourceModel("sales/order_collection")
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("state", array("in" => Mage::getSingleton("sales/order_config")->getVisibleOnFrontStates()))
                            ->setOrder("created_at", "DESC");
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $orderCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $orderCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $orderList = array();
                        foreach ($orderCollection as $key => $order) {
                            $eachOrder = array();
                            $eachOrder["id"]          = $key;
                            $eachOrder["order_id"]    = $order->getRealOrderId();
                            $eachOrder["date"]        = Mage::helper("core")->formatDate($order->getCreatedAtStoreDate());
                            $eachOrder["ship_to"]     = $order->getShippingAddress() ? Mage::helper("core")->stripTags($order->getShippingAddress()->getName()) : " ";
                            $eachOrder["order_total"] = Mage::helper("core")->stripTags($order->formatPrice($order->getGrandTotal()));
                            $eachOrder["state"]       = $order->getState();
                            $eachOrder["status"]      = $order->getStatusLabel();
                            $canReorder               = false;
                            $helper                   = Mage::helper("mobikul");
                            if($helper->canReorder($order))
                                $canReorder           = $helper->canReorder($order);
                            $eachOrder["canReorder"]  = $canReorder;
                            $orderList[]              = $eachOrder;
                        }
                        $returnArray["orderList"] = $orderList;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function reviewListAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["totalCount"]   = 0;
            $returnArray["reviewList"]   = array();
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
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $pageNumber = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $width      = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $reviews = Mage::getModel("review/review")
                            ->getProductCollection()
                            ->addStoreFilter($storeId)
                            ->addCustomerFilter($customerId)
                            ->setDateOrder();
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $reviews->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $reviews->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $reviewList = array();
                        foreach ($reviews as $key => $_review) {
                            $eachReview = array();
                            $eachReview["date"] = Mage::helper("core")->formatDate($_review->getReviewCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                            $eachReview["id"] = $key;
                            $_product = Mage::getModel("catalog/product")->load($_review->getEntityPkValue());
                            $imageData = Mage::helper("mobikul/image")->init($_product, "small_image")->keepFrame(true)->resize($width / 3)->__toString();
                            $eachReview["thumbNail"] = $imageData[0];
                            $eachReview["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                            $eachReview["typeId"] = $_product->getTypeId();
                            $eachReview["productId"] = $_product->getId();
                            $eachReview["proName"] = Mage::helper("core")->stripTags($_product->getName());
                            $eachReview["details"] = Mage::helper("core/string")->truncate(Mage::helper("core")->stripTags($_review->getDetail()), 50);
                            $ratingCollection = Mage::getModel("rating/rating_option_vote")
                                ->getResourceCollection()
                                ->setReviewFilter($key)
                                ->addRatingInfo($storeId)
                                ->setStoreFilter($storeId)
                                ->load();
                            $ratingArray = array();
                            foreach ($ratingCollection as $rating) {
                                $eachRating = array();
                                $eachRating["ratingCode"] = Mage::helper("core")->stripTags($rating->getRatingCode());
                                $eachRating["ratingValue"] = number_format($rating->getPercent(), 2, ".", "");
                                $ratingArray[] = $eachRating;
                            }
                            $eachReview["ratingData"] = $ratingArray;
                            $reviewList[] = $eachReview;
                        }
                        $returnArray["reviewList"] = $reviewList;
                        $returnArray["success"]    = true;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function wishListAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["totalCount"]   = 0;
            $returnArray["wishList"]     = array();
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
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $pageNumber   = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $width        = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $customer     = Mage::getModel("customer/customer")->load($customerId);
                        $wishlist     = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer, true);
                        $wishListCollection = $wishlist->getItemCollection();
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $wishListCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $wishListCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        if($pageNumber > $wishListCollection->getLastPageNumber()){
                            $returnArray["wishList"] = array();
                            $returnArray["success"]  = true;
                            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $wishList = array();
                        foreach ($wishListCollection as $item) {
                            $eachWishData                = array();
                            $eachWishData["id"]          = $item->getId();
                            $product                     = Mage::getModel("catalog/product")->load($item->getProduct()->getId());
                            $eachWishData["name"]        = $product->getName();
                            $eachWishData["description"] = $item->getDescription();
                            $eachWishData["sku"]         = $product->getSku();
                            $eachWishData["productId"]   = $product->getId();
                            $eachWishData["typeId"]      = $product->getTypeId();
                            $eachWishData["qty"]         = $item->getQty() * 1;
                            $eachWishData["price"]       = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getFinalPrice()));
                            if($product->getTypeId() == "grouped"){
                                if($product->getMinimalPrice() == "") {
                                    $groupedParentId     = Mage::getModel("catalog/product_type_grouped")->getParentIdsByChild($product->getId());
                                    $associatedProducts  = $product->getTypeInstance(true)->getAssociatedProducts($product);
                                    $minPrice            = array();
                                    foreach($associatedProducts as $associatedProduct) {
                                        if($ogPrice      = $associatedProduct->getFinalPrice())
                                            $minPrice[]  = $ogPrice;
                                    }
                                    if ( (int)$item->getProduct()->getFinalPrice() > 0){
                                        $minPrice = $item->getProduct()->getFinalPrice();
                                    }else 
                                    if(count($minPrice))
                                        $minPrice = min($minPrice);
                                    else
                                        $minPrice = 0;
                                    $eachWishData["groupedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($minPrice));
                                }
                                else
                                    $eachWishData["groupedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMinimalPrice()));
                            }

                            $eachWishData["unformatedPrice"]  = $product->getPrice();
                            $eachWishData["formatedPrice"]  = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getPrice()));

                            $eachWishData["specialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getSpecialPrice())));
                            $eachWishData["unformatedSpecialPrice"] = $product->getSpecialPrice();

                            $fromdate = $product->getSpecialFromDate();
                            $todate = $product->getSpecialToDate();
                            $isInRange = false;
                            if (isset($fromdate) && isset($todate)) {
                                $today     = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                                $todayTime = strtotime($today);
                                $fromTime  = strtotime($fromdate);
                                $toTime    = strtotime($todate);
                                if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                                    $isInRange = true;
                                }
                            }
                            if (isset($fromdate) && !isset($todate)) {
                                $today     = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                                $todayTime = strtotime($today);
                                $fromTime  = strtotime($fromdate);
                                if ($todayTime >= $fromTime) {
                                    $isInRange = true;
                                }
                            }
                            if(!isset($fromdate) && isset($todate)){
                                $today      = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                                $today_time = strtotime($today);
                                $from_time  = strtotime($fromdate);
                                if($today_time <= $from_time)
                                    $isInRange = true;
                            }
                            $eachWishData["isInRange"] = $isInRange;
                            
                            if ($product->getTypeId() == "bundle") {
                                $bundlePriceModel = Mage::getModel("bundle/product_price");
                                $eachWishData["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($product, "min", 1)));
                                $eachWishData["minPrice"] = $bundlePriceModel->getTotalPrices($product, "min", 1);
                                $eachWishData["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($product, "max", 1)));
                                $eachWishData["maxPrice"] = $bundlePriceModel->getTotalPrices($product, "max", 1);
                            } else {
                                $eachWishData["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMinPrice()));
                                $eachWishData["minPrice"] = $product->getMinPrice();
                                $eachWishData["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMaxPrice()));
                                $eachWishData["maxPrice"] = $product->getMaxPrice();
                            }
                            $imageData = Mage::helper("mobikul/image")->init($product, "small_image")->keepFrame(true)->resize($width / 3)->__toString();
                            $eachWishData["thumbNail"]   = $imageData[0];
                            $eachWishData["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                            if ($product->getTypeId() == "downloadable") {
                                $buyRequest = $item->getBuyRequest()->getData();
                                foreach ($buyRequest["links"] as $linkId) {
                                    $links = Mage::getModel("downloadable/link")->getCollection()->addTitleToResult()->addFieldToFilter("main_table.link_id", $linkId);
                                    $eachWishData["linkTitles"][] = $links->getFirstItem()->getDefaultTitle();
                                }
                            }
                            $customoptions = Mage::helper("catalog/product_configuration")->getOptions($item);
                            if (count($customoptions) > 0)
                                $eachWishData["option"]  = $customoptions;
                            $reviews = Mage::getModel("review/review")->getResourceCollection()->addStoreFilter($storeId)
                                    ->addEntityFilter("product", $product->getId())->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                                    ->setDateOrder()->addRateVotes();
                            $ratings = array();
                            if (count($reviews) > 0) {
                                foreach ($reviews->getItems() as $review) {
                                    foreach ($review->getRatingVotes() as $vote)
                                        $ratings[]  = $vote->getPercent();
                                }
                            }
                            if (count($ratings) > 0)
                                $rating             = number_format((5 * (array_sum($ratings) / count($ratings))) / 100, 2, ".", "");
                            else
                                $rating             = 0;
                            $eachWishData["rating"] = $rating;
                            $wishList[]             = $eachWishData;
                        }
                        $returnArray["wishList"]    = $wishList;
                        $returnArray["success"]     = true;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function updatewishListAction()     {
            $returnArray                 = array();
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
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $itemData     = isset($wholeData["itemData"])   ? $wholeData["itemData"]   : "[]";
                        $itemData     = Mage::helper("core")->jsonDecode($itemData);
                        $wishlist     = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
                        $updatedItems = 0;
                        foreach ($itemData as $eachItem) {
                            $item = Mage::getModel("wishlist/item")->load($eachItem["id"]);
                            if ($item->getWishlistId() != $wishlist->getId())
                                continue;
                            $description = (string) $eachItem["description"];
                            if ($description == Mage::helper("wishlist")->defaultCommentString())
                                $description = "";
                            elseif (!strlen($description))
                                $description = $item->getDescription();
                            $qty = null;
                            if (isset($eachItem["qty"]))
                                $qty = $eachItem["qty"];
                            if (is_null($qty)) {
                                $qty = $item->getQty();
                                if (!$qty)
                                    $qty = 1;
                            } elseif (0 == $qty) {
                                try {
                                    $item->delete();
                                } catch (Exception $e) {
                                    $returnArray["message"] = Mage::helper("mobikul")->__("Can't delete item from wishlist");
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                            }
                            if (($item->getDescription() == $description) && ($item->getQty() == $qty))
                                continue;
                            try {
                                $item->setDescription($description)->setQty($qty)->save();
                                ++$updatedItems;
                            } catch (Exception $e) {
                                $returnArray["message"] = Mage::helper("core")->__("Can't save description %s", Mage::helper("core")->escapeHtml($description));
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
                        if ($updatedItems) {
                            try {
                                $wishlist->save();
                                Mage::helper("wishlist")->calculate();
                            } catch (Exception $e) {
                                $returnArray["message"] = Mage::helper("mobikul")->__("Can't update wishlist");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
                        $returnArray["success"] = true;
                        $returnArray["message"] = Mage::helper("mobikul")->__("Wishlist updated successfully");
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function myDownloadsListAction()     {
            $returnArray                  = array();
            $returnArray["authKey"]       = "";
            $returnArray["responseCode"]  = 0;
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["totalCount"]    = 0;
            $returnArray["downloadsList"] = array();
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
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $pageNumber = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $purchased  = Mage::getResourceModel("downloadable/link_purchased_collection")
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addOrder("created_at", "desc");
                        $purchasedIds = array();
                        foreach ($purchased as $_item)
                            $purchasedIds[] = $_item->getId();
                        if (empty($purchasedIds))
                            $purchasedIds = array(null);
                        $purchasedItems = Mage::getResourceModel("downloadable/link_purchased_item_collection")
                            ->addFieldToFilter("purchased_id", array("in" => $purchasedIds))
                            ->addFieldToFilter("status", array("nin" => array(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT, Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW)))
                            ->setOrder("item_id", "desc");
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $purchasedItems->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $purchasedItems->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        foreach ($purchasedItems as $item)
                            $item->setPurchased($purchased->getItemById($item->getPurchasedId()));
                        $downloadsList = array();
                        foreach ($purchasedItems as $downloads) {
                            $eachDownloads = array();
                            $eachDownloads["incrementId"] = $incrementId = $downloads->getPurchased()->getOrderIncrementId();
                            $order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
                            if ($order->getRealOrderId() > 0) {
                                $eachDownloads["isOrderExist"] = 1;
                            } else {
                                $eachDownloads["isOrderExist"] = 0;
                                $eachDownloads["message"] = Mage::helper("mobikul")->__("Sorry This Order Does not Exist!!");
                            }
                            $eachDownloads["hash"]    = $downloads->getLinkHash();
                            $eachDownloads["date"]    = Mage::helper("core")->formatDate($downloads->getPurchased()->getCreatedAt());
                            $eachDownloads["proName"] = Mage::helper("core")->stripTags($downloads->getPurchased()->getProductName());
                            $eachDownloads["state"]   = $order->getState();
                            $eachDownloads["status"]  = $downloads->getStatus();
                            if ($downloads->getNumberOfDownloadsBought())
                                $eachDownloads["remainingDownloads"] = $downloads->getNumberOfDownloadsBought() - $downloads->getNumberOfDownloadsUsed();
                            else
                                $eachDownloads["remainingDownloads"] = Mage::helper("mobikul")->__("Unlimited");
                            $canReorder = false;
                            $helper = Mage::helper("mobikul");
                            if($helper->canReorder($order))
                                $canReorder = $helper->canReorder($order);
                            $eachDownloads["canReorder"] = $canReorder;
                            $downloadsList[] = $eachDownloads;
                        }
                        $returnArray["downloadsList"] = $downloadsList;
                        $returnArray["success"]       = true;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function downloadProductAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["url"]          = "";
            $returnArray["fileName"]     = "";
            $returnArray["mimeType"]     = "";
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
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $hash       = isset($wholeData["hash"])       ? $wholeData["hash"]       : "";
                        $linkPurchasedItem = Mage::getModel("downloadable/link_purchased_item")->load($hash, "link_hash");
                        if (!$linkPurchasedItem->getId()) {
                            $returnArray["message"] = Mage::helper("mobikul")->__("Requested link does not exist.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        if (!Mage::helper("downloadable")->getIsShareable($linkPurchasedItem)) {
                            $linkPurchased = Mage::getModel("downloadable/link_purchased")->load($linkPurchasedItem->getPurchasedId());
                            if ($linkPurchased->getCustomerId() != $customerId) {
                                $returnArray["message"] = Mage::helper("mobikul")->__("Requested link does not exist.");
                                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                return;
                            }
                        }
                        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();
                        $status = $linkPurchasedItem->getStatus();
                        if ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)) {
                            if ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                                $returnArray["url"] = $linkPurchasedItem->getLinkUrl();
                                $buffer = file_get_contents($linkPurchasedItem->getLinkUrl());
                                $finfo = new finfo(FILEINFO_MIME_TYPE);
                                $returnArray["mimeType"] = $finfo->buffer($buffer);
                                $fileArray = explode(DS, $linkPurchasedItem->getLinkUrl());
                                $returnArray["fileName"] = end($fileArray);
                            } elseif ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                                $linkFile = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile());
                                if (is_file($linkFile)) {
                                    $returnArray["mimeType"] = mime_content_type($linkFile);
                                    $returnArray["url"] = Mage::getUrl("mobikulhttp/download/index", array("hash" => $hash, "sessionId" => $sessionId));
                                    $fileArray = explode(DS, $linkFile);
                                    $returnArray["fileName"] = end($fileArray);
                                } else {
                                    $returnArray["message"] = Mage::helper("mobikul")->__("An error occurred while getting the requested content. Please contact the store owner.");
                                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                                    return;
                                }
                            }
                        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
                            $returnArray["message"] = Mage::helper("mobikul")->__("The link has expired.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING || $status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW) {
                            $returnArray["message"] = Mage::helper("mobikul")->__("The link is not available.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        } else {
                            $returnArray["message"] = Mage::helper("mobikul")->__("An error occurred while getting the requested content. Please contact the store owner.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function orderDetailsAction()     {
            $returnArray                    = array();
            $returnArray["authKey"]         = "";
            $returnArray["responseCode"]    = 0;
            $returnArray["success"]         = false;
            $returnArray["message"]         = "";
            $returnArray["incrementId"]     = 0;
            $returnArray["orderId"]         = 0;
            $returnArray["statusLabel"]     = "";
            $returnArray["orderDate"]       = "";
            $returnArray["shippingAddress"] = "";
            $returnArray["shippingMethod"]  = "";
            $returnArray["billingAddress"]  = "";
            $returnArray["billingMethod"]   = "";
            $returnArray["items"]           = array();
            $returnArray["totals"]          = new stdClass;
            $returnArray["canReorder"]      = false;
            $returnArray["state"]           = "";
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
                        $incrementId  = isset($wholeData["incrementId"]) ? $wholeData["incrementId"] : 0;
                        $storeId      = isset($wholeData["storeId"])     ? $wholeData["storeId"]     : 1;
                        $order        = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $returnArray["canReorder"]  = Mage::helper("mobikul")->canReorder($order);
                        if(!$order->getId()) {
                            $message = Mage::helper("mobikul")->__("Order is not Available.");
                            $returnArray["message"]  = $message;
                            $returnArray["success"]  = false;
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $returnArray["incrementId"] = $order->getRealOrderId();
                        $returnArray["statusLabel"] = $order->getStatusLabel();
                        $returnArray["state"]       = $order->getState();
                        $returnArray["orderDate"]   = Mage::helper("core")->formatDate($order->getCreatedAtStoreDate(), "long");
                        if ($order->getShippingAddressId()) {
// shipping address ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                            $shippingAddress = Mage::getModel("sales/order_address")->load($order->getShippingAddressId());
                            $returnArray["shippingAddress"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($shippingAddress->format("html"))));
                            if ($order->getShippingDescription())
                                $returnArray["shippingMethod"] = Mage::helper("core")->stripTags($order->getShippingDescription());
                            else
                                $returnArray["shippingMethod"] = Mage::helper("mobikul")->__("No shipping information available");
                        }
// billing address //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $billingAddress = Mage::getModel("sales/order_address")->load($order->getBillingAddressId());
                        $returnArray["billingAddress"] = preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", Mage::helper("core")->stripTags($billingAddress->format("html"))));
                        $returnArray["billingMethod"] = $order->getPayment()->getMethodInstance()->getTitle();
// item data ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $itemCollection = $order->getAllVisibleItems();

                        $returnArray["orderId"] = $order->getId();
                        foreach ($itemCollection as $item) {
                            $eachItem = array();
                            $eachItem["name"] = $item->getName();
                            $result = array();
                            if ($options = $item->getProductOptions()) {
                                if (isset($options["options"]))
                                    $result = array_merge($result, $options["options"]);
                                if (isset($options["additional_options"]))
                                    $result = array_merge($result, $options["additional_options"]);
                                if (isset($options["attributes_info"]))
                                    $result = array_merge($result, $options["attributes_info"]);
                            }
                            if ($result) {
                                foreach ($result as $_option) {
                                    $eachOption = array();
                                    $eachOption["label"]  = Mage::helper("core")->stripTags($_option["label"]);
                                    $eachOption["value"]  = $_option["value"];
                                    $eachItem["option"][] = $eachOption;
                                }
                            }
                            $eachItem["sku"]             = Mage::helper("core")->stripTags(Mage::helper("core/string")->splitInjection($item->getSku()));
                            $eachItem["price"]           = Mage::helper("core")->stripTags($order->formatPrice($item->getPrice()));
                            $eachItem["qty"]["Ordered"]  = $item->getQtyOrdered() * 1;
                            $eachItem["qty"]["Shipped"]  = $item->getQtyShipped() * 1;
                            $eachItem["qty"]["Canceled"] = $item->getQtyCanceled() * 1;
                            $eachItem["qty"]["Refunded"] = $item->getQtyRefunded() * 1;
                            $eachItem["subTotal"]        = Mage::helper("core")->stripTags($order->formatPrice($item->getRowTotal()));
                            $returnArray["items"][]      = $eachItem;
                        }
                        $totals = array();
                        $totals["subtotal"] = array(
                            "title" =>  Mage::helper("mobikul")->__("Subtotal"),
                            "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($order->getSubtotal())),
                            "unformatedValue" => $order->getSubtotal()
                        );
                        if (!$order->getIsVirtual() && ((float) $order->getShippingAmount() || $order->getShippingDescription())) {
                            $totals["shipping"] = array(
                                "title" =>  Mage::helper("mobikul")->__("Shipping & Handling"),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($order->getShippingAmount())),
                                "unformatedValue" => $order->getShippingAmount()
                            );
                        }
                        if (((float) $order->getDiscountAmount()) != 0) {
                            $discountTitle = Mage::helper("mobikul")->__("Discount");
                            if ($order->getDiscountDescription())
                                $discountTitle = Mage::helper("core")->__("Discount (%s)", $order->getDiscountDescription());
                            $totals["discount"] = array(
                                "title" => $discountTitle,
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($order->getDiscountAmount())),
                                "unformatedValue" => $order->getDiscountAmount()
                            );
                        }
                        if ($order->getTaxAmount()) {
                            $totals["tax"] = array(
                                "title" => Mage::helper("mobikul")->__("Tax"),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($order->getTaxAmount())),
                                "unformatedValue" => $order->getTaxAmount()
                            );
                        }
                        $totals["grandTotal"] = array(
                            "title" => Mage::helper("mobikul")->__("Grand Total"),
                            "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($order->getGrandTotal())),
                            "unformatedValue" => $order->getGrandTotal()
                        );
                        if ($order->isCurrencyDifferent()) {
                            $totals["baseGrandtotal"] = array(
                                "title" => Mage::helper("mobikul")->__("Grand Total to be Charged"),
                                "value" => Mage::helper("core")->stripTags(Mage::helper("core")->currency($order->getBaseGrandTotal())),
                                "unformatedValue" => $order->getBaseGrandTotal()
                            );
                        }
                        $returnArray["totals"]  = $totals;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function reOrderAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["cartCount"]    = 0;
            $returnArray["message"]      = Mage::helper("mobikul")->__("Product(s) Has been Added to cart.");
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
                        $incrementId = isset($wholeData["incrementId"]) ? $wholeData["incrementId"] : 0;
                        $customerId  = isset($wholeData["customerId"])  ? $wholeData["customerId"]  : 0;
                        $storeId     = isset($wholeData["storeId"])     ? $wholeData["storeId"]     : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
                        $outOfStockSignal = false;
                        $outOfStockItems = array();
                        $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                        $quoteCollection->addFieldToFilter("customer_id", $customerId);
                        
                        $quoteCollection->addOrder("updated_at", "desc");
                        $quote   = $quoteCollection->getFirstItem();
                        $quoteId = $quote->getId();
                        foreach ($order->getItemsCollection() as $item) {
                            if (is_null($item->getParentItem())) {
                                $product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($item->getProductId());
                                $info = $item->getProductOptionByCode("info_buyRequest");
                                $stockItem = $product->getStockItem();
                                if ($stockItem->getQty() < $item->getQtyOrdered()) {
                                    $outOfStockItems[] = $item->getName();
                                    $outOfStockSignal = true;
                                    continue;
                                }
                                if ($product->getStatus() == 2)
                                    continue;
                                $info = new Varien_Object($info);
                                $info->setQty($item->getQtyOrdered());
                                $item = $quote->addProductAdvanced($product, $info);
                                if ($item instanceof Mage_Sales_Model_Quote_Address_Item)
                                    $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
                                else
                                    $quoteItem = $item;
                                $product = $quoteItem->getProduct();
                                $product->setCustomerGroupId($quoteItem->getQuote()->getCustomerGroupId());
                                if ($item->getQuote()->getIsSuperMode()) {
                                    if (!$product)
                                        return false;
                                } else {
                                    if (!$product || !$product->isVisibleInCatalog())
                                        return false;
                                }
                                if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
                                    $finalPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
                                        $quoteItem->getParentItem()->getProduct(),
                                        $quoteItem->getParentItem()->getQty(),
                                        $quoteItem->getProduct(),
                                        $quoteItem->getQty()
                                    );
                                    $item->setPrice($finalPrice)->setBaseOriginalPrice($finalPrice);
                                    $item->calcRowTotal();
                                } elseif (!$quoteItem->getParentItem()) {
                                    $finalPrice = $product->getFinalPrice($quoteItem->getQty());
                                    $item->setPrice($finalPrice)->setBaseOriginalPrice($finalPrice);
                                    $item->calcRowTotal();
                                    $address = Mage::getSingleton("sales/quote_address")->setQuote($quote);
                                    $address->setTotalQty($address->getTotalQty() + $item->getQty());
                                }
                                $item->save();
                            }
                        }
                        $quote->setIsActive(true)->collectTotals()->save();
                        $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        if ($outOfStockSignal) {
                            $outOfStockMessage = implode(", ", $outOfStockItems);
                            $returnArray["message"] = Mage::helper("mobikul")->__("Following Products")."(".$outOfStockMessage.") ".Mage::helper("mobikul")->__("can't be added to cart as they are Out Of Stock");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function reviewDetailsAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["name"]         = "";
            $returnArray["image"]        = "";
            $returnArray["ratingData"]   = array();
            $returnArray["reviewDate"]   = "";
            $returnArray["reviewDetail"] = "";
            $returnArray["rating"]       = 0;
            $returnArray["productId"]    = 0;
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
                        $reviewId     = isset($wholeData["reviewId"]) ? $wholeData["reviewId"] : 0;
                        $width        = isset($wholeData["width"])    ? $wholeData["width"]    : 1000;
                        $storeId      = isset($wholeData["storeId"])  ? $wholeData["storeId"]  : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $review = Mage::getModel("review/review")->load($reviewId);
                        $product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($review->getEntityPkValue());
                        $returnArray["productId"] = $product->getId();
                        $returnArray["name"] = Mage::helper("core")->stripTags($product->getName());
                        $imageData = Mage::helper("mobikul/image")->init($product, "small_image")->keepFrame(true)->resize($width / 2)->__toString();
                        $returnArray["image"] = $imageData[0];
                        $returnArray["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                        $ratingCollection = Mage::getModel("rating/rating_option_vote")
                            ->getResourceCollection()
                            ->setReviewFilter($reviewId)
                            ->addRatingInfo($storeId)
                            ->setStoreFilter($storeId)
                            ->load();
                        $ratingArray = array();
                        foreach ($ratingCollection as $_rating) {
                            $eachRating                = array();
                            $eachRating["ratingCode"]  = Mage::helper("core")->stripTags($_rating->getRatingCode());
                            $eachRating["ratingValue"] = number_format($_rating->getPercent(), 2, ".", "");
                            $ratingArray[]             = $eachRating;
                        }
                        $returnArray["ratingData"] = $ratingArray;
                        $returnArray["reviewDate"] = Mage::helper("core")->__("Your Review (submitted on %s)", Mage::helper("core")->formatDate($review->getCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_LONG));
                        $returnArray["reviewDetail"] = Mage::helper("core")->stripTags($review->getDetail());
                        $reviews = Mage::getModel("review/review")
                            ->getResourceCollection()
                            ->addStoreFilter($storeId)
                            ->addEntityFilter("product", $product->getId())
                            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                            ->setDateOrder()
                            ->addRateVotes();
                        $ratings = array();
                        if (count($reviews) > 0) {
                            foreach ($reviews->getItems() as $review) {
                                foreach ($review->getRatingVotes() as $vote)
                                    $ratings[] = $vote->getPercent();
                            }
                        }
                        if (count($ratings) > 0)
                            $returnArray["rating"] = number_format((5 * (array_sum($ratings) / count($ratings))) / 100, 2, ".", "");
                        $returnArray["success"]    = true;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function saveReviewAction()     {
            $returnArray                 = array();
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
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $productId  = isset($wholeData["productId"])  ? $wholeData["productId"]  : 0;
                        $title      = isset($wholeData["title"])      ? $wholeData["title"]      : "";
                        $detail     = isset($wholeData["detail"])     ? $wholeData["detail"]     : "";
                        $nickname   = isset($wholeData["nickname"])   ? $wholeData["nickname"]   : "";
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $ratings    = isset($wholeData["ratings"])    ? $wholeData["ratings"]    : "[]";
                        if ($customerId == 0)
                            $customerId = null;
                        $ratings = Mage::helper("core")->jsonDecode($ratings);
                        $review = Mage::getModel("review/review");
                        $review->setEntityPkValue($productId);
                        $review->setStatusId(Mage_Review_Model_Review::STATUS_PENDING);
                        $review->setTitle($title);
                        $review->setDetail($detail);
                        $review->setEntityId(1);
                        $review->setStoreId($storeId);
                        if(!(bool)Mage::getStoreConfig("catalog/review/allow_guest") || $customerId != 0)
                            $review->setCustomerId($customerId);
                        $review->setNickname($nickname);
                        $review->setReviewId($review->getId());
                        $review->setStores(array($storeId));
                        $review->save();
                        foreach ($ratings as $ratingId => $optionId) {
                            Mage::getModel("rating/rating")
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customerId)
                                ->addOptionVote($optionId, $productId);
                        }
                        $review->aggregate();
                        $returnArray["message"] = Mage::helper("mobikul")->__("Your review has been accepted for moderation.");
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function removefromWishlistAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = Mage::helper("mobikul")->__("Item successfully deleted from wishlist.");
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
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $itemId       = isset($wholeData["itemId"])     ? $wholeData["itemId"]     : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $item = Mage::getModel("wishlist/item")->load($itemId);
                        $error = false;
                        if (!$item->getId())
                            $error = true;
                        $wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
                        if (!$wishlist)
                            $error = true;
                        $item->delete();
                        $wishlist->save();
                        if ($error){
                            $returnArray["message"] = Mage::helper("mobikul")->__("An error occurred while deleting the item from wishlist.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function wishlisttoCartAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["cartCount"]    = 0;
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
                        $qty          = isset($wholeData["qty"])        ? $wholeData["qty"]        : 1;
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $productId    = isset($wholeData["productId"])  ? $wholeData["productId"]  : 0;
                        $itemId       = isset($wholeData["itemId"])     ? $wholeData["itemId"]     : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $item         = Mage::getModel("wishlist/item")->load($itemId);
                        $wishlist     = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
                        if ($qty == "")
                            $qty = 1;
                        $options = Mage::getModel("wishlist/item_option")->getCollection()->addItemFilter(array($itemId));
                        $item->setOptions($options->getOptionsByItem($itemId));
                        $buyRequest = Mage::helper("catalog/product")->addParamsToBuyRequest(
                            array("item"=>$itemId, "qty"=>array($itemId=>$qty)),
                            array("current_config"=>$item->getBuyRequest())
                        );
                        $item->mergeBuyRequest($buyRequest);
                        $quoteCollection = Mage::getModel("sales/quote")
                            ->getCollection()
                            ->addFieldToFilter("is_active", true)
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addOrder("updated_at", "desc");
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
                        $status = true;
                        $product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($productId);
                        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                            $status = false;
                        if (!$product->isVisibleInSiteVisibility()) {
                            if ($product->getStoreId() == $storeId)
                                $status = false;
                        }
                        if (!$product->isSalable())
                            throw new Mage_Core_Exception(null, 901);
                        $buyRequest = $item->getBuyRequest();
                        try{
                            $params = array("related_product"=>null, "options"=>$buyRequest, "qty"=>$qty, "product_id"=>$productId);
                            $store = Mage::getSingleton("core/store")->load($storeId);
                            Mage::getModel("checkout/cart_product_api")->add($quoteId, array($params), $store);
                        }
                        catch (Exception $e) {
                            $returnArray["message"] = $e->getCustomMessage();
                            Mage::log($e, null, "mobikul.log");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $item->delete();
                        $wishlist->save();
                        Mage::helper("wishlist")->calculate();
                        $quote->collectTotals()->save();
                        $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;
                        $returnArray["message"] = Mage::helper("core")->__("Product(s) has successfully moved to cart.");
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
            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                    $returnArray["message"] = Mage::helper("core")->__("This product(s) is currently out of stock");
                    Mage::log($e, null, "mobikul.log");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                } elseif ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                    $returnArray["message"] = $e->getCustomMessage();
                    Mage::log($e, null, "mobikul.log");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                } else {
                    $returnArray["message"] = $e->getCustomMessage();
                    Mage::log($e, null, "mobikul.log");
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

        public function addressformDataAction()     {
            $returnArray                        = array();
            $returnArray["authKey"]             = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["success"]             = false;
            $returnArray["message"]             = "";
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
            $returnArray["prefixValue"]         = "";
            $returnArray["middleName"]          = "";
            $returnArray["suffixValue"]         = "";
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
                        $addressId    = isset($wholeData["addressId"])  ? $wholeData["addressId"]  : 0;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $customer     = Mage::getModel("customer/customer")->load($customerId);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        if ($addressId != 0) {
                            $address = Mage::getModel("customer/address")->load($addressId);
                            if ($customerId != 0) {
                                if ($customer->getDefaultBilling() == $addressId)
                                    $returnArray["addressData"]["isDefaultBilling"] = true;
                                else
                                    $returnArray["addressData"]["isDefaultBilling"] = false;
                                if ($customer->getDefaultShipping() == $addressId)
                                    $returnArray["addressData"]["isDefaultShipping"] = true;
                                else
                                    $returnArray["addressData"]["isDefaultShipping"] = false;
                            }
                            $addressData = $address->getData();
                            foreach ($addressData as $key => $addata) {
                                if ($addata != "")
                                    $returnArray["addressData"][$key] = $addata;
                                else
                                    $returnArray["addressData"][$key] = "";
                            }
                            $returnArray["addressData"]["street"] = $address->getStreet();
                        }
                        $countryCollection = Mage::getModel("directory/country")->getResourceCollection()->loadByStore()->toOptionArray(true);
                        unset($countryCollection[0]);
                        foreach ($countryCollection as $country) {
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
                            if (count($result) > 0) {
                                $eachCountry["states"] = $result;
                            }
                            $returnArray["countryData"][] = $eachCountry;
                        }
                        $returnArray["firstName"] = $customer->getFirstname();
                        $returnArray["lastName"] = $customer->getLastname();
                        $returnArray["streetLineCount"] = Mage::helper("customer/address")->getStreetLines();
                        $showPrefix = Mage::getStoreCOnfig("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"] = true;
                            $returnArray["isPrefixRequired"] = true;
                            $returnArray["prefixValue"] = is_null($customer->getPrefix()) ? "" : $customer->getPrefix();
                        }
                        elseif($showPrefix == "opt"){
                            $returnArray["isPrefixVisible"] = true;
                            $returnArray["prefixValue"] = is_null($customer->getPrefix()) ? "" : $customer->getPrefix();
                        }
                        $prefixOptions = Mage::getStoreCOnfig("customer/address/prefix_options");
                        if($prefixOptions != ""){
                            $returnArray["prefixHasOptions"] = true;
                            $returnArray["prefixOptions"] = explode(";", $prefixOptions);
                        }
                        $showMiddleName = Mage::getStoreCOnfig("customer/address/middlename_show");
                        if($showMiddleName == 1){
                            $returnArray["middleName"] = is_null($customer->getMiddlename()) ? "" : $customer->getMiddlename();
                            $returnArray["isMiddlenameVisible"] = true;
                        }
                        $showSuffix = Mage::getStoreCOnfig("customer/address/suffix_show");
                        if($showSuffix == "req"){
                            $returnArray["isSuffixVisible"] = true;
                            $returnArray["isSuffixRequired"] = true;
                            $returnArray["suffixValue"] = is_null($customer->getSuffix()) ? "" : $customer->getSuffix();
                        }
                        elseif($showSuffix == "opt"){
                            $returnArray["isSuffixVisible"] = true;
                            $returnArray["suffixValue"] = is_null($customer->getSuffix()) ? "" : $customer->getSuffix();
                        }
                        $suffixOptions = Mage::getStoreCOnfig("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"] = explode(";", $suffixOptions);
                        }
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function saveAddressAction()     {
            $returnArray                 = array();
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
                        $storeId     = isset($wholeData["storeId"])     ? $wholeData["storeId"]     : 1;
                        $customerId  = isset($wholeData["customerId"])  ? $wholeData["customerId"]  : 0;
                        $addressData = isset($wholeData["addressData"]) ? $wholeData["addressData"] : "[]";
                        $addressId   = isset($wholeData["addressId"])   ? $wholeData["addressId"]   : 0;
                        $addressDataObject = Mage::helper("core")->jsonDecode($addressData);
                        $addressData = array();
                        foreach ($addressDataObject as $key => $addressValue)
                            $addressData[$key] = $addressValue;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $customer = Mage::getModel("customer/customer")->load($customerId);
                        $customerSession = Mage::getSingleton("customer/session")->setCustomer($customer);
                        $address = Mage::getModel("customer/address");
                        if ($addressId != 0) {
                            $existsAddress = $customer->getAddressById($addressId);
                            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId())
                                $address->setId($existsAddress->getId());
                        }
                        $errors = array();
                        $addressForm = Mage::getModel("customer/form");
                        $addressForm->setFormCode("customer_address_edit")->setEntity($address);
                        $addressErrors = $addressForm->validateData($addressData);
                        if ($addressErrors !== true)
                            $errors = $addressErrors;
                        $addressForm->compactData($addressData);
                        $address->setCustomerId($customer->getId())->setIsDefaultBilling($addressData["default_billing"])->setIsDefaultShipping($addressData["default_shipping"]);
                        $addressErrors = $address->validate();
                        $address->save();
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["message"] = Mage::helper("mobikul")->__("The address has been saved.");
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function deleteAddressAction()     {
            $returnArray                 = array();
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
                        $addressId = isset($wholeData["addressId"]) ? $wholeData["addressId"] : 0;
                        $address   = Mage::getModel("customer/address")->load($addressId);
                        $address->delete();
                        $returnArray["message"] = Mage::helper("mobikul")->__("The address has been deleted.");
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

    }