<?php

    class Webkul_MobiKul_PayubizController extends Mage_Core_Controller_Front_Action  {

        public function indexAction()   {
            $wholeData = $this->getRequest()->getPost();
            $txnid = isset($wholeData["txnid"]) ? $wholeData["txnid"] : "";
            $amount = isset($wholeData["amount"]) ? $wholeData["amount"] : "";
            $productinfo = isset($wholeData["productinfo"]) ? $wholeData["productinfo"] : "";
            $firstname = isset($wholeData["firstname"]) ? $wholeData["firstname"] : "";
            $email = isset($wholeData["email"]) ? $wholeData["email"] : "";
            $udf1 = isset($wholeData["udf1"]) ? $wholeData["udf1"] : "";
            $udf2 = isset($wholeData["udf2"]) ? $wholeData["udf2"] : "";
            $udf3 = isset($wholeData["udf3"]) ? $wholeData["udf3"] : "";
            $udf4 = isset($wholeData["udf4"]) ? $wholeData["udf4"] : "";
            $udf5 = isset($wholeData["udf5"]) ? $wholeData["udf5"] : "";
            $user_credentials = isset($wholeData["user_credentials"]) ? $wholeData["user_credentials"] : "";
            $offerKey = isset($wholeData["offer_key"]) ? $wholeData["offer_key"] : "";
            $cardBin = "";
            $returnData = $this->getHashes($txnid, $amount, $productinfo, $firstname, $email, $user_credentials, $udf1, $udf2, $udf3, $udf4, $udf5, $offerKey, $cardBin);
            $this->getResponse()->clearHeaders()->setHeader("Content-type", "application/json", true);
            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnData));
            return;
        }

        protected function getHashes($txnid, $amount, $productinfo, $firstname, $email, $user_credentials, $udf1, $udf2, $udf3, $udf4, $udf5, $offerKey, $cardBin){

            $key = Mage::getStoreConfig("payment/payucheckout_shared/key");
            $salt = Mage::getStoreConfig("payment/payucheckout_shared/salt");

            $payhash_str = $key . "|" . $this->checkNull($txnid) . "|" .$this->checkNull($amount)  . "|" .$this->checkNull($productinfo)  . "|" . $this->checkNull($firstname) . "|" . $this->checkNull($email) . "|" . $this->checkNull($udf1) . "|" . $this->checkNull($udf2) . "|" . $this->checkNull($udf3) . "|" . $this->checkNull($udf4) . "|" . $this->checkNull($udf5) . "||||||" . $salt;
            $paymentHash = strtolower(hash("sha512", $payhash_str));
            $arr["payment_hash"] = $paymentHash;

            $cmnMobileSdk = "vas_for_mobile_sdk";
            $mobileSdk_str = $key . "|" . $cmnMobileSdk . "|default|" . $salt;
            $mobileSdk = strtolower(hash("sha512", $mobileSdk_str));
            $arr["vas_for_mobile_sdk_hash"] = $mobileSdk;

            $cmnNameMerchantCodes = "get_merchant_ibibo_codes";
            $merchantCodesHash_str = $key . "|" . $cmnNameMerchantCodes . "|default|" . $salt ;
            $merchantCodesHash = strtolower(hash("sha512", $merchantCodesHash_str));
            $arr["get_merchant_ibibo_codes_hash"] = $merchantCodesHash;

            $cmnPaymentRelatedDetailsForMobileSdk1 = "payment_related_details_for_mobile_sdk";
            $detailsForMobileSdk_str1 = $key  . "|" . $cmnPaymentRelatedDetailsForMobileSdk1 . "|default|" . $salt ;
            $detailsForMobileSdk1 = strtolower(hash("sha512", $detailsForMobileSdk_str1));
            $arr["payment_related_details_for_mobile_sdk_hash"] = $detailsForMobileSdk1;

            //used for verifying payment(optional)
            // $cmnVerifyPayment = "verify_payment";
            // $verifyPayment_str = $key . "|" . $cmnVerifyPayment . "|".$txnid ."|" . $salt;
            // $verifyPayment = strtolower(hash("sha512", $verifyPayment_str));
            // $arr["verify_payment_hash"] = $verifyPayment;

            if($user_credentials != NULL && $user_credentials != ""){
                $cmnNameDeleteCard = "delete_user_card";
                $deleteHash_str = $key  . "|" . $cmnNameDeleteCard . "|" . $user_credentials . "|" . $salt ;
                $deleteHash = strtolower(hash("sha512", $deleteHash_str));
                $arr["delete_user_card_hash"] = $deleteHash;

                $cmnNameGetUserCard = "get_user_cards";
                $getUserCardHash_str = $key  . "|" . $cmnNameGetUserCard . "|" . $user_credentials . "|" . $salt ;
                $getUserCardHash = strtolower(hash("sha512", $getUserCardHash_str));
                $arr["get_user_cards_hash"] = $getUserCardHash;

                $cmnNameEditUserCard = "edit_user_card";
                $editUserCardHash_str = $key  . "|" . $cmnNameEditUserCard . "|" . $user_credentials . "|" . $salt ;
                $editUserCardHash = strtolower(hash("sha512", $editUserCardHash_str));
                $arr["edit_user_card_hash"] = $editUserCardHash;

                $cmnNameSaveUserCard = "save_user_card";
                $saveUserCardHash_str = $key  . "|" . $cmnNameSaveUserCard . "|" . $user_credentials . "|" . $salt ;
                $saveUserCardHash = strtolower(hash("sha512", $saveUserCardHash_str));
                $arr["save_user_card_hash"] = $saveUserCardHash;

                $cmnPaymentRelatedDetailsForMobileSdk = "payment_related_details_for_mobile_sdk";
                $detailsForMobileSdk_str = $key  . "|" . $cmnPaymentRelatedDetailsForMobileSdk . "|" . $user_credentials . "|" . $salt ;
                $detailsForMobileSdk = strtolower(hash("sha512", $detailsForMobileSdk_str));
                $arr["payment_related_details_for_mobile_sdk_hash"] = $detailsForMobileSdk;
            }
            if ($offerKey != NULL && !empty($offerKey)) {
                $cmnCheckOfferStatus = "check_offer_status";
                        $checkOfferStatus_str = $key  . "|" . $cmnCheckOfferStatus . "|" . $offerKey . "|" . $salt ;
                        $checkOfferStatus = strtolower(hash("sha512", $checkOfferStatus_str));
                $arr["check_offer_status_hash"] = $checkOfferStatus;
            }
            if ($cardBin != NULL && !empty($cardBin)) {
                $cmnCheckIsDomestic = "check_isDomestic";
                $checkIsDomestic_str = $key  . "|" . $cmnCheckIsDomestic . "|" . $cardBin . "|" . $salt ;
                $checkIsDomestic = strtolower(hash("sha512", $checkIsDomestic_str));
                $arr["check_isDomestic_hash"] = $checkIsDomestic;
            }
            $arr["message"] = Mage::helper("mobikul")->__("successfully generated hash"); 
            $arr["status"] = "0";
            return $arr;
        }

        protected function checkNull($value) {
            if ($value == null)
                return "";
            else
                return $value;
        }

    }
