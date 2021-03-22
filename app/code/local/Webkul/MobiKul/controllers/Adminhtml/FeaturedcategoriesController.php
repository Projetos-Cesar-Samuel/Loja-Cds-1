<?php

    class Webkul_MobiKul_Adminhtml_FeaturedcategoriesController extends Mage_Adminhtml_Controller_Action {

        protected function _isAllowed(){
            return Mage::getSingleton("admin/session")->isAllowed("mobikul/featured_categories");
        }

        public function indexAction() {
            $this->loadLayout()->_setActiveMenu("mobikul");
            $this->getLayout()->getBlock("head")->setTitle($this->__("Featured Categories Manager"));
            $this->renderLayout();
        }

        protected function _initCategory($getRootInstead = false)    {
            $this->_title($this->__("Catalog"))
                 ->_title($this->__("Categories"))
                 ->_title($this->__("Manage Categories"));
            $categoryId = (int) $this->getRequest()->getParam("category",false);
            $storeId    = (int) $this->getRequest()->getParam("store");
            $category = Mage::getModel("catalog/category");
            $category->setStoreId($storeId);
            if($categoryId) {
                $category->load($categoryId);
                if ($storeId) {
                    $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    if (!in_array($rootId, $category->getPathIds())) {
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
            if ($activeTabId = (string) $this->getRequest()->getParam("active_tab_id"))
                Mage::getSingleton("admin/session")->setActiveTabId($activeTabId);
            Mage::register("category", $category);
            Mage::register("current_category", $category);
            Mage::getSingleton("cms/wysiwyg_config")->setStoreId($this->getRequest()->getParam("store"));
            return $category;
        }

        public function categoriesJsonAction()    {
            if ($categoryId = (int) $this->getRequest()->getPost("category")) {
                $this->getRequest()->setParam("category", $categoryId);
                if(!$category = $this->_initCategory())
                    return;
                $this->getResponse()->setBody($this->getLayout()->createBlock("adminhtml/catalog_category_tree")->getTreeJson($category));
            }
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("mobikul/featuredcategories")->load($id);
            if($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if(!empty($data))
                    $model->setData($data);
                Mage::register("featuredcategories_data", $model);
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setTitle($this->__("Add/Edit Featured Categories"));
                $this->_setActiveMenu("mobikul");
                $this->_addContent($this->getLayout()->createBlock("mobikul/adminhtml_featuredcategories_edit"))
                        ->_addLeft($this->getLayout()->createBlock("mobikul/adminhtml_featuredcategories_edit_tabs"));
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

        public function saveAction() {
            $imagedata = array();
            if(!empty($_FILES["filename"]["name"])) {
                $validation =  Mage::helper("mobikul/validation");
                $flag =  $validation->validMine($_FILES, getimagesize($_FILES["filename"]['tmp_name']), 'filename');
                if ($flag ==  false) {
                    Mage::getSingleton("adminhtml/session")->addError(Mage::helper("mobikul")->__("Invalid File Type"));
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
                try {
                    $ext = substr($_FILES["filename"]["name"], strrpos($_FILES["filename"]["name"], ".") + 1);
                    $fname = "File-".time().".".$ext;
                    $uploader = new Varien_File_Uploader("filename");
                    $uploader->setAllowedExtensions(array("jpg", "JPG", "jpeg", "JPEG", "gif", "GIF", "png", "PNG"));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir("media").DS."mobikul".DS."featuredcategories";
                    $uploader->save($path, $fname);
                    $imagedata["filename"] = "mobikul/featuredcategories/".$fname;
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost()) {
                $data["store_id"] = implode(",", $data["store_id"]);
                if(!empty($imagedata["filename"]))
                    $data["filename"] = $imagedata["filename"];
                else {
                    if(isset($data["filename"]["delete"]) && $data["filename"]["delete"] == 1) {
                        if($data["filename"]["value"] != "") {
                            $this->removeFile(Mage::getBaseDir("media").DS.$data["filename"]["value"]);
                        }
                        $data["filename"] = "";
                    }
                    else
                        unset($data["filename"]);
                }
                $model = Mage::getModel("mobikul/featuredcategories");
                $model->setData($data)->setId($this->getRequest()->getParam("id"));
                try {
                    if($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL)
                        $model->setCreatedTime(now())->setUpdateTime(now());
                    else
                        $model->setUpdateTime(now());
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
                    $model = Mage::getModel("mobikul/featuredcategories")->load($this->getRequest()->getParam("id"));
                    $filePath = Mage::getBaseDir("media").DS.$model->getFilename();
                    $model->delete();
                    $this->removeFile($filePath);
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
            $bannerIds = $this->getRequest()->getParam("banners");
            if(!is_array($bannerIds))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            else {
                try {
                    foreach($bannerIds as $bannerId) {
                        $model = Mage::getModel("mobikul/featuredcategories")->load($bannerId);
                        $filePath = Mage::getBaseDir("media").DS.$model->getFilename();
                        $model->delete();
                        $this->removeFile($filePath);
                    }
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Total of %d record(s) were successfully deleted", count($bannerIds)));
                }
                catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function massStatusAction() {
            $bannerIds = $this->getRequest()->getParam("banners");
            if(!is_array($bannerIds))
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            else {
                try {
                    foreach($bannerIds as $bannerId) {
                        Mage::getSingleton("mobikul/featuredcategories")->load($bannerId)
                            ->setStatus($this->getRequest()->getParam("status"))
                            ->save();
                    }
                    $this->_getSession()->addSuccess($this->__("Total of %d record(s) were successfully updated", count($bannerIds)));
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function exportCsvAction() {
            $fileName = "featuredcategories.csv";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_featuredcategories_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "featuredcategories.xml";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_featuredcategories_grid")->getXml();
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