<?php

    class Webkul_MobiKul_DownloadController extends Mage_Core_Controller_Front_Action   {

        public function indexAction()   {
            $hash        = $this->getRequest()->getParam("hash");
            $authKey     = $this->getRequest()->getHeader("authKey");
            $apiKey      = $this->getRequest()->getHeader("apiKey");
            $apiPassword = $this->getRequest()->getHeader("apiPassword");
            $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
            if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                $linkFile          = "";
                $fileName          = "";
                $linkPurchasedItem = Mage::getModel("downloadable/link_purchased_item")->load($hash, "link_hash");
                $downloadsLeft     = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();
                $status            = $linkPurchasedItem->getStatus();
                if($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)) {
                    if($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                        $linkFile  = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile());
                        $fileArray = explode(DS, $linkFile);
                        $fileName  = end($fileArray);
                        $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);
                        if($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1))
                            $linkPurchasedItem->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED);
                        $linkPurchasedItem->save();
                    }
                }
                header("Content-Description: File Transfer");
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=".$fileName);
                header("Content-Transfer-Encoding: binary");
                header("Expires: 0");
                header("Accept-Ranges: bytes");
                header("Cache-Control: max-age=604800");
                header("Pragma: public");
                header("Content-Length: " . filesize($linkFile));
                ob_clean();
                flush();
                readfile($linkFile);
            }
        }

        public function downloadlinksampleAction()  {
            $linkId           = $this->getRequest()->getParam("linkId");
            $link             = Mage::getModel("downloadable/link")->load($linkId);
            $smplLinkFilePath = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Link::getBaseSamplePath(), $link->getSampleFile());
            $fileArray        = explode(DS, $smplLinkFilePath);
            $fileName         = end($fileArray);
            header("Content-Description: File Transfer");
            header("Content-Type: application/vnd.android.package-archive");
            header("Content-Disposition: attachment; filename=".$fileName);
            header("Content-Transfer-Encoding: binary");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Content-Length: ".filesize($smplLinkFilePath));
            ob_clean();
            flush();
            readfile($smplLinkFilePath);
        }

        public function downloadsampleAction()  {
            $sampleId       = $this->getRequest()->getParam("sampleId");
            $sample         = Mage::getModel("downloadable/sample")->load($sampleId);
            $sampleFilePath = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Sample::getBasePath(), $sample->getSampleFile());
            $fileArray      = explode(DS, $sampleFilePath);
            $fileName       = end($fileArray);
            header("Content-Description: File Transfer");
            header("Content-Type: application/vnd.android.package-archive");
            header("Content-Disposition: attachment; filename=".$fileName);
            header("Content-Transfer-Encoding: binary");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Content-Length: ".filesize($sampleFilePath));
            ob_clean();
            flush();
            readfile($sampleFilePath);
        }

    }