<?php

    class Webkul_MobiKul_Helper_Searchsuggestion extends Mage_Core_Helper_Abstract    {

        public function isOnSale($product)  {
            $specialPrice = number_format($product->getFinalPrice(), 2);
            $regularPrice = number_format($product->getPrice(), 2);
            if ($specialPrice != $regularPrice)
                return $this->_nowIsBetween($product->getData("special_from_date"), $product->getData("special_to_date"));
            else
                return false;
        }

        public function matchString($term, $tagName)    {
            $str      = "";
            $len      = strlen($term);
            $term1    = strtolower($term);
            $tagName1 = strtolower($tagName);
            $pos = strpos($tagName1, $term1);
            for($i=0; $i<$len; $i++) {
                $j = $pos+$i;
                $subTerm  = substr($term, $i, 1);
                $subTerm1 = strtolower($subTerm);
                $subTerm2 = strtoupper($subTerm);
                $subName  = substr($tagName, $j, 1);
                if ($subTerm1 == $subName)
                    $str .= $subTerm1;
                elseif ($subTerm2 == $subName)
                    $str .= $subTerm2;
            }
            return($str);
        }

        public function getBoldName($tagName, $str, $term)  {
            $len = strlen($term);
            if(strlen($str) >= $len)
                $tagName = str_replace($str, "<b>".$str."</b>", $tagName);
            return($tagName);
        }

        public function displayTags()   {
            return Mage::getStoreConfig("mobikul/searchsuggestion/display_tag");
        }

        public function displayProducts() {
            $count = Mage::getStoreConfig("mobikul/searchsuggestion/display_product");
            return $count;
        }

        public function getNumberOfTags()   {
            $count = (int) Mage::getStoreConfig("mobikul/searchsuggestion/tag");
            return $count;
        }

        public function getNumberOfProducts()   {
            $count = (int) Mage::getStoreConfig("mobikul/searchsuggestion/product");
            return $count;
        }

        protected function _nowIsBetween($fromDate, $toDate)    {
            if ($fromDate)  {
                $fromDate = strtotime($fromDate);
                $toDate   = strtotime($toDate);
                $now      = strtotime(Mage::app()->getLocale()->date()->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
                if ($toDate)    {
                    if ($fromDate <= $now && $now <= $toDate)
                        return true;
                }
                else    {
                    if ($fromDate <= $now)
                        return true;
                }
            }
            return false;
        }

    }