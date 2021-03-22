<?php

    class Webkul_MobiKul_Helper_Data extends Mage_Core_Helper_Data  {

        protected $_helper;

        public function translateInStore($storeId, $string)
        {
            $newLocaleCode = Mage::getStoreConfig(
                Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId
            );
            $initialEnvironmentInfo = Mage::getSingleton('core/app_emulation')
                ->startEnvironmentEmulation($storeId);
            Mage::app()->getLocale()->setLocaleCode($newLocaleCode);
            Mage::getSingleton('core/translate')->setLocale($newLocaleCode)->init(Mage_Core_Model_App_Area::AREA_FRONTEND, true);

            $translatedString = Mage::helper("mobikul")->__($string);

            Mage::getSingleton('core/app_emulation')
                ->stopEnvironmentEmulation($initialEnvironmentInfo);

            return $translatedString;
        }


        public function updateDirSepereator($path){
            return str_replace("\\", DS, $path);
        }

        public function getStoreIds() {
          $option = array();
          $allStores = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
          foreach($allStores as $k => $v){
              if ($k!=0){
                  $option[] = $v;
              }
          }
            return $option;
        }

        public function getStoreforGrid() {
            $allStores = Mage::getModel("core/store_group")->getCollection();
            $option = array();
            foreach($allStores as $_eachStore)
                $option[$_eachStore->getGroupId()] = $_eachStore->getName();
            return $option;
        }

        public function isAuthorized($authKey, $apiKey, $apiPassword)   {
            $returnArray = array();
            $returnArray["authKey"] = $authKey;
            $returnArray["responseCode"] = 0;
            $returnArray["message"] = "";
            $currentSessionId = Mage::getSingleton("core/session")->getEncryptedSessionId();
            if ($authKey == $currentSessionId) {
                $returnArray["responseCode"] = 1;
            } else {
                $configUserName = Mage::getStoreConfig("mobikul/configuration/apiusername");
                $configApiKey = Mage::getStoreConfig("mobikul/configuration/apikey");
                if (($apiKey == $configUserName) && ($apiPassword == $configApiKey)) {
                    $newSessionId = Mage::getSingleton("core/session")->getEncryptedSessionId();
                    $returnArray["authKey"] = $newSessionId;
                    $returnArray["responseCode"] = 2;
                } else {
                    $returnArray["responseCode"] = 3;
                    $returnArray["message"] = Mage::helper("api")->__("Unable to Authorize User.");
                }
            }
            return $returnArray;
        }

        public function resizeNCache($basePath="", $newPath="", $width=0, $height=0, $forCustomer=false){
            if (!is_file($newPath) || $forCustomer) {
                $imageObj = new Varien_Image($basePath);
                $imageObj->keepAspectRatio(false);
                $imageObj->backgroundColor(array(255, 255, 255));
                $imageObj->keepFrame(false);
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
            }
        }

        public function canReorder(Mage_Sales_Model_Order $order)   {
            if(!Mage::getStoreConfig("sales/reorder/allow", $order->getStore()))
                return false;
            else
                return $order->canReorder();

        }

    }
