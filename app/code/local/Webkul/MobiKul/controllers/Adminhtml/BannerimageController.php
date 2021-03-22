<?php

    class Webkul_MobiKul_Adminhtml_BannerimageController extends Mage_Adminhtml_Controller_Action {

        protected function _isAllowed(){
            return Mage::getSingleton("admin/session")->isAllowed("mobikul/banner_images");
        }

        public function indexAction() {
            $this->loadLayout()->_setActiveMenu("mobikul");
            $this->getLayout()->getBlock("head")->setTitle($this->__("Banner Manager"));
            $this->renderLayout();
        }

        public function editAction() {
            $id = $this->getRequest()->getParam("id");
            $model = Mage::getModel("mobikul/bannerimage")->load($id);
            if($model->getId() || $id == 0) {
                $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
                if(!empty($data))
                    $model->setData($data);
                Mage::register("bannerimage_data", $model);
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setTitle($this->__("Add/Edit Banner"));
                $this->_setActiveMenu("mobikul");
                $this->_addContent($this->getLayout()->createBlock("mobikul/adminhtml_bannerimage_edit"))
                        ->_addLeft($this->getLayout()->createBlock("mobikul/adminhtml_bannerimage_edit_tabs"));
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
                    $uploader->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir("media").DS."mobikul".DS."bannerimages";
                    $uploader->save($path, $fname);
                    $imagedata["filename"] = "mobikul".DS."bannerimages".DS.$fname;
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
                            $_helper = Mage::helper("mobikul");
                            $this->removeFile(Mage::getBaseDir("media").DS.$_helper->updateDirSepereator($data["filename"]["value"]));
                        }
                        $data["filename"] = "";
                    }
                    else
                        unset($data["filename"]);
                }
                $model = Mage::getModel("mobikul/bannerimage");
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
            if($this->getRequest()->getParam('id') > 0) {
                try {
                    $model = Mage::getModel("mobikul/bannerimage")->load($this->getRequest()->getParam("id"));
                    $_helper = Mage::helper("mobikul");
                    $filePath = Mage::getBaseDir("media").DS.$_helper->updateDirSepereator($model->getFilename());
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
                        $model = Mage::getModel("mobikul/bannerimage")->load($bannerId);
                        $_helper = Mage::helper("mobikul");
                        $filePath = Mage::getBaseDir("media").DS.$_helper->updateDirSepereator($model->getFilename());
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
                        Mage::getSingleton("mobikul/bannerimage")->load($bannerId)
                            ->setStatus($this->getRequest()->getParam("status"))
                            ->setIsMassupdate(true)
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
            $fileName = "bannerimages.csv";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_bannerimage_grid")->getCsv();
            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = "bannerimages.xml";
            $content = $this->getLayout()->createBlock("mobikul/adminhtml_bannerimage_grid")->getXml();
            $this->_sendUploadResponse($fileName, $content);
        }

        protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
            $response = $this->getResponse();
            $response->setHeader('HTTP/1.1 200 OK', '');
            $response->setHeader('Pragma', 'public', true);
            $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
            $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
            $response->setHeader('Last-Modified', date('r'));
            $response->setHeader('Accept-Ranges', 'bytes');
            $response->setHeader('Content-Length', strlen($content));
            $response->setHeader('Content-type', $contentType);
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