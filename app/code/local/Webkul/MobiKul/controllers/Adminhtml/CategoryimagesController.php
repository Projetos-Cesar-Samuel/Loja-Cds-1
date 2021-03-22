<?php

    class Webkul_MobiKul_Adminhtml_CategoryimagesController extends Mage_Adminhtml_Controller_Action {

        protected function _isAllowed(){
            return Mage::getSingleton("admin/session")->isAllowed("mobikul/categories_bannernimages");
        }

        public function indexAction() {
            $this->loadLayout()->_setActiveMenu("mobikul");
            $this->getLayout()->getBlock("head")->setTitle($this->__("Category's Banner n Image Manager"));
            $this->renderLayout();
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("mobikul/categoryimages")->load($id);
            if($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if(!empty($data))
                    $model->setData($data);
                Mage::register("categoryimages_data", $model);
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setTitle($this->__("Add/Manage Category's Banners n Images"));
                $this->_setActiveMenu("mobikul");
                $this->_addContent($this->getLayout()->createBlock("mobikul/adminhtml_categoryimages_edit"))
                        ->_addLeft($this->getLayout()->createBlock("mobikul/adminhtml_categoryimages_edit_tabs"));
                $this->renderLayout();
            }
            else {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function newAction() {
            $this->_forward("edit");
        }

        protected function _initCategory($getRootInstead = false)    {
            $this->_title($this->__("Catalog"))
                 ->_title($this->__("Categories"))
                 ->_title($this->__("Manage Categories"));
            $categoryId = (int) $this->getRequest()->getParam("category",false);
            $storeId = (int) $this->getRequest()->getParam("store");
            $category = Mage::getModel("catalog/category");
            $category->setStoreId($storeId);
            if($categoryId) {
                $category->load($categoryId);
                if($storeId) {
                    $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    if(!in_array($rootId, $category->getPathIds())) {
                        // load root category instead wrong one
                        if($getRootInstead)
                            $category->load($rootId);
                        else {
                            $this->_redirect("*/*/", array("_current"=>true, "id"=>null));
                            return false;
                        }
                    }
                }
            }
            if($activeTabId = (string) $this->getRequest()->getParam("active_tab_id"))
                Mage::getSingleton("admin/session")->setActiveTabId($activeTabId);
            Mage::register("category", $category);
            Mage::register("current_category", $category);
            Mage::getSingleton("cms/wysiwyg_config")->setStoreId($this->getRequest()->getParam("store"));
            return $category;
        }

        public function categoriesJsonAction()    {
            if($categoryId = (int) $this->getRequest()->getPost("category")) {
                $this->getRequest()->setParam("category", $categoryId);
                if(!$category = $this->_initCategory())
                    return;
                $this->getResponse()->setBody($this->getLayout()->createBlock("mobikul/adminhtml_catalog_category_tree")->getTreeJson($category));
            }
        }
        
        public function saveAction() {
            $imagedata = array();
            if($data = $this->getRequest()->getPost()) {
                $categoryCollection = Mage::getModel("mobikul/categoryimages")->getCollection()->addFieldToFilter("category_id", $data["category_id"]);
                foreach($categoryCollection as $category) {
                    if($category->getCategoryId() == $data["category_id"] && !isset($data["banner"]) && !isset($data["icon"])){
                        Mage::getSingleton("adminhtml/session")->addError("You can not save same category again");
                        $this->_redirect("*/*/new");
                        return;
                    }
                }
                if(empty($_FILES["banner"]["name"]) && empty($_FILES["icon"]["name"]) && !isset($data["banner"]) && !isset($data["icon"])) {
                    Mage::getSingleton("adminhtml/session")->addError("Please select atleast one image before saving");
                    $this->_redirect("*/*/new");
                    return;
                }
                if(!empty($_FILES["banner"]["name"])) {
                    $validation =  Mage::helper("mobikul/validation");
                    $flag =  $validation->validMine($_FILES, getimagesize($_FILES["banner"]['tmp_name']), 'banner');
                    if ($flag ==  false) {
                        Mage::getSingleton("adminhtml/session")->addError(Mage::helper("mobikul")->__("Invalid File Type"));
                        $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                        return;
                    }
                    try {
                        $ext = substr($_FILES["banner"]["name"], strrpos($_FILES["banner"]["name"], ".") + 1);
                        $bannerName = "Banner-".time().".".$ext;
                        $uploader = new Varien_File_Uploader("banner");
                        $uploader->setAllowedExtensions(array("jpg", "JPG", "jpeg", "JPEG", "gif", "GIF", "png", "PNG"));
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir("media").DS."mobikul".DS."categoryimages";
                        $uploader->save($path, $bannerName);
                        $imagedata["banner"] = "mobikul/categoryimages/".$bannerName;
                    }
                    catch (Exception $e) {
                        Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                        $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                        return;
                    }
                }
                if(!empty($_FILES["icon"]["name"])) {
                    try {
                        $ext = substr($_FILES["icon"]["name"], strrpos($_FILES["icon"]["name"], ".") + 1);
                        $iconName = "Icon-".time().".".$ext;
                        $uploader = new Varien_File_Uploader("icon");
                        $uploader->setAllowedExtensions(array("jpg", "JPG", "jpeg", "JPEG", "gif", "GIF", "png", "PNG"));
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir("media").DS."mobikul".DS."categoryimages";
                        $uploader->save($path, $iconName);
                        $imagedata["icon"] = "mobikul/categoryimages/".$iconName;
                    }
                    catch (Exception $e) {
                        Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                        $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                        return;
                    }
                }
                if(!empty($imagedata["banner"]))
                    $data["banner"] = $imagedata["banner"];
                else {
                    if(isset($data["banner"]["delete"]) && $data["banner"]["delete"] == 1) {
                        if($data["banner"]["value"] != "") {
                            $this->removeFile(Mage::getBaseDir("media").DS.$data["banner"]["value"]);
                        }
                        $data["banner"] = "";
                    }
                    else
                        unset($data["banner"]);
                }
                if(!empty($imagedata["icon"]))
                    $data["icon"] = $imagedata["icon"];
                else {
                    if(isset($data["icon"]["delete"]) && $data["icon"]["delete"] == 1) {
                        if($data["icon"]["value"] != "") {
                            $this->removeFile(Mage::getBaseDir("media").DS.$data["icon"]["value"]);
                        }
                        $data["icon"] = "";
                    }
                    else
                        unset($data["icon"]);
                }
                if($data["category_id"] != "")
                    $data["category_name"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($data["category_id"], "name", (int)$this->getRequest()->getParam("store"));
                $model = Mage::getModel("mobikul/categoryimages");
                $model->setData($data)->setId($this->getRequest()->getParam("id"));
                try {
                    $model->save();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setFormData(false);
                    if($this->getRequest()->getParam("back")) {
                        $this->_redirect("*/*/edit", array("id" => $model->getId()));
                        return;
                    }
                    $this->_redirect("*/*/");
                    return;
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    Mage::getSingleton("adminhtml/session")->setFormData($data);
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            Mage::getSingleton("adminhtml/session")->addError($this->__("Unable to find item to save"));
            $this->_redirect("*/*/");
        }

        public function deleteAction() {
            if($this->getRequest()->getParam("id") > 0) {
                try {
                    $model = Mage::getModel("mobikul/categoryimages")->load($this->getRequest()->getParam("id"));
                    $banner = $model->getBanner();
                    $icon = $model->getIcon();
                    $model->delete();
                    if($banner != ""){
                        $filePath = Mage::getBaseDir("media").DS.$banner;
                        $this->removeFile($filePath);
                    }
                    if($icon != ""){
                        $filePath = Mage::getBaseDir("media").DS.$icon;
                        $this->removeFile($filePath);
                    }
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully deleted"));
                    $this->_redirect("*/*/");
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                }
            }
            $this->_redirect("*/*/");
        }

        public function massDeleteAction() {
            $categoryIds = $this->getRequest()->getParam("categoryIds");
            if(!is_array($categoryIds))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            else {
                try {
                    foreach($categoryIds as $categoryId) {
                        $model = Mage::getModel("mobikul/categoryimages")->load($categoryId);
                        $banner = $model->getBanner();
                        $icon = $model->getIcon();
                        $model->delete();
                        if($banner != ""){
                            $filePath = Mage::getBaseDir("media").DS.$banner;
                            $this->removeFile($filePath);
                        }
                        if($icon != ""){
                            $filePath = Mage::getBaseDir("media").DS.$icon;
                            $this->removeFile($filePath);
                        }
                    }
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Total of %d record(s) were successfully deleted", count($categoryIds)));
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function exportCsvAction() {
            $fileName = "categoryimages.csv";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_categoryimages_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "categoryimages.xml";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_categoryimages_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        protected function _sendUploadResponse($fileName, $content, $contentType="application/octet-stream") {
            $response = $this->getResponse();
            $response->setHeader("HTTP/1.1 200 OK", "");
            $response->setHeader("Pragma", "public", true);
            $response->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
            $response->setHeader("Content-Disposition", "attachment; filename=" . $fileName);
            $response->setHeader("Last-Modified", date("r"));
            $response->setHeader("Accept-Ranges", "bytes");
            $response->setHeader("Content-Length", strlen($content));
            $response->setHeader("Content-type", $contentType);
            $response->setBody($content);
            $response->sendResponse();
        }

        protected function removeFile($file) {
            if(is_file($file)){
                try {
                    $io = new Varien_Io_File();
                    $result = $io->rmdir($file, true);
                }
                catch (Exception $e) {}
            }
        }

    }