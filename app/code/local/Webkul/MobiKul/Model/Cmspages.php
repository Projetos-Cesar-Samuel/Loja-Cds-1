<?php

    class Webkul_MobiKul_Model_Cmspages    {

        public function toOptionArray()     {
            $collection = Mage::getModel("cms/page")
                ->getCollection()
                ->addFieldToFilter("is_active", 1)
                ->addFieldToFilter("identifier", array(array("nin" => array("no-route", "enable-cookies"))));
            $returnData = array();
            foreach ($collection as $cms) {
                $returnData[] =  array(
                    "value" => $cms->getId(),
                    "label" => $cms->getTitle()
                );
            }
            return $returnData;
        }

    }