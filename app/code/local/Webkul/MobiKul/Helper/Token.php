<?php

    class Webkul_MobiKul_Helper_Token extends Mage_Core_Helper_Data      {

        public function saveToken($customerId, $token)  {
            $data = Mage::app()->getRequest()->getParams();
            $os = isset($data["os"]) ? $data["os"] : '';
            try{
                $tokenModel = Mage::getModel("mobikul/devicetoken");
                if($customerId != "" && $token != ""){
                    $collection = $tokenModel
                        ->getCollection()
                        ->addFieldToFilter("token", $token);
                    if($collection->getSize() > 0)  {
                        foreach ($collection as $eachRow) {
                            Mage::getModel("mobikul/devicetoken")
                                ->load($eachRow->getId())
                                ->setCustomerId($customerId)
                                ->setOs($os)
                                ->save();
                            return $eachRow->getId();
                        }
                    }
                    else{
                        return Mage::getModel("mobikul/devicetoken")
                            ->setToken($token)
                            ->setCustomerId($customerId)
                            ->setOs($os)
                            ->save()
                            ->getId();
                    }
                }
                if($customerId == "" && $token != ""){
                    $collection = $tokenModel->getCollection()->addFieldToFilter("token", $token);
                    if($collection->getSize() > 0)  {
                        foreach ($collection as $eachRow) {
                            Mage::getModel("mobikul/devicetoken")
                                ->load($eachRow->getId())
                                ->setCustomerId($customerId)
                                ->setOs($os)
                                ->save();
                            return $eachRow->getId();
                        }
                    }
                    else{
                        return Mage::getModel("mobikul/devicetoken")
                            ->setToken($token)
                            ->setCustomerId($customerId)
                            ->setOs($os)
                            ->save()
                            ->getId();
                    }
                }
            }
            catch (Exception $e) {
                Mage::log($e);
            }
        }
    }