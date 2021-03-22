<?php

    class Webkul_MobiKul_IndexController extends Mage_Core_Controller_Front_Action  {

        public function uploadProfilePicAction(){
            $returnArray            = array();
            $returnArray["url"]     = "";
            $returnArray["success"] = false;
            $returnArray["message"] = "";
            if(isset($_FILES)){
                $data = $this->getRequest()->getParams();
                $customerId = isset($data["customerId"]) ? $data["customerId"] : 0;
                $mFactor    = isset($data["mFactor"])    ? $data["mFactor"]    : 1;
                $width      = isset($data["width"])      ? $data["width"]      : 1000;
                // validate files
                $isValid    = $this->isvalidFile($_FILES);
                if (!$isValid) {
                    $returnArray["message"] = "Invalid Image.";
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
                $this->uploadPicture($_FILES, $customerId, $customerId."-profile", "profile");
                $this->resizeAndCache($width, $customerId, $mFactor, "profile");
            }
            else{
                $returnArray["message"] = "Invalid Image.";
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function uploadBannerPicAction(){
            $returnArray            = array();
            $returnArray["url"]     = "";
            $returnArray["success"] = false;
            $returnArray["message"] = "";
            if(isset($_FILES)){
                $data = $this->getRequest()->getParams();
                $customerId = isset($data["customerId"]) ? $data["customerId"] : 0;
                $mFactor    = isset($data["mFactor"])    ? $data["mFactor"]    : 1;
                // validate files
                $width      = isset($data["width"])      ? $data["width"]      : 1000;
                $isValid    = $this->isvalidFile($_FILES);
                if (!$isValid) {
                    $returnArray["message"] = "Invalid Image.";
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
                $this->uploadPicture($_FILES, $customerId, $customerId."-banner", "banner");
                $this->resizeAndCache($width, $customerId, $mFactor, "banner");
            }
            else{
                $returnArray["message"] = "Invalid Image.";
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }
        
        /**
         * This is used to validate the image files
         *
         * @param array $files
         * @return bool
         */
        private function isvalidFile($files)
        {
            $count = count($files);
            $flag = 0;
            foreach($files as $key => $image) {
               $contentType = mime_content_type($files[$key]['tmp_name']);
               $typeExtension= explode('/', $contentType);
               if (in_array(end($typeExtension),array("jpg", "jpeg")) && in_array(end(explode('.',$_FILES[$key]['name'])),array("jpg", "jpeg"))){
                    $flag = $flag + 1;
                } else
                if(end($typeExtension)== end(explode('.',$_FILES[$key]['name']))) {
                    $flag = $flag + 1;
                } else if (strpos($contentType,"image") !== false) {
                    $flag = $flag + 1;
                }              
            }
            if ($count == $flag) 
                return true; 
            return false;      
        }

        protected function uploadPicture($files, $customerId, $name, $signal){
            $target = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS;
            if(isset($files) && count($files) > 0) {
                $file = new Varien_Io_File();
                if(is_dir($target)){
                    $directories = glob($target."*" , GLOB_ONLYDIR);
                    foreach ($directories as $dir) {
                        $file->rmdir($dir, true);
                    }
                }
                $file->mkdir($target);
                foreach($files as $image) {
                    if($image["tmp_name"] != "") {
                        $splitname = explode(".", $image["name"]);
                        $nameOfFile = $name.time().".".end($splitname);
                        $finalTarget = $target.$nameOfFile;
                        move_uploaded_file($image["tmp_name"], $finalTarget);
                        $userImageModel = Mage::getModel("mobikul/userimage");
                        $collection = $userImageModel->getCollection()->addFieldToFilter("customer_id", $customerId);
                        if($collection->getSize() > 0){
                            foreach($collection as $value){
                                $loadedUserImageModel = $userImageModel->load($value->getId());
                                if($signal == "banner")
                                    $loadedUserImageModel->setBanner($nameOfFile);
                                if($signal == "profile")
                                    $loadedUserImageModel->setProfile($nameOfFile);
                                $loadedUserImageModel->save();
                            }
                        }
                        else{
                            if($signal == "banner")
                                $userImageModel->setBanner($nameOfFile);
                            if($signal == "profile")
                                $userImageModel->setProfile($nameOfFile);
                            $userImageModel->setCustomerId($customerId)->save();
                        }
                    }
                }
            }
        }

        protected function resizeAndCache($width=1000, $customerId, $mFactor=1, $signal){
            $returnArray            = array();
            $returnArray["url"]     = "";
            $returnArray["success"] = false;
            $returnArray["message"] = "";
            $this->getResponse()->setHeader("Content-type", "application/json");
            $collection = Mage::getModel("mobikul/userimage")->getCollection()->addFieldToFilter("customer_id", $customerId);
            $time = time();
            if($collection->getSize() > 0){
                foreach($collection as $value) {
                    if($signal == "banner" && $value->getBanner() != ""){
                        $basePath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getBanner();
                        $newUrl   = "";
                        if(is_file($basePath)){
                            list($w, $h, $type, $attr) = getimagesize($basePath);
                            $ratio = $w/$h;
                            $height = ($width/$ratio)*$mFactor;
                            $width *= $mFactor;
                            $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner()."?".$time;
                            $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner();
                            Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $width, $height, true);
                            $returnArray["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);
                        }
                        $returnArray["url"]     = $newUrl."?".$time;
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                    if($signal == "profile" && $value->getProfile() != ""){
                        $basePath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getProfile();
                        $ppHeight = $ppWidth = 144 * $mFactor;
                        $newUrl   = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$ppWidth."x".$ppHeight.DS.$value->getProfile();
                        if(is_file($basePath)){
                            $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$ppWidth."x".$ppHeight.DS.$value->getProfile();
                            Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $ppWidth, $ppHeight, true);
                            $returnArray["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath); 
                        }
                        $returnArray["url"]     = $newUrl."?".$time;
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                }
            }
        }
    }