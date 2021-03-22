<?php

    class Webkul_MobiKul_Helper_Catalog extends Mage_Core_Helper_Data   {

        /**
         * This function returns dominant color from the image
         *
         * @param string $filePath
         * @return string
         */
        public function getDominantColor($filePath)
        {
            $total = $blueTotal = $greenTotal = $redTotal = 0;
            if (strpos($filePath, Mage::getBaseUrl('media')) !== false) {
                $filePath = str_replace(Mage::getBaseUrl('media'),Mage::getBaseDir('media').'/',$filePath);
            }

            $color = $this->isColorExists($filePath);
            if ($color) {
                return $color;
            }
            return "#f6f6f6";
        }

        private function getColor($filePath)
        {
            $imageInfo = @getimagesize($filePath);
            $image = '';
            if (isset($imageInfo['mime'])){
                switch(strtolower($imageInfo['mime']))
                {
                    case 'image/png':
                        $image = @imagecreatefrompng($filePath);
                        break;
                    case 'image/jpeg':
                        $image = @imagecreatefromjpeg($filePath);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($filePath);
                        break;
                    default: 
                }
            }
            if ($image ) {
                for ($x=0; $x<@imagesx($image); $x++) {
                    for ($y=0; $y<@imagesy($image); $y++) {
                        $rgb = @imagecolorat($image, $x, $y);
                        $red = ($rgb >> 16) &0xFF;
                        $green = ($rgb >> 8) &0xFF;
                        $blue = $rgb & 0xFF;
                        $redTotal += $red;
                        $greenTotal += $green;
                        $blueTotal += $blue;
                        $total++;
                    }
                }
                $redAverage = round($redTotal/$total);
                $greenAverage = round($greenTotal/$total);
                $blueAverage = round($blueTotal/$total);
                return sprintf("#%02x%02x%02x", $redAverage, $greenAverage, $blueAverage);
            }
            return '';
        }

        private function isColorExists($filePath)
        {
            $path      = Mage::getBaseDir('media')."/mobikul/color/";
            $sha1Tag   = sha1($filePath);
            $cacheFile = "color.json";
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $allColorTags = array();
            try {
                $allColorTags = file_get_contents($path.$cacheFile);
            } catch (Exception $e) {
            }
            if ($allColorTags == '') {
                $allColorTags = '{}';
            }
            $allColorTags = Mage::helper("core")->jsonDecode($allColorTags);
            if (empty($allColorTags[$sha1Tag])) {
                $allColorTags[$sha1Tag] = $this->getColor($filePath);
                try {
                    file_put_contents($path.$cacheFile, Mage::helper("core")->jsonEncode($allColorTags));
                } catch (Exception $e) {}
            }
            return $allColorTags[$sha1Tag];
        }

        public function getAttributeInputType($attribute)    {
            $dataType  = $attribute->getBackend()->getType();
            $inputType = $attribute->getFrontend()->getInputType();
            if($inputType == "select" || $inputType == "multiselect")
                return "select";
            else
            if($inputType == "boolean")
                return "yesno";
            else
            if($inputType == "price")
                return "price";
            else
            if($dataType == "int" || $dataType == "decimal")
                return "number";
            else
            if($dataType == "datetime")
                return "date";
            else
                return "string";
        }

        public function _renderRangeLabel($fromPrice, $toPrice, $storeId)    {
            $store = Mage::getModel("core/store")->load($storeId);
            $formattedFromPrice  = $store->formatPrice($fromPrice);
            if($toPrice === "")
                return Mage::helper("catalog")->__("%s and above", $formattedFromPrice);
            elseif($fromPrice == $toPrice && Mage::app()->getStore()->getConfig("catalog/layered_navigation/one_price_interval"))
                return $formattedFromPrice;
            else{
                if($fromPrice != $toPrice)
                    $toPrice -= .01;
                return Mage::helper("catalog")->__("%s - %s", $formattedFromPrice, $store->formatPrice($toPrice));
            }
        }

        public function getPriceFilter($priceFilterModel, $storeId) {
            if(Mage::app()->getStore()->getConfig("catalog/layered_navigation/price_range_calculation") == "improved") {
                $algorithmModel = Mage::getSingleton("catalog/layer_filter_price_algorithm");
                $collection = $priceFilterModel->getLayer()->getProductCollection();
                $appliedInterval = $priceFilterModel->getInterval();
                if($appliedInterval && $collection->getPricesCount() <= $priceFilterModel->getIntervalDivisionLimit())
                    return array();
                $algorithmModel->setPricesModel($priceFilterModel)->setStatistics(
                    $collection->getMinPrice(),
                    $collection->getMaxPrice(),
                    $collection->getPriceStandardDeviation(),
                    $collection->getPricesCount()
                );
                if($appliedInterval) {
                    if($appliedInterval[0] == $appliedInterval[1] || $appliedInterval[1] === "0")
                        return array();
                    $algorithmModel->setLimits($appliedInterval[0], $appliedInterval[1]);
                }
                $items = array();
                foreach($algorithmModel->calculateSeparators() as $separator) {
                    $items[] = array(
                        "label" => Mage::helper("core")->stripTags($this->_renderRangeLabel($separator["from"], $separator["to"], $storeId)),
                        "id"    => (($separator["from"] == 0) ? "" : $separator["from"])."-".$separator["to"].$priceFilterModel->_getAdditionalRequestData(),
                        "count" => $separator["count"]
                    );
                }
            }
            elseif($priceFilterModel->getInterval())
                return array();
            $range    = $priceFilterModel->getPriceRange();
            $dbRanges = $priceFilterModel->getRangeItemCounts($range);
            $data     = array();
            if(!empty($dbRanges)) {
                $lastIndex = array_keys($dbRanges);
                $lastIndex = $lastIndex[count($lastIndex) - 1];
                foreach($dbRanges as $index => $count) {
                    $fromPrice = ($index == 1) ? "" : (($index - 1) * $range);
                    $toPrice = ($index == $lastIndex) ? "" : ($index * $range);
                    $data[] = array(
                        "label" => Mage::helper("core")->stripTags($this->_renderRangeLabel($fromPrice, $toPrice, $storeId)),
                        "id"    => $fromPrice."-".$toPrice,
                        "count" => $count
                    );
                }
            }
            return $data;
        }

        public function getAttributeFilter($attributeFilterModel, $_filter){
            $options = $_filter->getFrontend()->getSelectOptions();
            $optionsCount = Mage::getResourceModel("catalog/layer_filter_attribute")->getCount($attributeFilterModel);
            $data = array();
            foreach($options as $option) {
                if(is_array($option["value"]))
                    continue;
                if(Mage::helper("core/string")->strlen($option["value"])) {
                    if($_filter->getIsFilterable() == 1) {
                        if(!empty($optionsCount[$option["value"]])) {
                            $data[] = array(
                                "label" => $option["label"],
                                "id"    => $option["value"],
                                "count" => $optionsCount[$option["value"]]
                            );
                        }
                    }
                    else {
                        $data[] = array(
                            "label" => $option["label"],
                            "id"    => $option["value"],
                            "count" => isset($optionsCount[$option["value"]]) ? $optionsCount[$option["value"]] : 0
                        );
                    }
                }
            }
            return $data;
        }

        public function getQueryArray($queryString){
            $queryArray = array();
            foreach($queryString as $each) {
                if($each["inputType"] == "string" || $each["inputType"] == "yesno"){
                    if($each["value"] != "")
                        $queryArray[$each["code"]] = $each["value"];
                }
                else
                if($each["inputType"] == "price" || $each["inputType"] == "date"){
                    $valueArray = $each["value"];
                    if($valueArray["from"] != "" && $valueArray["to"] != "")
                        $queryArray[$each["code"]] = array("from"=>$valueArray["from"], "to"=>$valueArray["to"]);
                }
                else
                if($each["inputType"] == "select"){
                    $valueArray = $each["value"];
                    $selectedArray = array();
                    foreach($valueArray as $key => $value) {
                        if($value == "true")
                            $selectedArray[] = $key;
                    }
                    if(count($selectedArray) > 0)
                        $queryArray[$each["code"]] = $selectedArray;
                }
            }
            return $queryArray;
        }

        public function getOneProductRelevantData($product, $storeId, $width, $customerId=0){
            $reviews = Mage::getModel("review/review")->getResourceCollection()->addStoreFilter($storeId)
                    ->addEntityFilter("product", $product->getId())->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                    ->setDateOrder()->addRateVotes();
            $ratings = array();
            if(count($reviews) > 0) {
                foreach($reviews->getItems() as $review) {
                    foreach($review->getRatingVotes() as $vote)
                        $ratings[] = $vote->getPercent();
                }
            }
            $isIncludeTaxInPrice = 0;
            if ($this->getIfTaxIncludeInPrice() == 2) {
                $isIncludeTaxInPrice = 1;
            }
            $eachProduct = array();
            $eachProduct["isInWishlist"] = false;
            if($customerId != 0)    {
                $customer   = Mage::getModel("customer/customer")->load($customerId);
                $wishlist   = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer);
                $wishlistCollection = Mage::getModel("wishlist/item")->getCollection()
                        ->addFieldToFilter("wishlist_id", $wishlist->getId())
                        ->addFieldToFilter("product_id", $product->getId());
                $item = $wishlistCollection->getFirstItem();
                $eachProduct["isInWishlist"] = !!$item->getId();
                if ($eachProduct["isInWishlist"])
                    $eachProduct["itemId"] = $item->getId();
            }
            $eachProduct["entityId"] = $product->getId();
            if($product->getTypeId() == "downloadable"){
                Mage::unregister("current_product");
                Mage::unregister("product");
                Mage::register("current_product", $product);
                Mage::register("product", $product);
                $downloadableBlock = new Mage_Downloadable_Block_Catalog_Product_Links();
                $eachProduct["linksPurchasedSeparately"] = $downloadableBlock->getLinksPurchasedSeparately();
            }
            if($product->getTypeId() == "bundle"){
                $eachProduct["priceView"] = $product->getPriceView();
                $eachProduct["priceType"] = $product->getPriceType();
                $_priceModel  = $product->getPriceModel();
                if ($isIncludeTaxInPrice) {
                    list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getTotalPrices($product, null, true, false);
                    $eachProduct["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $_minimalPriceInclTax)));
                    $eachProduct["minPrice"] = Mage::helper("tax")->getPrice($product, $_minimalPriceInclTax);
                    $eachProduct["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $_maximalPriceInclTax)));
                    $eachProduct["maxPrice"] = Mage::helper("tax")->getPrice($product, $_maximalPriceInclTax);
                }
                else{
                    list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getTotalPrices($product, null, null, false);
                    $eachProduct["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_minimalPriceTax));
                    $eachProduct["minPrice"] = $_minimalPriceTax;
                    $eachProduct["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_maximalPriceTax));
                    $eachProduct["maxPrice"] = $_maximalPriceTax;
                }
            }
            $_coreHelper = Mage::helper('core');
            $_weeeHelper = Mage::helper('weee');
            $_taxHelper  = Mage::helper('tax');
            /* @var $_coreHelper Mage_Core_Helper_Data */
            /* @var $_weeeHelper Mage_Weee_Helper_Data */
            /* @var $_taxHelper Mage_Tax_Helper_Data */
            $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
            $_minimalPriceValue   = $product->getMinimalPrice();
            $_store               = $product->getStore();
            $_minimalPriceValue   = $_store->roundPrice($_store->convertPrice($_minimalPriceValue));
            $_minimalPrice        = $_taxHelper->getPrice($product, $_minimalPriceValue, $_simplePricesTax);
            $_convertedFinalPrice = $_store->roundPrice($_store->convertPrice($product->getFinalPrice()));
            $_finalPrice          = $_taxHelper->getPrice($product, $_convertedFinalPrice);
            $_finalPriceInclTax   = $_taxHelper->getPrice($product, $_convertedFinalPrice, true);

            $eachProduct["displayBothPrice"]  = Mage::helper("tax")->displayBothPrices();
            $eachProduct["priceExcludingTax"] = $_coreHelper->formatPrice($_finalPrice, false);
            $eachProduct["priceIncludingTax"] = $_coreHelper->formatPrice($_finalPriceInclTax, false);
            $eachProduct["taxIncludeInPrice"] = Mage::helper("mobikul/catalog")->getIfTaxIncludeInPrice();
            $eachProduct["priceIncludesTax"]  = (Mage::helper("mobikul/catalog")->getPriceIncludesTax())? true : false;


            $tierPrice = $product->getTierPrice();
            if(count($tierPrice) > 0){
                $tierPrices = array();
                foreach($tierPrice as $value)
                    $tierPrices[] = $value["price"];
                sort($tierPrices);
                $eachProduct["tierPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($tierPrices[0]));
                $eachProduct["hasTierPrice"] = "true";
            }
            else
                $eachProduct["hasTierPrice"] = "false";
            $eachProduct["shortDescription"] = html_entity_decode(Mage::helper("core")->stripTags($product->getShortDescription()));
            if(count($ratings) > 0)
                $rating = number_format((5*(array_sum($ratings) / count($ratings)))/100, 2, ".", "");
            else
                $rating = 0;
            if($product->isAvailable())
                $eachProduct["isAvailable"] = true;
            else
                $eachProduct["isAvailable"] = false;
            $eachProduct["rating"] = $rating;
            if ($isIncludeTaxInPrice) {
                $eachProduct["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getPrice())));
                $eachProduct["price"] = Mage::helper("tax")->getPrice($product, $product->getPrice());
                $eachProduct["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getFinalPrice())));
                $eachProduct["finalPrice"] = round(Mage::helper("tax")->getPrice($product, $product->getFinalPrice()), 2);
                $eachProduct["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getSpecialPrice())));
                $eachProduct["specialPrice"] = Mage::helper("tax")->getPrice($product, $product->getSpecialPrice());
            }
            else{
                $eachProduct["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getPrice()));
                $eachProduct["price"] = $product->getPrice();
                $eachProduct["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getFinalPrice()));
                $eachProduct["finalPrice"] = round($product->getFinalPrice(), 2);
                $eachProduct["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getSpecialPrice()));
                $eachProduct["specialPrice"] = $product->getSpecialPrice();
            }
            $eachProduct["qtyIncrements"] = $product->getStockItem()->getQtyIncrements();
            $eachProduct["qtyIncrementsEnable"] = ($product->getStockItem()->getQtyIncrements() != null)? true :false;
            $eachProduct["typeId"] = $product->getTypeId();
            $eachProduct["hasOptions"] = $product->getHasOptions();
            $eachProduct["requiredOptions"] = $product->getRequiredOptions();
            $returnArray["msrpEnabled"] = $product->getMsrpEnabled();
            $returnArray["msrpDisplayActualPriceType"] = $product->getMsrpDisplayActualPriceType();
            $eachProduct["name"] = $product->getName();
            if($product->getTypeId() == "grouped"){
                if($product->getMinimalPrice() == "") {
                    $groupedParentId = Mage::getModel("catalog/product_type_grouped")->getParentIdsByChild($product->getId());
                    $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    $minPrice = array();
                    foreach($associatedProducts as $associatedProduct) {
                        if($ogPrice = $associatedProduct->getFinalPrice())
                            $minPrice[] = $ogPrice;
                    }
                    if(count($minPrice))
                        $minPrice = min($minPrice);
                    else
                        $minPrice = 0;
                    $eachProduct["groupedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($minPrice));
                }
                else
                    $eachProduct["groupedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMinimalPrice()));
            }
            $fromdate = $product->getSpecialFromDate();
            $todate = $product->getSpecialToDate();
            $isInRange = false;
            if(isset($fromdate) && isset($todate)){
                $today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                $today_time = strtotime($today);
                $from_time = strtotime($fromdate);
                $to_time = strtotime($todate);
                if($today_time >= $from_time && $today_time <= $to_time)
                    $isInRange = true;
            }
            if(isset($fromdate) && !isset($todate)){
                $today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                $today_time = strtotime($today);
                $from_time = strtotime($fromdate);
                if($today_time >= $from_time)
                    $isInRange = true;
            }
            if(!isset($fromdate) && isset($todate)){
                $today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                $today_time = strtotime($today);
                $from_time = strtotime($fromdate);
                if($today_time <= $from_time)
                    $isInRange = true;
            }
            $eachProduct["isInRange"] = $isInRange;
            // $imageData = Mage::helper("mobikul/image")->init($product, "small_image")->keepFrame(true)->resize($width/2.5)->__toString();
                    
            // $eachProduct["thumbNail"] = $imageData[0];
            // $eachProduct["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]); 
            $eachProduct["thumbNail"] = Mage::helper("catalog/image")->init($product, "small_image")->keepFrame(true)->resize($width/2.5)->__toString();
            $eachProduct["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($eachProduct["thumbNail"]); 
            return $eachProduct;
        }

        public function getStoreData()  {
            $storeData = array();
            $storeBlock = new Mage_Page_Block_Switch();
            foreach($storeBlock->getGroups() as $group) {
                $groupArr = array();
                $groupArr["id"] = $group->getGroupId();
                $groupArr["name"] = $group->getName();
                $stores = $group->getStores();
                foreach($stores as $store) {
                    if (!$store->getIsActive())
                        continue;
                    $storeArr = array();
                    $storeArr["id"] = $store->getStoreId();
                    $code = explode("_", Mage::getStoreConfig("general/locale/code", $store->getStoreId()));
                    $storeArr["code"] = $code[0];
                    $storeArr["name"] = $store->getName();
                    $groupArr["stores"][] = $storeArr;
                }
                $storeData[] = $groupArr;
            }
            return $storeData;
        }

        public function getIfTaxIncludeInPrice()    {
            return Mage::getStoreConfig("tax/display/type", Mage::app()->getStore()->getId());
        }
        
        public function getPriceIncludesTax()    {
            return Mage::getStoreConfig("tax/calculation/price_includes_tax", Mage::app()->getStore()->getId());
        }

         public function getDisplayCartBothPrices()    {
            return Mage::getStoreConfig("tax/cart_display/price", Mage::app()->getStore()->getId());
        }

    }
