<?php

    class Webkul_MobiKul_Adminhtml_NotificationController extends Mage_Adminhtml_Controller_Action    {

        protected function _isAllowed()     {
            return Mage::getSingleton("admin/session")->isAllowed("mobikul/notification_alert");
        }

        public function indexAction()   {
            $this->loadLayout()->_setActiveMenu("mobikul");
            $this->getLayout()->getBlock("head")->setTitle($this->__("Notification Manager"));
            $this->renderLayout();
        }

        public function editAction()   {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("mobikul/notification")->load($id);
            if ($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if (!empty($data)) {
                    $model->setData($data);
                }
                Mage::register("notification_data", $model);
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setTitle($this->__("Add/Edit Notification"));
                $this->_setActiveMenu("mobikul");
                $this->_addContent($this->getLayout()->createBlock("mobikul/adminhtml_notification_edit"))
                        ->_addLeft($this->getLayout()->createBlock("mobikul/adminhtml_notification_edit_tabs"));
                $this->renderLayout();
            } else {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Item does not exist"));
                $this->_redirect("*/*/");
            }
        }

        public function newAction()   {
            $this->_forward("edit");
        }

        public function saveAction()   {
            if ($data = $this->getRequest()->getPost()) {
                if (isset($data["push"])) {
                    $model = Mage::getModel("mobikul/notification")->load($this->getRequest()->getParam("id"));
// Push Notification ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $bannerUrl = "";
                    if ($model->getFilename() != "")
                        $bannerUrl = Mage::getBaseUrl("media").$model->getFilename();
                    $message = array(
                        "title"            => $model->getTitle(),
                        "message"          => $model->getContent(),
                        "id"               => $model->getId(),
                        "notificationType" => $model->getType(),
                        "banner_url"       => $bannerUrl,
                        "store_id"         => $model->getStoreId(),
                        "body"             => $model->getContent(),
                        "sound"            => "default"
                    );
                    if ($model->getType() == "category" && $model->getProCatId() != "") {
// for category /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $message["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($model->getProCatId(), "name", $model->getStoreId());
                        $message["categoryId"] = $model->getProCatId();
                    } elseif ($model->getType() == "product" && $model->getProCatId() != "") {
// for product //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $message["productName"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($model->getProCatId(), "name", $model->getStoreId());
                        $message["productId"] = $model->getProCatId();
                    }
                    $url     = "https://fcm.googleapis.com/fcm/send";
                    $authKey = Mage::getStoreConfig("mobikul/notification/apikey");
                    $iostopic   = Mage::getStoreConfig("mobikul/notification/iostopic");
                    $androidtopic   = Mage::getStoreConfig("mobikul/notification/androidtopic");
                    if ($authKey == "" || ($iostopic == "" && $androidtopic == "" )) {
                        Mage::getSingleton("adminhtml/session")->addNotice($this->__("Please fill the FCM details."));
                    } else {
                        $headers = array(
                            "Authorization: key=".$authKey,
                            "Content-Type: application/json",
                        );
                        if($iostopic != ""){
                            $fields = array(
                                "to"                => "/topics/".$iostopic,
                                "data"              => $message,
                                "notification"      => $message,
                                "priority"          => "high",
                                "content_available" => true,
                                "time_to_live"      => 30,
                                "delay_while_idle"  => true
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, Mage::helper("core")->jsonEncode($fields));
                            $iosResult = Mage::helper("core")->jsonDecode(curl_exec($ch));
                            curl_close($ch);
                        }
                        if($androidtopic != ""){
                            $fields = array(
                                "to"                => "/topics/".$androidtopic,
                                "data"              => $message,
                                "priority"          => "high",
                                "content_available" => true,
                                "time_to_live"      => 30,
                                "delay_while_idle"  => true
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, Mage::helper("core")->jsonEncode($fields));
                            $androidResult = Mage::helper("core")->jsonDecode(curl_exec($ch));
                            curl_close($ch);
                        }
                        Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Notification successfully sent"));
                    }
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                    return;
                } else {
                    if (!empty($_FILES["filename"]["name"])) {
                        $imagedata = array();
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
                            $uploader->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"));
                            $uploader->setAllowRenameFiles(true);
                            $uploader->setFilesDispersion(false);
                            $path = Mage::getBaseDir("media").DS."mobikul".DS."notificationbanners";
                            $uploader->save($path, $fname);
                            $imagedata["filename"] = "mobikul/notificationbanners/".$fname;
                        } catch (Exception $e) {
                            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                            $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                            return;
                        }
                    }
                    if (!empty($imagedata["filename"])) {
                        $data["filename"] = $imagedata["filename"];
                    } else {
                        if (isset($data["filename"]["delete"]) && $data["filename"]["delete"] == 1) {
                            if ($data["filename"]["value"] != "") {
                                $_helper = Mage::helper("mobikul");
                                $this->removeFile(Mage::getBaseDir("media").DS.$_helper->updateDirSepereator($data["filename"]["value"]));
                            }
                            $data["filename"] = "";
                        } else {
                            unset($data["filename"]);
                        }
                    }
                    $data["store_id"] = implode(",", $data["store_id"]);
                    $model = Mage::getModel("mobikul/notification");
                    if ($data["type"] == "product") {
// for product type notification ////////////////////////////////////////////////////////////////////////////////////////////////
                        if (Mage::getModel("catalog/product")->load($data["pro_cat_id"])->getSku() == "") {
                            Mage::getSingleton("adminhtml/session")->setNotificationData($data);
                            Mage::getSingleton("adminhtml/session")->addNotice($this->__("Invalid Product Id"));
                            if ($this->getRequest()->getParam("id"))
                                $this->_redirect("*/*/edit", array("id" => $model->getId()));
                            else
                                $this->_redirect("*/*/new");
                            return;
                        }
                    } elseif ($data["type"] == "category") {
// for category type notification ///////////////////////////////////////////////////////////////////////////////////////////////
                        if (Mage::getModel("catalog/category")->load($data["pro_cat_id"])->getName() == "") {
                            Mage::getSingleton("adminhtml/session")->setNotificationData($data);
                            Mage::getSingleton("adminhtml/session")->addNotice($this->__("Invalid Category Id"));
                            if ($this->getRequest()->getParam("id"))
                                $this->_redirect("*/*/edit", array("id" => $model->getId()));
                            else
                                $this->_redirect("*/*/new");
                            return;
                        }
                    } elseif ($data["type"] == "custom") {
// for custom collection type notification //////////////////////////////////////////////////////////////////////////////////////
                        if ($this->getRequest()->getPost("collection_type") == "product_attribute") {
                            $attribute = $this->getRequest()->getPost("attribute");
                            if (count($attribute) > 0) {
                                $data["filter_data"] = serialize($attribute);
                            } else {
                                Mage::getSingleton("adminhtml/session")->setNotificationData($data);
                                Mage::getSingleton("adminhtml/session")->addNotice($this->__("Please provide product count"));
                                if ($this->getRequest()->getParam("id"))
                                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                                else
                                    $this->_redirect("*/*/new");
                                return;
                            }
                        }
                        if ($this->getRequest()->getPost("collection_type") == "product_ids") {
                            $productIds = $this->getRequest()->getPost("productIds");
                            if ($productIds == "") {
                                Mage::getSingleton("adminhtml/session")->setNotificationData($data);
                                Mage::getSingleton("adminhtml/session")->addNotice($this->__("Please provide few product Ids"));
                                if ($this->getRequest()->getParam("id"))
                                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                                else
                                    $this->_redirect("*/*/new");
                                return;
                            } else {
                                $data["filter_data"] = serialize($productIds);
                            }
                        }
                        if ($this->getRequest()->getPost("collection_type") == "product_new") {
                            $newProductCount = $this->getRequest()->getPost("newProductCount");
                            if ($newProductCount == "") {
                                Mage::getSingleton("adminhtml/session")->setNotificationData($data);
                                Mage::getSingleton("adminhtml/session")->addNotice($this->__("Please provide product count"));
                                if ($this->getRequest()->getParam("id"))
                                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                                else
                                    $this->_redirect("*/*/new");
                                return;
                            } else
                                $data["filter_data"] = serialize($newProductCount);
                        }
                    }
                    $model->setData($data)->setId($this->getRequest()->getParam("id"));
                    try {
                        if ($model->getCreatedTime == null || $model->getUpdateTime() == null)
                            $model->setCreatedTime(now())->setUpdateTime(now());
                        else
                            $model->setUpdateTime(now());
                        $model->save();
                        Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully saved"));
                        Mage::getSingleton("adminhtml/session")->setFormData(false);
                        if ($this->getRequest()->getParam("back")) {
                            $this->_redirect("*/*/edit", array("id" => $model->getId()));
                            return;
                        }
                        $this->_redirect("*/*/");
                        return;
                    } catch (Exception $e) {
                        Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                        Mage::getSingleton("adminhtml/session")->setFormData($data);
                        $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                        return;
                    }
                }
            }
            Mage::getSingleton("adminhtml/session")->addError($this->__("Unable to find item to save"));
            $this->_redirect("*/*/");
        }

        public function deleteAction()        {
            if ($this->getRequest()->getParam("id") > 0) {
                try {
                    $model = Mage::getModel("mobikul/notification")->load($this->getRequest()->getParam("id"));
                    $model->delete();
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Item was successfully deleted"));
                    $this->_redirect("*/*/");
                } catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                    $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                }
            }
            $this->_redirect("*/*/");
        }

        public function massDeleteAction()        {
            $notificationIds = $this->getRequest()->getParam("notifications");
            if (!is_array($notificationIds)) {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            } else {
                try {
                    foreach ($notificationIds as $notificationId) {
                        $model = Mage::getModel("mobikul/notification")->load($notificationId);
                        $model->delete();
                    }
                    Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Total of %d notification(s) were successfully deleted", count($notificationIds)));
                } catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function massPushAction()        {
            $notificationIds = $this->getRequest()->getParam("notifications");
            if (!is_array($notificationIds)) {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            } else {
                try {
                    $result = true;
                    foreach ($notificationIds as $notificationId) {
                        $model = Mage::getModel("mobikul/notification")->load($notificationId);
// Push Notification ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($model->getFilename() != "")
                            $bannerUrl = Mage::getBaseUrl("media").$model->getFilename();
                        else
                            $bannerUrl = "";
                        $message = array(
                            "title"            => $model->getTitle(),
                            "message"          => $model->getContent(),
                            "id"               => $model->getId(),
                            "notificationType" => $model->getType(),
                            "banner_url"       => $bannerUrl,
                            "store_id"         => $model->getStoreId(),
                            "body"             => $model->getContent(),
                            "sound"            => "default"
                        );
                        if ($model->getType() == "category" && $model->getProCatId() != "") {
// for category /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                            $message["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($model->getProCatId(), "name", $model->getStoreId());
                            $message["categoryId"] = $model->getProCatId();
                        } elseif ($model->getType() == "product" && $model->getProCatId() != "") {
// for product //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                            $message["productName"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($model->getProCatId(), "name", $model->getStoreId());
                            $message["productId"] = $model->getProCatId();
                        }
                        $url     = "https://fcm.googleapis.com/fcm/send";
                        $authKey = Mage::getStoreConfig("mobikul/notification/apikey");
                        $iostopic   = Mage::getStoreConfig("mobikul/notification/iostopic");
                        $androidtopic   = Mage::getStoreConfig("mobikul/notification/androidtopic");
                        if ($authKey == "" || ($iostopic == "" && $androidtopic == "")) {
                            Mage::getSingleton("adminhtml/session")->addNotice($this->__("Please fill the FCM details."));
                            $this->_redirect("*/*/index");
                            return;
                        } else {
                            $headers = array(
                                "Authorization: key=".$authKey,
                                "Content-Type: application/json",
                            );
                            if($iostopic != ""){
                                $fields = array(
                                    "to"                => "/topics/".$iostopic,
                                    "data"              => $message,
                                    "notification"      => $message,
                                    "priority"          => "high",
                                    "content_available" => true,
                                    "time_to_live"      => 30,
                                    "delay_while_idle"  => true
                                );
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, Mage::helper("core")->jsonEncode($fields));
                                $iosResult = Mage::helper("core")->jsonDecode(curl_exec($ch));
                                curl_close($ch);
                            }
                            if($androidtopic != ""){
                                $fields = array(
                                    "to"                => "/topics/".$androidtopic,
                                    "data"              => $message,
                                    "priority"          => "high",
                                    "content_available" => true,
                                    "time_to_live"      => 30,
                                    "delay_while_idle"  => true
                                );
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, Mage::helper("core")->jsonEncode($fields));
                                $androidResult = Mage::helper("core")->jsonDecode(curl_exec($ch));
                                curl_close($ch);
                            }
                        }
                        if ($result == false) {
                            $this->_redirect("*/*/index");
                            return;
                        }
                        Mage::getSingleton("adminhtml/session")->addSuccess($this->__("Total of %d notification(s) were successfully pushed", count($notificationIds)));
                    }
                } catch (Exception $e) {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function massStatusAction()        {
            $notificationIds = $this->getRequest()->getParam("notifications");
            if (!is_array($notificationIds)) {
                Mage::getSingleton("adminhtml/session")->addError($this->__("Please select item(s)"));
            } else {
                try {
                    foreach ($notificationIds as $notificationId) {
                        Mage::getSingleton("mobikul/notification")->load($notificationId)
                            ->setStatus($this->getRequest()->getParam("status"))
                            ->setIsMassupdate(true)
                            ->save();
                    }
                    $this->_getSession()->addSuccess($this->__("Total of %d notification(s) were successfully updated", count($notificationIds)));
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
            $this->_redirect("*/*/index");
        }

        public function exportCsvAction()        {
            $fileName = "notifications.csv";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_notification_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction()        {
            $fileName = "notifications.xml";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_notification_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        protected function _sendUploadResponse($fileName, $content, $contentType = "application/octet-stream")        {
            $response = $this->getResponse();
            $response->setHeader("HTTP/1.1 200 OK", "");
            $response->setHeader("Pragma", "public", true);
            $response->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
            $response->setHeader("Content-Disposition", "attachment; filename=".$fileName);
            $response->setHeader("Last-Modified", date("r"));
            $response->setHeader("Accept-Ranges", "bytes");
            $response->setHeader("Content-Length", strlen($content));
            $response->setHeader("Content-type", $contentType);
            $response->setBody($content);
            $response->sendResponse();
        }

        protected function removeFile($file)        {
            try {
                $io = new Varien_Io_File();
                $result = $io->rmdir($file, true);
            } catch (Exception $e) {}
        }

        public function getAttributeHtmlAction()        {
            $attributeCode = $this->getRequest()->getParam("attributeCode");
            $attribute = Mage::getModel("eav/entity_attribute")->loadByCode("catalog_product", $attributeCode);
            $returnArray = array();
            if ($attributeCode == "type_id") {
                $returnArray["type"] = "type_id";
                $returnArray["options"] = Mage::getModel("catalog/product_type")->getOptionArray();
            } elseif ($attributeCode == "category_ids") {
                $returnArray["type"] = "category_ids";
            } elseif ($attributeCode == "attribute_set_id") {
                $returnArray["type"] = "attribute_set_id";
                $entityTypeId = Mage::getResourceModel("catalog/product")->getTypeId();
                $attributeSetCollection = Mage::getResourceModel("eav/entity_attribute_set_collection")->setEntityTypeFilter($entityTypeId);
                $returnArray["options"] = $attributeSetCollection->getData();
            } elseif (in_array($attribute->getFrontendInput(), array("textarea", "text", "price"))) {
                $returnArray["type"] = "text";
            } elseif (in_array($attribute->getFrontendInput(), array("select", "multiselect"))) {
                $returnArray["type"] = "multiselect";
                $allOptions = $attribute->getSource()->getAllOptions(true, true);
                $tempArr = array();
                foreach ($allOptions as $value) {
                    if ($value["value"] != "") {
                        $tempArr[] = $value;
                    }
                }
                $returnArray["options"] = $tempArr;
            }
            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
        }

        public function getCategoryTreeHtmlAction()        {
            Mage::register("id", $this->getRequest()->getParam("id"));
            $categoryTreeBlock = $this->getLayout()->createBlock("mobikul/adminhtml_notification_edit_tab_categories");
            $this->getResponse()->setBody($categoryTreeBlock->toHtml());
        }

        protected function _initCategory($getRootInstead = false)        {
            $this->_title($this->__("Catalog"))
                 ->_title($this->__("Categories"))
                 ->_title($this->__("Manage Categories"));
            $categoryId = (int) $this->getRequest()->getParam("category", false);
            $storeId = (int) $this->getRequest()->getParam("store");
            $category = Mage::getModel("catalog/category");
            $category->setStoreId($storeId);
            if ($categoryId) {
                $category->load($categoryId);
                if ($storeId) {
                    $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    if (!in_array($rootId, $category->getPathIds())) {
// load root category instead wrong one /////////////////////////////////////////////////////////////////////////////////////////
                        if ($getRootInstead) {
                            $category->load($rootId);
                        } else {
                            $this->_redirect("*/*/", array("_current" => true, "id" => null));
                            return false;
                        }
                    }
                }
            }
            if ($activeTabId = (string) $this->getRequest()->getParam("active_tab_id")) {
                Mage::getSingleton("admin/session")->setActiveTabId($activeTabId);
            }
            Mage::register("category", $category);
            Mage::register("current_category", $category);
            Mage::getSingleton("cms/wysiwyg_config")->setStoreId($this->getRequest()->getParam("store"));
            return $category;
        }

        public function categoriesJsonAction()        {
            if ($categoryId = (int) $this->getRequest()->getPost("category")) {
                $this->getRequest()->setParam("category", $categoryId);
                if (!$category = $this->_initCategory()) {
                    return;
                }
                $this->getResponse()->setBody($this->getLayout()->createBlock("adminhtml/catalog_category_tree")->getTreeJson($category));
            }
        }

        public function getProductGridHtmlAction()        {
            Mage::register("id", $this->getRequest()->getParam("id"));
            $productGridBlock = $this->getLayout()->createBlock("mobikul/adminhtml_notification_edit_tab_grid");
            $this->getResponse()->setBody($productGridBlock->toHtml());
        }

        public function gridAction()        {
            Mage::register("id", $this->getRequest()->getParam("id"));
            if (!$category = $this->_initCategory(true)) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock("mobikul/adminhtml_notification_edit_tab_grid", "notification.product.grid")->toHtml()
            );
        }

    }