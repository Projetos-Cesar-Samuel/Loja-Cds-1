<?php

    class Webkul_MobiKul_CatalogController extends Mage_Core_Controller_Front_Action    {

        public function testAction()   {
            
            $client = new SoapClient('https://www.cazadosport.com.br/api/soap/?wsdl');
            $session = $client->login('cdssistemas', '88d}4UNbox@P');
            $resultCliente = $client->call($session, 'customer.info', '78');
            echo '<pre>';
            var_dump($resultCliente);
            die();
        }
        public function homePageDataAction()   {
            $returnArray                         = array();
            $returnArray["authKey"]              = "";
            $returnArray["responseCode"]         = 0;
            $returnArray["success"]              = false;
            $returnArray["message"]              = "";
            $returnArray["categories"]           = new stdClass();
            $returnArray["bannerImages"]         = array();
            $returnArray["featuredCategories"]   = array();
            $returnArray["featuredProducts"]     = array();
            $returnArray["newProducts"]          = array();
            $returnArray["hotDeals"]             = array();
            $returnArray["categoryImages"]       = array();
            $returnArray["customerBannerImage"]  = "";
            $returnArray["customerProfileImage"] = "";
            $returnArray["storeId"]              = 0;
            $returnArray["cartCount"]            = 0;
            $returnArray["themeCode"]            = "";
            $returnArray["customerName"]         = "";
            $returnArray["customerEmail"]        = "";
            $returnArray["cmsData"]              = array();
            $returnArray["productId"]            = 0;
            $returnArray["productName"]          = "";
            $returnArray["categoryId"]           = 0;
            $returnArray["categoryName"]         = "";
            $returnArray["minQueryLength"]       = 1;
            $returnArray["isMobileLoginEnable"]  = false;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData       = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $quoteId    = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 0;
                        $websiteId  = isset($wholeData["websiteId"])  ? $wholeData["websiteId"]  : 0;
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $width      = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $isFromUrl  = isset($wholeData["isFromUrl"])  ? $wholeData["isFromUrl"]  : 0;
                        $url        = isset($wholeData["url"])        ? $wholeData["url"]        : "";
                        $mFactor    = isset($wholeData["mFactor"])    ? $wholeData["mFactor"]    : 1;
                        if ($storeId == 0) {
                            $storeId = Mage::app()->getWebsite($websiteId)->getDefaultGroup()->getDefaultStoreId();
                            $returnArray["storeId"] = $storeId;
                        }
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        if ($isFromUrl != 0) {
                            $exploded = explode('?_store',$url);
                            if ($exploded[1]) {
                                $url = $exploded[1];
                            }
                            $oRewrite = Mage::getModel("core/url_rewrite")->setStoreId($storeId)->loadByRequestPath($url);
                            $productId = $oRewrite->getProductId();
                            $categoryId = $oRewrite->getCategoryId();
                            if ($productId != "") {
                                $returnArray["productId"] = $productId;
                                $returnArray["productName"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($productId, "name", $storeId);
                            } elseif ($categoryId != "") {
                                $returnArray["categoryId"] = $categoryId;
                                $returnArray["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($categoryId, "name", $storeId);
                            }
                            $returnArray["success"] = true;
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
// Wheather Mobile Login enable //////////////////////////////////////////////////////////////////////////////////////////////////
                        if(Mage::getStoreConfig("mobikul/basic/enable_mobile_login") == 1)
                            $returnArray["isMobileLoginEnable"] = true;
// Theme Code of the application ////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["themeCode"] = Mage::getStoreConfig("mobikul/theme/code");
// getting minimum search query length //////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["minQueryLength"] = Mage::getStoreConfig("catalog/search/min_query_length");
// Category data for drawer menu ////////////////////////////////////////////////////////////////////////////////////////////////
                        // $categories = '{}';
                        $categories = Mage::getModel("mobikul/category_api")->tree(null, $storeId);
                        $returnArray["categories"] = $categories;
// Featured Category ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $featuredCategories = array();
                        $featuredCategoryCollection = Mage::getModel("mobikul/featuredcategories")->getCollection()->addFieldToFilter("status", 1)
                            ->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
                            ->setOrder("sort_order", "ASC");
                        $FCheight = $FCwidth = 96 * $mFactor;
                        foreach ($featuredCategoryCollection as $eachCategory) {
                            $oneCategory = array();
                            $newUrl = "";
                            $basePath = Mage::getBaseDir("media").DS.$eachCategory->getFilename();
                            if (is_file($basePath)) {
                                $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."featuredCategory".DS.$FCwidth."x".$FCheight.DS.$eachCategory->getFilename();                                
                                Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $FCwidth, $FCheight);
                                $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."featuredCategory".DS.$FCwidth."x".$FCheight.DS.$eachCategory->getFilename();
                                $oneCategory["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);
                            }
                            $oneCategory["url"] = $newUrl;
                            $oneCategory["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($eachCategory->getCategoryId(), "name", $storeId);
                            $oneCategory["categoryId"] = $eachCategory->getCategoryId();
                            $featuredCategories[] = $oneCategory;
                        }
                        $returnArray["featuredCategories"] = $featuredCategories;
// Banner Images ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $bannerImages = array();
                        $collection = Mage::getModel("mobikul/bannerimage")
                            ->getCollection()
                            ->addFieldToFilter("status", 1)
                            ->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
                            ->setOrder("sort_order", "ASC");
                        $bannerWidth = $width;
                        $bannerWidth *= $mFactor;
                        $height = ($width/2)*$mFactor;
                        foreach ($collection as $eachBanner) {
                            $oneBanner = array();
                            $newUrl = "";
                            $basePath = Mage::getBaseDir("media").DS.$eachBanner->getFilename();
                            if (is_file($basePath)) {
                                $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."banner".DS.$bannerWidth."x".$height.DS.$eachBanner->getFilename();                                
                                Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                                $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."banner".DS.$bannerWidth."x".$height.DS.$eachBanner->getFilename();
                                $oneBanner["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath); 
                            }
                            $oneBanner["url"] = $newUrl;
                            $oneBanner["bannerType"] = $eachBanner->getType();
                            if ($eachBanner->getType() == "category") {
                                if (Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId))
                                    $oneBanner["error"] = false;
                                else
                                    $oneBanner["error"] = true;
                                $oneBanner["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId);
                                $oneBanner["categoryId"] = $eachBanner->getProCatId();
                            } elseif ($eachBanner->getType() == "product") {
                                if (Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId))
                                    $oneBanner["error"] = false;
                                else
                                    $oneBanner["error"] = true;
                                $oneBanner["productName"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId);
                                $oneBanner["productId"] = $eachBanner->getProCatId();
                            }
                            $bannerImages[] = $oneBanner;
                        }
                        $returnArray["bannerImages"] = $bannerImages;
// Featured Products ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $featuredProducts = array();
                        $attributes = Mage::getSingleton("catalog/config")->getProductAttributes();
                        if(Mage::getStoreConfig("mobikul/basic/featuredproduct", $storeId) == 1){
                            $featuredProductCollection = Mage::getResourceModel("catalog/product_collection")->addAttributeToSelect($attributes);
                            Mage::getModel("catalog/layer")->prepareProductCollection($featuredProductCollection);
                            $featuredProductCollection->getSelect()->order("rand()");
                            $featuredProductCollection->setPage(1, 5)->load();
                            Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($featuredProductCollection);
                            Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($featuredProductCollection);
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($featuredProductCollection);
                        }
                        else{
                            $featuredProductCollection = Mage::getModel("catalog/product")->getCollection()
                                ->setStore($storeId)
                                ->addAttributeToSelect($attributes)
                                ->addAttributeToSelect("as_featured")
                                ->addAttributeToSelect("visibility")
                                ->addStoreFilter()
                                ->addAttributeToFilter("visibility", array("in" => array(2, 3, 4)))
                                ->addAttributeToFilter("as_featured", 1)
                                ->addAttributeToFilter("status", 1)
                                ->setPageSize(5)
                                ->setCurPage(1);
                        }
                        foreach ($featuredProductCollection as $eachProduct) {
                            $eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
                                $featuredProducts[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["featuredProducts"] = $featuredProducts;
// New Products /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $newProducts = array();
                        $todayStartOfDayDate  = Mage::app()->getLocale()->date()->setTime("00:00:00")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $todayEndOfDayDate    = Mage::app()->getLocale()->date()->setTime("23:59:59")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $newProductCollection = Mage::getResourceModel("catalog/product_collection")
                            ->setVisibility(Mage::getSingleton("catalog/product_visibility")->getVisibleInCatalogIds())
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addTaxPercents()
                            ->addAttributeToSelect(Mage::getSingleton("catalog/config")->getProductAttributes());
                        $newProductCollection->addStoreFilter()
                            ->addAttributeToFilter("news_from_date", array("or"=> array(
                                0 => array("date" => true, "to" => $todayEndOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter("news_to_date", array("or"=> array(
                                0 => array("date" => true, "from" => $todayStartOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter(
                                array(
                                    array("attribute" => "news_from_date", "is"=>new Zend_Db_Expr("not null")),
                                    array("attribute" => "news_to_date", "is"=>new Zend_Db_Expr("not null"))
                                )
                            )
                        ->addAttributeToSort("news_from_date", "desc")
                        ->setPageSize(5)
                        ->setCurPage(1);
                        foreach ($newProductCollection as $eachProduct) {
                            $eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
                                $newProducts[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["newProducts"] = $newProducts;
// Hot Products /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $hotDeals = array();
                        $todayStartOfDayDate = Mage::app()->getLocale()->date()->setTime("00:00:00")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $todayEndOfDayDate   = Mage::app()->getLocale()->date()->setTime("23:59:59")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $hotDealCollection   = Mage::getResourceModel("catalog/product_collection")
                            ->setVisibility(Mage::getSingleton("catalog/product_visibility")->getVisibleInCatalogIds())
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addTaxPercents()
                            ->addAttributeToSelect("special_price")
                            ->addAttributeToSelect("price")
                            ->addAttributeToSelect("special_from_date")
                            ->addAttributeToSelect("special_to_date")
                            ->addAttributeToSelect(Mage::getSingleton("catalog/config")->getProductAttributes());
                        $hotDealCollection->addStoreFilter()
                            ->addAttributeToFilter("special_from_date", array("or"=> array(
                                0 => array("date" => true, "to" => $todayEndOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter("special_to_date", array("or"=> array(
                                0 => array("date" => true, "from" => $todayStartOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter(
                                array(
                                    array("attribute" => "special_from_date", "is"=>new Zend_Db_Expr("not null")),
                                    array("attribute" => "special_to_date", "is"=>new Zend_Db_Expr("not null"))
                                )
                            )
                            ->addAttributeToFilter("special_price", array('gteq' => 0));
                            // $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_decimal');
                            // $hotDealCollection->getSelect()->join( array('special_attr'=> $tableName), 'special_attr.entity_id = e.entity_id', array('special_attr.*'));
                            // $hotDealCollection->getSelect()->where('special_attr.value < price_index.price');
                            $hotDealCollection->setPageSize(5)
                            ->setCurPage(1);
                        foreach ($hotDealCollection as $eachProduct) {
                            $eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
                                $hotDeals[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["hotDeals"] = $hotDeals;
// Store Data ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["storeData"] = Mage::helper("mobikul/catalog")->getStoreData();
// Customer Profile and Banner Images ///////////////////////////////////////////////////////////////////////////////////////////
                        if ($customerId != 0) {
                            $customer = Mage::getModel("customer/customer")->load($customerId);
                            $returnArray["customerName"] = $customer->getName();
                            $returnArray["customerEmail"] = $customer->getEmail();
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $quote->collectTotals()->save();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                            $collection = Mage::getModel("mobikul/userimage")->getCollection()->addFieldToFilter("customer_id", $customerId);
                            $time = time();
                            if ($collection->getSize() > 0) {
                                foreach ($collection as $value) {
                                    if ($value->getBanner() != "") {
                                        $basePath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getBanner();
                                        $newUrl = "";
                                        if (is_file($basePath)) {
                                            $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner();
                                            Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $width, $height);
                                            $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner();
                                            $returnArray["customerBannerDominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);
                                        }
                                        $returnArray["customerBannerImage"] = $newUrl."?".$time;
                                    }
                                    if ($value->getProfile() != "") {
                                        $basePath = Mage::getBaseDir("media").DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getProfile();
                                        $newUrl = "";
                                        if (is_file($basePath)) {
                                            $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$FCwidth."x".$FCheight.DS.$value->getProfile();
                                            Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $FCwidth, $FCheight);
                                            $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."customerpicture".DS.$customerId.DS.$FCwidth."x".$FCheight.DS.$value->getProfile();
                                            $returnArray["customerProfileDominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);
                                        }
                                        $returnArray["customerProfileImage"] = $newUrl."?".$time;
                                    }
                                }
                            }
                        }
// Category Image Collection ////////////////////////////////////////////////////////////////////////////////////////////////////
                        $categoryImageCollection = Mage::getModel("mobikul/categoryimages")->getCollection();
                        $categoryImages = array();
                        foreach ($categoryImageCollection as $categoryImage) {
                            if ($categoryImage->getBanner() != "" && $categoryImage->getIcon() != "") {
                                $eachCategoryImage["id"] = $categoryImage->getCategoryId();
                                if ($categoryImage->getBanner() != "") {
                                    $newUrl = "";
                                    $basePath = Mage::getBaseDir("media").DS.$categoryImage->getBanner();
                                    if (is_file($basePath)) {
                                        $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."categoryimages".DS.$width."x".$height.DS.$categoryImage->getBanner();
                                        Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $width, $height);
                                        $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."categoryimages".DS.$width."x".$height.DS.$categoryImage->getBanner();
                                        $eachCategoryImage["bannerDominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);
                                    }
                                    $eachCategoryImage["banner"] = $newUrl;
                                }
                                if ($categoryImage->getIcon() != "") {
                                    $basePath = Mage::getBaseDir("media").DS.$categoryImage->getIcon();
                                    // if (is_file($basePath)) {
                                    //     $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."categoryimages".DS.$FCwidth."x".$FCheight.DS.$categoryImage->getIcon();
                                    //     Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $FCwidth, $FCheight);
                                    //     $newUrl = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."categoryimages".DS.$FCwidth."x".$FCheight.DS.$categoryImage->getIcon();
                                    //     $eachCategoryImage["iconDominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);
                                    // }
                                    // $eachCategoryImage["thumbnail"] = $newUrl;
                                    $eachCategoryImage["thumbnail"] =  Mage::getBaseUrl("media").DS.$categoryImage->getIcon();
                                }
                                $categoryImages[] = $eachCategoryImage;
                            }
                        }
                        $returnArray["categoryImages"] = $categoryImages;
// Getting CMS page data ////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $allowedCmsPages = Mage::getStoreConfig("mobikul/basic/cms", $storeId);
                        if($allowedCmsPages != ""){
                            $allowedIds = explode(",", $allowedCmsPages);
                            $collection = Mage::getModel("cms/page")->getCollection()
                              ->addStoreFilter($storeId)
                              ->addFieldToFilter("page_id", array("in" => $allowedIds));
                            $cmsData = array();
                            foreach ($collection as $cms) {
                                $cmsData[] =  array(
                                    "id"    => $cms->getId(),
                                    "title" => $cms->getTitle()
                                );
                            }
                            $returnArray["cmsData"] = $cmsData;
                        }
// Cart Count ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId)->getItemsQty() * 1;
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;

                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function categoryProductListAction() {            
            $returnArray                         = array();
            $returnArray["authKey"]              = "";
            $returnArray["responseCode"]         = 0;
            $returnArray["success"]              = false;
            $returnArray["message"]              = "";
            $returnArray["bannerImage"]          = "";
            $returnArray["dominantColor"]        = "";
            $returnArray["totalCount"]           = 0;
            $returnArray["productList"]          = array();
            $returnArray["layeredData"]          = array();
            $returnArray["sortingData"]          = array();
            $returnArray["cartCount"]            = 0;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 0;
                        $categoryId   = isset($wholeData["categoryId"]) ? $wholeData["categoryId"] : 0;
                        $width        = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $pageNumber   = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $sortData     = isset($wholeData["sortData"])   ? $wholeData["sortData"]   : "[]";
                        $filterData   = isset($wholeData["filterData"]) ? $wholeData["filterData"] : "[]";
                        $mFactor      = isset($wholeData["mFactor"])    ? $wholeData["mFactor"]    : 1;
                        $sortData     = Mage::helper("core")->jsonDecode($sortData);
                        $filterData   = Mage::helper("core")->jsonDecode($filterData);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $productList  = array(); $layeredData = array(); $sortingData = array();
                        $category     = Mage::getModel("catalog/category")->setStoreId($storeId)->load($categoryId);
                        Mage::register("current_category", $category);
                        $categoryBlock = new Mage_Catalog_Block_Product_List();
                        $productCollection = $categoryBlock->getLoadedProductCollection();
                        if (Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 0) {
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
                            Mage::getSingleton("catalog/product_status")->addSaleableFilterToCollection($productCollection);
                        }
                        
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($filterData) > 0) {
                            for ($i=0; $i<count($filterData[0]); $i++) {
                                if ($filterData[0][$i] != "") {
                                    if ($filterData[1][$i] == "price") {
                                        $minPossiblePrice = .01;
                                        $currencyRate = $productCollection->getCurrencyRate();
                                        $priceRange = explode("-", $filterData[0][$i]);
                                        $from = $priceRange[0];
                                        $to = $priceRange[1];
                                        $fromRange = ($from - ($minPossiblePrice / 2)) / $currencyRate;
                                        $toRange = ($to - ($minPossiblePrice / 2)) / $currencyRate;
                                        $select = $productCollection->getSelect();
                                        if ($from !== "")
                                            $select->where("price_index.min_price".">=".$fromRange);
                                        if ($to !== "")
                                            $select->where("price_index.min_price"."<".$toRange);
                                    } elseif ($filterData[1][$i] == "cat") {
                                        $categoryToFilter = Mage::getModel("catalog/category")->load($filterData[0][$i]);
                                        $productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
                                    } else {
                                        $attribute = Mage::getModel("eav/entity_attribute")->loadByCode("catalog_product", $filterData[1][$i]);
                                        $attributeModel = Mage::getSingleton("catalog/layer_filter_attribute");
                                        $attributeModel->setAttributeModel($attribute);
                                        Mage::getResourceModel("catalog/layer_filter_attribute")->applyFilterToCollection($attributeModel, $filterData[0][$i]);
                                    }
                                }
                            }
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        foreach ($productCollection as $product){
                            $eachProduct = Mage::getModel("catalog/product")->load($product->getId());
                                $productList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
                        $doCategory = 1;
                        if (count($filterData) > 0) {
                            if (in_array("cat", $filterData[1]))
                                $doCategory = 0;
                        }
                        if ($doCategory == 1) {
                            $categoryFilterModel = new Mage_Catalog_Model_Layer_Filter_Category();
                            if ($categoryFilterModel->getItemsCount()) {
                                $each = array();
                                $each["label"] = Mage::helper("mobikul")->__("Category");
                                $each["code"] = "cat";
                                $key = $categoryFilterModel->getLayer()->getStateKey()."_SUBCATEGORIES";
                                $data = $categoryFilterModel->getLayer()->getAggregator()->getCacheData($key);
                                if ($data === null) {
                                    $category = $categoryFilterModel->getCategory();
                                    $categories = $category->getChildrenCategories();
                                    $categoryFilterModel->getLayer()->getProductCollection()->addCountToCategories($categories);
                                    $data = array();
                                    foreach ($categories as $category) {
                                        if ($category->getIsActive() && $category->getProductCount()) {
                                            $data[] = array(
                                                "label" => str_replace("&amp;", "&", Mage::helper("core")->stripTags($category->getName())),
                                                "id"    => $category->getId(),
                                                "count" => $category->getProductCount()
                                            );
                                        }
                                    }
                                    $tags = $categoryFilterModel->getLayer()->getStateTags();
                                    $categoryFilterModel->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
                                }
                                $each["options"] = $data;
                                $layeredData[] = $each;
                            }
                        }
                        $doPrice = 1;
                        if (count($filterData) > 0) {
                            if (in_array("price", $filterData[1]))
                                $doPrice = 0;
                        }
                        $filters = Mage::getModel("catalog/layer")->getFilterableAttributes();
                        foreach ($filters as $filter) {
                            if ($filter->getFrontendInput() == "price") {
                                if ($doPrice == 1) {
                                    $priceFilterModel = new Mage_Catalog_Model_Layer_Filter_Price();
                                    if ($priceFilterModel->getItemsCount()) {
                                        $each = array();
                                        $each["label"]   = $filter->getFrontendLabel();
                                        $each["code"]    = $filter->getAttributeCode();
                                        $priceOptions    = Mage::helper("mobikul/catalog")->getPriceFilter($priceFilterModel, $storeId);
                                        $each["options"] = $priceOptions;
                                        $layeredData[]   = $each;
                                    }
                                }
                            } else {
                                $doAttribute = 1;
                                if (count($filterData) > 0) {
                                    if (in_array($filter->getAttributeCode(), $filterData[1]))
                                        $doAttribute = 0;
                                }
                                if ($doAttribute == 1) {
                                    $attributeFilterModel = Mage::getModel("catalog/layer_filter_attribute")->setAttributeModel($filter);
                                    if ($attributeFilterModel->getItemsCount()) {
                                        $each = array();
                                        $each["label"]    = $filter->getFrontendLabel();
                                        $each["code"]     = $filter->getAttributeCode();
                                        // $attributeOptions = Mage::helper("mobikul/catalog")->getAttributeFilter($attributeFilterModel, $filter);
                                        $attributeOptions = $this->getCustomAttributeFilter($productCollection, $attributeFilterModel, $filter);
                                        $each["options"]  = $attributeOptions;
                                        $layeredData[]    = $each;
                                    }
                                }
                            }
                        }
                        
                        foreach ($category->getAvailableSortByOptions() as $key => $order) {
                            $each = array();
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["layeredData"] = $layeredData;
                        $returnArray["sortingData"] = $sortingData;
                        if ($customerId != 0) {
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId)->getItemsQty() * 1;
                        }
                        $categoryImageCollection = Mage::getModel("mobikul/categoryimages")->getCollection()->addFieldToFilter("category_id", $categoryId);
                        $bannerWidth = $width;
                        $bannerWidth *= $mFactor;
                        $bannerHeight = ($width/2)*$mFactor;
                        foreach ($categoryImageCollection as $categoryImage) {
                            if ($categoryImage->getBanner() != "") {
                                $basePath = Mage::getBaseDir("media").DS.$categoryImage->getBanner();
                                if (is_file($basePath)) {
                                    $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."categoryimages".DS.$bannerWidth."x".$bannerHeight.DS.$categoryImage->getBanner();
                                    Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                                    $returnArray["bannerImage"] = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."categoryimages".DS.$bannerWidth."x".$bannerHeight.DS.$categoryImage->getBanner();
                                    $returnArray["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath);                                    
                                }
                            }
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function searchResultAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["totalCount"]   = 0;
            $returnArray["productList"]  = array();
            $returnArray["sortingData"]  = array();
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])     ? $wholeData["storeId"]     : 1;
                        $searchQuery  = isset($wholeData["searchQuery"]) ? $wholeData["searchQuery"] : "";
                        $width        = isset($wholeData["width"])       ? $wholeData["width"]       : 1000;
                        $pageNumber   = isset($wholeData["pageNumber"])  ? $wholeData["pageNumber"]  : 1;
                        $customerId   = isset($wholeData["customerId"])  ? $wholeData["customerId"]  : 0;
                        $sortData     = isset($wholeData["sortData"])    ? $wholeData["sortData"]    : "[]";
                        $sortData     = Mage::helper("core")->jsonDecode($sortData);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
// Searching for product ////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $isFlatEnabled = Mage::getResourceModel("catalog/product_collection")->isEnabledFlat();
                        $collection = Mage::getResourceModel("catalog/product_collection")->addAttributeToSelect("*");
                        if($isFlatEnabled)   {
                            $query = Mage::getModel("catalogsearch/query")->setQueryText($searchQuery)->prepare();
                            $collection = Mage::getModel("catalog/product")
                                          ->getCollection()
                                          ->addAttributeToFilter(
                                              array(
                                                  array("attribute"=>"sku", "like"=>"%".$searchQuery."%"),
                                                  array("attribute"=>"name", "like"=>"%".$searchQuery."%"),
                                                  array("attribute"=>"description", "like"=>"%".$searchQuery."%")
                                              )
                                          );
                        }
                        else{
                            $query = Mage::getModel("catalogsearch/query")->loadByQuery($searchQuery);
                            $query->setStoreId($storeId);
                            if ($query->getId())
                                $query->setPopularity($query->getPopularity()+1);
                            else{
                                $query->setQueryText($searchQuery)->setIsActive(1)->setIsProcessed(1)->setDisplayInTerms(1);//->save();
                                $query->setPopularity(1);
                            }
                            $query->prepare();
                            $fulltextResource = Mage::getResourceModel("catalogsearch/fulltext")->prepareResult(Mage::getModel("catalogsearch/fulltext"), $searchQuery, $query);
                            $collection = $query->getSearchCollection()
                                ->addSearchFilter($searchQuery)
                                ->addAttributeToSelect("*")
                                ->addStoreFilter()
                                ->setStore(Mage::getModel("core/store")->load($storeId))
                                ->addAttributeToFilter("visibility", array("in" => array(2, 3, 4)))
                                ->addAttributeToFilter("status", 1);
                        }
                        Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($collection);
                        Mage::getSingleton("catalog/product_visibility")->addVisibleInSearchFilterToCollection($collection);
                        if (Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 0) {
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($collection);
                            Mage::getSingleton("catalog/product_status")->addSaleableFilterToCollection($collection);
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $collection->setOrder($sortBy, "ASC");
                            else
                                $collection->setOrder($sortBy, "DESC");
                        }
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $collection->getSize();
                            $query->setNumResults($collection->getSize())->save();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $collection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = array();
                        foreach ($collection as $product){
                            $eachProduct = Mage::getModel("catalog/product")->load($product->getId());
                                $productList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// getting sorting collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
                        $availableOrders = $toolbar->getAvailableOrders();
                        unset($availableOrders["position"]);
                        $availableOrders = array_merge(array("relevance" => "Relevance"), $availableOrders);
                        foreach ($availableOrders as $key => $order) {
                            $each = array();
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"]     = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function advancedSearchFormDataAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["fieldList"]    = array();
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"]) ? $wholeData["storeId"] : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $attributes = Mage::getSingleton("catalogsearch/advanced")->getAttributes();
                        foreach ($attributes as $attribute) {
                            $each = array();
                            $code = $attribute->getAttributeCode();
                            $label = $attribute->getStoreLabel();
                            $each["label"] = $label;
                            $each["inputType"] = Mage::helper("mobikul/catalog")->getAttributeInputType($attribute);
                            $each["attributeCode"] = $code;
                            $each["maxQueryLength"] = Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_LENGTH, $storeId);
                            $each["title"] = Mage::helper("core")->stripTags($label);
                            $each["options"] = $attribute->getSource()->getAllOptions(false);
                            $returnArray["fieldList"][] = $each;
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function advancedSearchResultAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["totalCount"]   = 0;
            $returnArray["productList"]  = array();
            $returnArray["sortingData"]  = array();
            $returnArray["critariaData"] = array();
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])     ? $wholeData["storeId"]     : 1;
                        $queryString  = isset($wholeData["queryString"]) ? $wholeData["queryString"] : "[]";
                        $sortData     = isset($wholeData["sortData"])    ? $wholeData["sortData"]    : "[]";
                        $width        = isset($wholeData["width"])       ? $wholeData["width"]       : 1000;
                        $pageNumber   = isset($wholeData["pageNumber"])  ? $wholeData["pageNumber"]  : 1;
                        $customerId   = isset($wholeData["customerId"])  ? $wholeData["customerId"]  : 0;
                        $queryArray   = Mage::helper("core")->jsonDecode($queryString);
                        $sortData     = Mage::helper("core")->jsonDecode($sortData);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
// Getting Product Collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        $queryArray = Mage::helper("mobikul/catalog")->getQueryArray($queryArray);
                        $productCollection = Mage::getSingleton("catalogsearch/advanced")->addFilters($queryArray)->getProductCollection();
// Sorting Product Collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $productList = array();
                        foreach ($productCollection as $product)    {
                            $eachProduct = Mage::getModel("catalog/product")->load($product->getId());
                                $productList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Getting Sorting Collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
                        $availableOrders = $toolbar->getAvailableOrders();
                        unset($availableOrders["position"]);
                        $availableOrders = array_merge(array("relevance" => "Relevance"), $availableOrders);
                        foreach ($availableOrders as $key => $order) {
                            $each = array();
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
// Getting Criteria Data ////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $critariaData = array();
                        $advancedSearchBlock = new Mage_CatalogSearch_Block_Advanced_Result();
                        $searchCriterias = $advancedSearchBlock->getSearchCriterias();
                        foreach (array("left", "right") as $side) {
                            if ($searchCriterias[$side]) {
                                foreach ($searchCriterias[$side] as $criteria)
                                    $critariaData[] = Mage::helper("core")->stripTags($criteria["name"])." : ".Mage::helper("core")->stripTags($criteria["value"]);
                            }
                        }
                        $returnArray["critariaData"] = $critariaData;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $returnArray["success"] = true;
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function ratingDetailsAction()   {
            $returnArray                         = array();
            $returnArray["authKey"]              = "";
            $returnArray["responseCode"]         = 0;
            $returnArray["success"]              = false;
            $returnArray["message"]              = "";
            $returnArray["name"]                 = "";
            $returnArray["thumbNail"]            = "";
            $returnArray["formatedFinalPrice"]   = "";
            $returnArray["finalPrice"]           = 0.0;
            $returnArray["formatedMinPrice"]     = "";
            $returnArray["minPrice"]             = 0.0;
            $returnArray["formatedMaxPrice"]     = "";
            $returnArray["maxPrice"]             = 0.0;
            $returnArray["formatedSpecialPrice"] = "";
            $returnArray["specialPrice"]         = 0.0;
            $returnArray["typeId"]               = "";
            $returnArray["reviewList"]           = array();
            $returnArray["ratingData"]           = array();
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])   ? $wholeData["storeId"]   : 1;
                        $productId    = isset($wholeData["productId"]) ? $wholeData["productId"] : 0;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $isIncludeTaxInPrice = 0;
                        if (Mage::getStoreConfig("tax/display/type", $storeId) == 2)
                            $isIncludeTaxInPrice = 1;
// Getting Produc Rating Details ////////////////////////////////////////////////////////////////////////////////////////////////
                        $product = Mage::getModel("catalog/product")->load($productId);
                        $returnArray["name"] = $product->getName();
                        
                        $imageData = Mage::helper("mobikul/image")->init($product, "small_image")->keepFrame(true)->resize(150)->__toString();
                        
                        $returnArray["thumbNail"] = $imageData[0];
                        $returnArray["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                        if ($isIncludeTaxInPrice) {
                            $returnArray["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getFinalPrice())));
                            $returnArray["finalPrice"] = Mage::helper("tax")->getPrice($product, $product->getFinalPrice());
                            $returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getMinPrice())));
                            $returnArray["minPrice"] = Mage::helper("tax")->getPrice($product, $product->getMinPrice());
                            $returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getMaxPrice())));
                            $returnArray["maxPrice"] = Mage::helper("tax")->getPrice($product, $product->getMaxPrice());
                            $returnArray["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getSpecialPrice())));
                            $returnArray["specialPrice"] = Mage::helper("tax")->getPrice($product, $product->getSpecialPrice());
                        }
                        else{
                            $returnArray["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getFinalPrice()));
                            $returnArray["finalPrice"] = $product->getFinalPrice();
                            $returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMinPrice()));
                            $returnArray["minPrice"] = $product->getMinPrice();
                            $returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMaxPrice()));
                            $returnArray["maxPrice"] = $product->getMaxPrice();
                            $returnArray["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getSpecialPrice()));
                            $returnArray["specialPrice"] = $product->getSpecialPrice();
                        }
                        $returnArray["typeId"] = $product->getTypeId();
                        $reviewCollection = Mage::getModel("review/review")
                            ->getResourceCollection()
                            ->addStoreFilter($storeId)
                            ->addEntityFilter("product", $productId)
                            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                            ->setDateOrder()
                            ->addRateVotes();
                        $reviewList = array();
                        foreach ($reviewCollection as $review) {
                            $oneReview = array(); $ratings = array();
                            $oneReview["title"]   = Mage::helper("core")->stripTags($review->getTitle());
                            $oneReview["details"] = Mage::helper("core")->stripTags($review->getDetail());
                            $votes = $review->getRatingVotes();
                            if (count($votes)) {
                                foreach ($votes as $vote) {
                                    $oneVote = array();
                                    $oneVote["label"] = Mage::helper("core")->stripTags($vote->getRatingCode());
                                    $oneVote["value"] = number_format($vote->getValue(), 2, ".", "");
                                    $ratings[]        = $oneVote;
                                }
                            }
                            $oneReview["ratings"]  = $ratings;
                            $oneReview["reviewBy"] = Mage::helper("core")->__("Review by %s", Mage::helper("core")->stripTags($review->getNickname()));
                            $oneReview["reviewOn"] = Mage::helper("core")->__("(Posted on %s)", Mage::helper("core")->formatDate($review->getCreatedAt()), "long");
                            $reviewList[]          = $oneReview;
                        }
                        $returnArray["reviewList"] = $reviewList;
                        $ratingCollection = Mage::getModel("rating/rating")
                            ->getResourceCollection()
                            ->addEntityFilter("product")
                            ->setPositionOrder()
                            ->setStoreFilter($storeId)
                            ->addRatingPerStoreName($storeId)
                            ->load();
                        $ratingCollection->addEntitySummaryToItem($productId, $storeId);
                        $ratingData = array();
                        foreach ($ratingCollection as $rating) {
                            if ($rating->getSummary()) {
                                $eachRating = array();
                                $eachRating["ratingCode"] = Mage::helper("core")->stripTags($rating->getRatingCode());
                                $eachRating["ratingValue"] = number_format((5 * $rating->getSummary()) / 100, 2, ".", "");
                                $ratingData[] = $eachRating;
                            }
                        }
                        $returnArray["ratingData"] = $ratingData;
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function productPageDataAction()   {
            $returnArray                               = array();
            $returnArray["authKey"]                    = "";
            $returnArray["responseCode"]               = 0;
            $returnArray["success"]                    = false;
            $returnArray["message"]                    = "";
            $returnArray["id"]                         = 0;
            $returnArray["productUrl"]                 = "";
            $returnArray["name"]                       = "";
            $returnArray["formatedMinPrice"]           = "";
            $returnArray["minPrice"]                   = 0.0;
            $returnArray["formatedMaxPrice"]           = "";
            $returnArray["maxPrice"]                   = 0.0;
            $returnArray["formatedPrice"]              = "";
            $returnArray["price"]                      = 0.0;
            $returnArray["formatedFinalPrice"]         = "";
            $returnArray["finalPrice"]                 = 0.0;
            $returnArray["formatedSpecialPrice"]       = "";
            $returnArray["specialPrice"]               = 0.0;
            $returnArray["typeId"]                     = "";
            $returnArray["msrpEnabled"]                = 0;
            $returnArray["msrpDisplayActualPriceType"] = 0;
            $returnArray["msrp"]                       = 0.0;
            $returnArray["formatedMsrp"]               = "";
            $returnArray["shortDescription"]           = "";
            $returnArray["description"]                = "";
            $returnArray["isInRange"]                  = false;
            $returnArray["availability"]               = "";
            $returnArray["isAvailable"]                = false;
            $returnArray["priceFormat"]                = new stdClass();
            $returnArray["imageGallery"]               = array();
            $returnArray["additionalInformation"]      = array();
            $returnArray["ratingData"]                 = array();
            $returnArray["reviewList"]                 = array();
            $returnArray["customOptions"]              = array();
            $returnArray["links"]                      = new stdClass();
            $returnArray["samples"]                    = new stdClass();
            $returnArray["groupedData"]                = array();
            $returnArray["bundleOptions"]              = array();
            $returnArray["priceView"]                  = "";
            $returnArray["configurableData"]           = new stdClass();
            $returnArray["tierPrices"]                 = array();
            $returnArray["relatedProductList"]         = array();
            $returnArray["cartCount"]                  = 0;
            $returnArray["ratingFormData"]             = array();
            $returnArray["isAllowedGuestCheckout"]     = true;
            $returnArray["guestCanReview"]             = true;
            $returnArray["isInWishlist"]               = false;
            $returnArray["priceType"]                  = "";
            $returnArray["qtyIncrementsEnable"]        = false;
            $returnArray["displayBothPrice"]           = false;
            $returnArray["priceExcludingTax"]          = 0.0;
            $returnArray["priceIncludingTax"]          = 0.0;
            $returnArray["taxIncludeInPrice"]          = 0;
            $returnArray["priceIncludesTax"]           = false;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $productId  = isset($wholeData["productId"])  ? $wholeData["productId"]  : 0;
                        $width      = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $quoteId    = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $isIncludeTaxInPrice = false;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $product = Mage::getModel("catalog/product")->load($productId);
                        Mage::register("current_product", $product);
                        Mage::register("product", $product);
                        
                        if (Mage::getStoreConfig("tax/display/type", $storeId) == 2)
                            $isIncludeTaxInPrice = true;
                        $returnArray["id"] = $productId;
                        $returnArray["productUrl"] = $product->getProductUrl();
                        $returnArray["guestCanReview"] = (bool)Mage::getStoreConfig("catalog/review/allow_guest");
                        $returnArray["name"] = $product->getName();
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

                        $returnArray["displayBothPrice"]  = Mage::helper("tax")->displayBothPrices();
                        $returnArray["priceExcludingTax"] = $_coreHelper->formatPrice($_finalPrice, false);
                        $returnArray["priceIncludingTax"] = $_coreHelper->formatPrice($_finalPriceInclTax, false);
                        $returnArray["taxIncludeInPrice"] = Mage::helper("mobikul/catalog")->getIfTaxIncludeInPrice();
                        $returnArray["priceIncludesTax"]  = (Mage::helper("mobikul/catalog")->getPriceIncludesTax())? true : false;

                        if ($product->getTypeId() == "bundle") {
                            $bundlePriceModel = Mage::getModel("bundle/product_price");
                            $returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($product, "min", 1)));
                            $returnArray["minPrice"] = $bundlePriceModel->getTotalPrices($product, "min", 1);
                            $returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($product, "max", 1)));
                            $returnArray["maxPrice"] = $bundlePriceModel->getTotalPrices($product, "max", 1);
                        } else {
                            $returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMinPrice()));
                            $returnArray["minPrice"] = $product->getMinPrice();
                            $returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMaxPrice()));
                            $returnArray["maxPrice"] = $product->getMaxPrice();
                        }
                        if ($isIncludeTaxInPrice) {
                            $returnArray["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getPrice())));
                            $returnArray["price"] = Mage::helper("tax")->getPrice($product, $product->getPrice());
                            $returnArray["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getFinalPrice())));
                            $returnArray["finalPrice"] = Mage::helper("tax")->getPrice($product, $product->getFinalPrice());
                            $returnArray["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency(Mage::helper("tax")->getPrice($product, $product->getSpecialPrice())));
                            $returnArray["specialPrice"] = Mage::helper("tax")->getPrice($product, $product->getSpecialPrice());
                        }
                        else{
                            $returnArray["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getPrice()));
                            $returnArray["price"] = $product->getPrice();
                            $returnArray["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getFinalPrice()));
                            $returnArray["finalPrice"] = $product->getFinalPrice();
                            $returnArray["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getSpecialPrice()));
                            $returnArray["specialPrice"] = $product->getSpecialPrice();
                        }
                        if ($product->getStockItem()->getQtyIncrements()) {
                            $returnArray["qtyIncrementsEnable"] = ($product->getStockItem()->getQtyIncrements())? true :false;
                            $returnArray["qtyIncrements"] = $product->getStockItem()->getQtyIncrements();
                            $returnArray["qtyIncrementsMsg"] = Mage::helper('cataloginventory')->__('%s is available for purchase in increments of %s', $product->getName(), $product->getStockItem()->getQtyIncrements());
                        }
                        $returnArray["typeId"] = $product->getTypeId();
                        $returnArray["msrpEnabled"] = $product->getMsrpEnabled();
                        $returnArray["msrpDisplayActualPriceType"] = $product->getMsrpDisplayActualPriceType();
                        $returnArray["msrp"] = $product->getMsrp();
                        $returnArray["formatedMsrp"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($product->getMsrp()));
                        $returnArray["shortDescription"] = html_entity_decode(Mage::helper("core")->stripTags($product->getShortDescription()));
                        $returnArray["description"] = Mage::helper('catalog/output')->productAttribute($product, $product->getDescription(), 'description');
                        $fromdate = $product->getSpecialFromDate();
                        $todate = $product->getSpecialToDate();
                        $isInRange = false;
                        if (isset($fromdate) && isset($todate)) {
                            $today     = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                            $todayTime = strtotime($today);
                            $fromTime  = strtotime($fromdate);
                            $toTime    = strtotime($todate);
                            if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                                $isInRange = true;
                            }
                        }
                        if (isset($fromdate) && !isset($todate)) {
                            $today     = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                            $todayTime = strtotime($today);
                            $fromTime  = strtotime($fromdate);
                            if ($todayTime >= $fromTime) {
                                $isInRange = true;
                            }
                        }
                        if(!isset($fromdate) && isset($todate)){
                            $today      = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                            $today_time = strtotime($today);
                            $from_time  = strtotime($fromdate);
                            if($today_time <= $from_time)
                                $isInRange = true;
                        }
                        $returnArray["isInRange"] = $isInRange;
                        if ($product->isAvailable()) {
                            $returnArray["availability"] = Mage::helper("mobikul")->__("In stock");
                            $returnArray["isAvailable"] = true;
                        } else {
                            $returnArray["availability"] = Mage::helper("mobikul")->__("Out of stock");
                        }
// getting price format /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["priceFormat"] = Mage::app()->getLocale()->getJsPriceFormat();
// getting image galleries //////////////////////////////////////////////////////////////////////////////////////////////////////
                        $galleryCollection = $product->getMediaGalleryImages();
                        $imageGallery = array();
                        foreach ($galleryCollection as $image) {
                            $eachImage = array();
                            $eachImage["smallImage"] = Mage::helper("catalog/image")->init($product, "thumbnail", $image->getFile())->keepFrame(false)->resize($width / 3)->__toString();
                            $eachImage["largeImage"] = Mage::helper("catalog/image")->init($product, "thumbnail", $image->getFile())->keepFrame(false)->resize($width)->__toString();
                            $eachImage["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($image->getPath());
                            $imageGallery[] = $eachImage;
                        }
                        
                         if (empty($imageGallery)) {
                            $placeholder = Mage::helper("catalog/image")->init($product, "image")->getPlaceholder();
                            $imageGallery[0]["smallImage"] = Mage::helper("catalog/image")->init($product, "image")->keepFrame(false)->resize($width / 3)->__toString();
                            $imageGallery[0]["largeImage"] = Mage::helper("catalog/image")->init($product, "image")->keepFrame(false)->resize($width)->__toString();
                            $imageGallery[0]["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor(Mage::getDesign()->getSkinBaseDir(['_package' => "base"]).DS.$placeholder);
                        }

                        $returnArray["imageGallery"] = $imageGallery;
//getting additional information ////////////////////////////////////////////////////////////////////////////////////////////////
                        $additionalInformation = array();
                        foreach ($product->getAttributes() as $attribute) {
                            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), array())) {
                                $value = $attribute->getFrontend()->getValue($product);
                                if (!$product->hasData($attribute->getAttributeCode()))
                                    $value = Mage::helper("catalog")->__("N/A");
                                elseif ((string) $value == "")
                                    $value = Mage::helper("catalog")->__("No");
                                elseif ($attribute->getFrontendInput() == "price" && is_string($value))
                                    $value = Mage::app()->getStore()->convertPrice($value, true);
                                if (is_string($value) && strlen($value)) {
                                    $eachAttribute = array();
                                    $eachAttribute["label"] = html_entity_decode(Mage::helper("core")->stripTags($attribute->getStoreLabel()));
                                    $eachAttribute["value"] = html_entity_decode(Mage::helper("core")->stripTags($value));
                                    $additionalInformation[] = $eachAttribute;
                                }
                            }
                        }
                        $returnArray["additionalInformation"] = $additionalInformation;
//getting rating form data //////////////////////////////////////////////////////////////////////////////////////////////////////
                        $ratingCollection = Mage::getModel("rating/rating")
                            ->getResourceCollection()
                            ->addEntityFilter("product")
                            ->setPositionOrder()
                            ->addRatingPerStoreName($storeId)
                            ->setStoreFilter($storeId)
                            ->load()
                            ->addOptionToItems();
                        $ratingFormData = array();
                        foreach ($ratingCollection as $rating) {
                            $eachTypeRating = array();
                            $eachRatingFormData = array();
                            foreach ($rating->getOptions() as $option)
                                $eachTypeRating[] = $option->getId();
                            $eachRatingFormData["id"] = $rating->getId();
                            $eachRatingFormData["name"] = Mage::helper("core")->stripTags($rating->getRatingCode());
                            $eachRatingFormData["values"] = $eachTypeRating;
                            $ratingFormData[] = $eachRatingFormData;
                        }
                        $returnArray["ratingFormData"] = $ratingFormData;
//getting rating list ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $ratingCollection = Mage::getModel("rating/rating")
                            ->getResourceCollection()
                            ->addEntityFilter("product")
                            ->setPositionOrder()
                            ->setStoreFilter($storeId)
                            ->addRatingPerStoreName($storeId)
                            ->load();
                        $ratingCollection->addEntitySummaryToItem($productId, $storeId);
                        $ratingData = array();
                        foreach ($ratingCollection as $rating) {
                            if ($rating->getSummary()) {
                                $eachRating = array();
                                $eachRating["ratingCode"] = Mage::helper("core")->stripTags($rating->getRatingCode());
                                $eachRating["ratingValue"] = number_format((5 * $rating->getSummary()) / 100, 2, ".", "");
                                $ratingData[] = $eachRating;
                            }
                        }
                        $returnArray["ratingData"] = $ratingData;
//getting review list ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $reviewCollection = Mage::getModel("review/review")
                            ->getResourceCollection()
                            ->addStoreFilter($storeId)
                            ->addEntityFilter("product", $productId)
                            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                            ->setDateOrder()->addRateVotes();
                        $reviewList = array();
                        foreach ($reviewCollection as $review) {
                            $oneReview = array(); $ratings = array();
                            $oneReview["title"] = Mage::helper("core")->stripTags($review->getTitle());
                            $oneReview["details"] = Mage::helper("core")->stripTags($review->getDetail());
                            $votes = $review->getRatingVotes();
                            if (count($votes)) {
                                foreach ($votes as $vote) {
                                    $oneVote = array();
                                    $oneVote["label"] = Mage::helper("core")->stripTags($vote->getRatingCode());
                                    $oneVote["value"] = number_format($vote->getValue(), 2, ".", "");
                                    $ratings[] = $oneVote;
                                }
                            }
                            $oneReview["ratings"] = $ratings;
                            $oneReview["reviewBy"] = Mage::helper("core")->__("Review by %s", Mage::helper("core")->stripTags($review->getNickname()));
                            $oneReview["reviewOn"] = Mage::helper("core")->__("(Posted on %s)", Mage::helper("core")->formatDate($review->getCreatedAt()), "long");
                            $reviewList[] = $oneReview;
                        }
                        $returnArray["reviewList"] = $reviewList;
// getting custom options ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        $optionBlock = new Mage_Catalog_Block_Product_View_Options();
                        $options = Mage::helper("core")->decorateArray($optionBlock->getOptions());
                        $customOptions = array();
                        if (count($options)) {
                            $eachOption = array();
                            foreach ($options as $option) {
                                $eachOption = $option->getData();
                                $eachOption["unformated_default_price"] = Mage::helper("core")->currency($option->getDefaultPrice(), false, false);
                                $eachOption["formated_default_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($option->getDefaultPrice()));
                                $eachOption["unformated_price"] = Mage::helper("core")->currency($option->getPrice(), false, false);
                                $eachOption["formated_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($option->getPrice()));
                                $optionValueCollection = $option->getValues();
                                foreach ($optionValueCollection as $optionValue) {
                                    $eachOptionValue = array();
                                    $eachOptionValue = $optionValue->getData();
                                    $eachOptionValue["formated_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($optionValue->getPrice()));
                                    $eachOptionValue["formated_default_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($optionValue->getDefaultPrice()));
                                    $eachOption["optionValues"][] = $eachOptionValue;
                                }
                                $customOptions[] = $eachOption;
                            }
                        }
                        $returnArray["customOptions"] = $customOptions;
// getting downloadable product data ////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "downloadable") {
                            $linkArray = array();
                            $downloadableBlock = new Mage_Downloadable_Block_Catalog_Product_Links();
                            $linkArray["title"] = $downloadableBlock->getLinksTitle();
                            $linkArray["linksPurchasedSeparately"] = $downloadableBlock->getLinksPurchasedSeparately();
                            $links = $downloadableBlock->getLinks();
                            $linkData = array();
                            foreach ($links as $link) {
                                $eachLink = array();
                                $eachLink["id"] = $linkId = $link->getId();
                                $eachLink["linkTitle"] = $link->getTitle() ? $link->getTitle() : "";
                                $eachLink["price"] = Mage::helper("core")->currency($link->getPrice(), false, false);
                                $eachLink["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($link->getPrice()));
                                if ($link->getSampleFile() || $link->getSampleUrl()) {
                                    $link = Mage::getModel("downloadable/link")->load($linkId);
                                    if ($link->getId()) {
                                        if ($link->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                                            $eachLink["url"] = $link->getSampleUrl();
                                            $fileArray = explode(DS, $link->getSampleUrl());
                                            $eachLink["fileName"] = end($fileArray);
                                        } elseif ($link->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                                            $sampleLinkFilePath = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Link::getBaseSamplePath(), $link->getSampleFile());
                                            $eachLink["url"] = Mage::getUrl("mobikulhttp/download/downloadlinksample", array("linkId" => $linkId));
                                            $fileArray = explode(DS, $sampleLinkFilePath);
                                            $eachLink["fileName"] = end($fileArray);
                                        }
                                    }
                                    $eachLink["haveLinkSample"]  = 1;
                                    $eachLink["linkSampleTitle"] = Mage::helper("downloadable")->__("sample");
                                }
                                $linkData[] = $eachLink;
                            }
                            $linkArray["linkData"] = $linkData;
                            $returnArray["links"] = $linkArray;
                            $linkSampleArray = array();
                            $downloadableSampleBlock = new Mage_Downloadable_Block_Catalog_Product_Samples();
                            $linkSampleArray["hasSample"] = $downloadableSampleBlock->hasSamples();
                            $linkSampleArray["title"] = $downloadableSampleBlock->getSamplesTitle();
                            $linkSamples = $downloadableSampleBlock->getSamples();
                            $linkSampleData = array();
                            foreach ($linkSamples as $linkSample) {
                                $eachSample = array();
                                $sampleId = $linkSample->getId();
                                $eachSample["sampleTitle"] = Mage::helper("core")->stripTags($linkSample->getTitle());
                                $sample = Mage::getModel("downloadable/sample")->load($sampleId);
                                if ($sample->getId()) {
                                    if ($sample->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                                        $eachSample["url"] = $sample->getSampleUrl();
                                        $fileArray = explode(DS, $sample->getSampleUrl());
                                        $eachSample["fileName"] = end($fileArray);
                                        $buffer = file_get_contents($sample->getSampleUrl());
                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        $eachSample["mimeType"] = $finfo->buffer($buffer);
                                    } elseif ($sample->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                                        $sampleFilePath = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Sample::getBasePath(), $sample->getSampleFile());
                                        $eachSample["mimeType"] = mime_content_type($sampleFilePath);
                                        $eachSample["url"] = Mage::getUrl("mobikulhttp/download/downloadsample", array("sampleId" => $sampleId));
                                        $fileArray = explode(DS, $sampleFilePath);
                                        $eachSample["fileName"] = end($fileArray);
                                    }
                                }
                                $linkSampleData[] = $eachSample;
                            }
                            $linkSampleArray["linkSampleData"] = $linkSampleData;
                            $returnArray["samples"] = $linkSampleArray;
                        }
// getting grouped product data /////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "grouped") {
                            $groupedParentId = Mage::getModel("catalog/product_type_grouped")->getParentIdsByChild($product->getId());
                            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                            $groupedData = array();
                            foreach ($associatedProducts as $associatedProduct) {
                                $eachAssociatedProduct = array();
                                $eachAssociatedProduct["name"]        = Mage::helper("core")->stripTags($associatedProduct->getName());
                                $eachAssociatedProduct["id"]          = $associatedProduct->getId();
                                $eachAssociatedProduct["defaultQty"]  = $associatedProduct->getQty();
                                if ($associatedProduct->isAvailable()) {
                                    $eachAssociatedProduct["isAvailable"] = $associatedProduct->isAvailable();
                                } else {
                                    $eachAssociatedProduct["isAvailable"] = false;
                                }
// not working on web ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                                // $fromdate = $associatedProduct->getSpecialFromDate();
                                // $todate = $associatedProduct->getSpecialToDate();
                                // $isInRange = false;
                                // if (isset($fromdate) && isset($todate)) {
                                //     $today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                                //     $todayTime = strtotime($today);
                                //     $fromTime = strtotime($fromdate);
                                //     $toTime = strtotime($todate);
                                //     if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                                //         $isInRange = true;
                                //     }
                                // }
                                // if (isset($fromdate) && !isset($todate)) {
                                //     $today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                                //     $todayTime = strtotime($today);
                                //     $fromTime = strtotime($fromdate);
                                //     if ($todayTime >= $fromTime) {
                                //         $isInRange = true;
                                //     }
                                // }
                                // if (!isset($fromdate) && isset($todate)) {
                                //     $today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
                                //     $todayTime = strtotime($today);
                                //     $fromTime = strtotime($fromdate);
                                //     if ($todayTime <= $fromTime) {
                                //         $isInRange = true;
                                //     }
                                // }
                                $eachAssociatedProduct["formattedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($associatedProduct->getSpecialPrice()));
                                $eachAssociatedProduct["specialPrice"] = $associatedProduct->getSpecialPrice();
                                $eachAssociatedProduct["foramtedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($associatedProduct->getPrice()));
                                $eachAssociatedProduct["price"] = $associatedProduct->getPrice();
                                $imageData = Mage::helper("mobikul/image")->init($associatedProduct, "thumbnail")->keepFrame(true)->resize($width / 5)->__toString();
                                $eachAssociatedProduct["thumbNail"] = $imageData[0];
                                $eachAssociatedProduct["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imageData[1]);
                                $groupedData[] = $eachAssociatedProduct;
                            }
                            $returnArray["groupedData"] = $groupedData;
                        }
// getting bundle product options ///////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "bundle") {
                            $typeInstance = $product->getTypeInstance(true);
                            $typeInstance->setStoreFilter($product->getStoreId(), $product);
                            $optionCollection = $typeInstance->getOptionsCollection($product);
                            $selectionCollection = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
                            $bundleOptionCollection = $optionCollection->appendSelections($selectionCollection, false, Mage::helper("catalog/product")->getSkipSaleableCheck());
                            $bundleOptionBlock = new Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option();
                            $bundleOptions = array();
                            foreach ($bundleOptionCollection as $bundleOption) {
                                $oneOption = array();
                                if (!$bundleOption->getSelections())
                                    continue;
                                $oneOption = $bundleOption->getData();
                                $selections = $bundleOption->getSelections();
                                unset($oneOption["selections"]);
                                $bundleOptionValues = array();
                                foreach ($selections as $selection) {
                                    $eachBundleOptionValues = array();
                                    if ($selection->isSaleable()) {
                                        $coreHelper = Mage::helper("core");
                                        $taxHelper = Mage::helper("tax");
                                        $price = $product->getPriceModel()->getSelectionPreFinalPrice($product, $selection, 1);
                                        $priceTax = $taxHelper->getPrice($product, $price);
                                        if ($oneOption["type"] == "checkbox" || $oneOption["type"] == "multi") {
                                            $eachBundleOptionValues["title"] = str_replace("&nbsp;", " ", Mage::helper("core")->stripTags($bundleOptionBlock->getSelectionQtyTitlePrice($selection)));
                                        }
                                        if ($oneOption["type"] == "radio" || $oneOption["type"] == "select") {
                                            $eachBundleOptionValues["title"] = str_replace("&nbsp;", " ", Mage::helper("core")->stripTags($bundleOptionBlock->getSelectionTitlePrice($selection, false)));
                                        }
                                        $eachBundleOptionValues["isQtyUserDefined"] = $selection->getSelectionCanChangeQty();
                                        $eachBundleOptionValues["optionId"] = $selection->getId();
                                        $eachBundleOptionValues["isDefault"] = $selection->getIsDefault();
                                        $eachBundleOptionValues["optionValueId"] = $selection->getSelectionId();
                                        $eachBundleOptionValues["foramtedPrice"] = $coreHelper->currencyByStore($priceTax, $product->getStore(), true, true);
                                        $eachBundleOptionValues["price"] = $coreHelper->currencyByStore($priceTax, $product->getStore(), false, false);
                                        $eachBundleOptionValues["isSingle"] = (count($selections) == 1 && $bundleOption->getRequired());
                                        $eachBundleOptionValues["defaultQty"] = $selection->getSelectionQty();
                                        $bundleOptionValues[] = $eachBundleOptionValues;
                                    }
                                }
                                $oneOption["optionValues"] = $bundleOptionValues;
                                $bundleOptions[] = $oneOption;
                            }
                            $returnArray["bundleOptions"] = $bundleOptions;
                            $returnArray["priceView"] = $product->getPriceView();
                            $returnArray["priceType"] = $product->getPriceType();
                        }
// getting bundle product options ///////////////////////////////////////////////////////////////////////////////////////////////
                        if ($product->getTypeId() == "configurable") {
                            $configurableBlock = new Webkul_MobiKul_Block_Configurable();
                            $returnArray["configurableData"] = $configurableBlock->getJsonConfig();
                        }
// getting tier prices /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $allTierPrices = array();
                        $tierBlock = new Mage_Catalog_Block_Product_Price();
                        $tierPrices = $tierBlock->getTierPrices();
                        foreach ($tierPrices as $price) {
                            $allTierPrices[] = Mage::helper("core")->__("Buy %s for %s each", $price["price_qty"], Mage::helper("core")->stripTags($price["formated_price_incl_tax"]))." ".Mage::helper("mobikul")->__("and")." ".Mage::helper("mobikul")->__("save")." ".$price["savePercent"]."%";
                        }
                        $returnArray["tierPrices"] = $allTierPrices;
// getting related product //////////////////////////////////////////////////////////////////////////////////////////////////////
                        $relatedProductCollection = $product->getRelatedProductIds();
                        $relatedProductList = array();
                        foreach ($relatedProductCollection as $id) {
                            $product = Mage::getModel("catalog/product")->load($id);
                            $relatedProductList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($product, $storeId, $width, $customerId);
                        }
                        $returnArray["relatedProductList"] = $relatedProductList;
                        $quote = new Varien_Object();
                        if ($customerId != 0) {
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                            $returnArray["isAllowedGuestCheckout"] = $quote->isAllowedGuestCheckout();
// checking for product in wishlist /////////////////////////////////////////////////////////////////////////////////////////////
                            $customer   = Mage::getModel("customer/customer")->load($customerId);
                            $wishlist   = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer);
                            $wishlistCollection = Mage::getModel("wishlist/item")->getCollection()
                                    ->addFieldToFilter("wishlist_id", $wishlist->getId())
                                    ->addFieldToFilter("product_id", $productId);
                            $item = $wishlistCollection->getFirstItem();
                            $returnArray["isInWishlist"] = !!$item->getId();
                            if ($returnArray["isInWishlist"])
                                $returnArray["itemId"] = $item->getId();
                        }
                        if ($quoteId != 0){
                            $quote = Mage::getModel("sales/quote")->load($quoteId);
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                            $returnArray["isAllowedGuestCheckout"] = $quote->isAllowedGuestCheckout();
                        }
                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function addtoWishlistAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["success"]      = false;
            $returnArray["itemId"]       = 0;
            $returnArray["message"]      = "";
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $productId    = isset($wholeData["productId"])  ? $wholeData["productId"]  : 0;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $params       = isset($wholeData["params"])     ? ($wholeData["params"] == '' ? '{}' : $wholeData["params"] )     : "{}";
                        $params       = Mage::helper("core")->jsonDecode($params);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
                        $product  = Mage::getModel("catalog/product")->load($productId);
                        $paramOptionsArray = array();
                        $paramOption = array();
                        if(isset($params["options"])){
                            $productOptions = $params["options"];
                            foreach ($productOptions as $optionId => $values) {
                                $option = Mage::getModel("catalog/product_option")->load($optionId);
                                $optionType = $option->getType();
                                if (in_array($optionType, array("multiple", "checkbox"))) {
                                    foreach ($values as $optionValue)
                                        $paramOption[$optionId][] = $optionValue;
                                } elseif (in_array($optionType, array("radio", "drop_down", "area", "field"))) {
                                    $paramOption[$optionId] = $values;
                                } elseif ($optionType == "file") {
// downloading file /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $base64String = $productOptions["optionId"]["encodeImage"];
                                    $fileName = time().$productOptions["optionId"]["name"];
                                    $fileType = $productOptions["optionId"]["type"];
                                    $fileWithPath = Mage::getBaseDir().DS."media".DS.$fileName;
                                    $ifp = fopen($fileWithPath, "wb");
                                    fwrite($ifp, base64_decode($base64String));
// assigning file to option /////////////////////////////////////////////////////////////////////////////////////////////////////
                                    $fileOption = array(
                                        "type"       => $fileType,
                                        "title"      => $fileName,
                                        "quote_path" => DS."media".DS.$fileName,
                                        "fullpath"   => $fileWithPath,
                                        "secret_key" => substr(md5(file_get_contents($fileWithPath)), 0, 20)
                                    );
                                    $filesToDelete[] = $fileWithPath;
                                    $paramOption[$optionId] = $fileOption;
                                } elseif ($optionType == "date") {
                                    $paramOption[$optionId]["month"]    = $values["month"];
                                    $paramOption[$optionId]["day"]      = $values["day"];
                                    $paramOption[$optionId]["year"]     = $values["year"];
                                } elseif ($optionType == "date_time") {
                                    $paramOption[$optionId]["month"]    = $values["month"];
                                    $paramOption[$optionId]["day"]      = $values["day"];
                                    $paramOption[$optionId]["year"]     = $values["year"];
                                    $paramOption[$optionId]["hour"]     = $values["hour"];
                                    $paramOption[$optionId]["minute"]   = $values["minute"];
                                    $paramOption[$optionId]["day_part"] = $values["day_part"];
                                } elseif ($optionType == "time") {
                                    $paramOption[$optionId]["hour"]     = $values["hour"];
                                    $paramOption[$optionId]["minute"]   = $values["minute"];
                                    $paramOption[$optionId]["day_part"] = $values["day_part"];
                                }
                            }
                            if (count($paramOption) > 0)
                                $paramOptionsArray["options"] = $paramOption;
                        }
                        if ($product->getTypeId() == "downloadable") {
                            if(isset($params["links"]))
                                $paramOptionsArray["links"] = $params["links"];
                        } elseif ($product->getTypeId() == "grouped") {
                            if (isset($params["super_group"]))
                                $paramOptionsArray["super_group"] = $params["super_group"];
                        } elseif ($product->getTypeId() == "configurable") {
                            if(isset($params["super_attribute"]))
                                $paramOptionsArray["super_attribute"] = $params["super_attribute"];
                        } elseif ($product->getTypeId() == "bundle") {
                            if(isset($params["bundle_option"]) && isset($params["bundle_option_qty"]))  {
                                $paramOptionsArray["bundle_option"] = $params["bundle_option"];
                                $paramOptionsArray["bundle_option_qty"] = $params["bundle_option_qty"];
                            }
                        }
                        if (count($paramOptionsArray) > 0)
                            $buyRequest = new Varien_Object($paramOptionsArray);
                        else
                            $buyRequest = new Varien_Object();
                        if (!$product->getId() || !$product->isVisibleInCatalog()) {
                            $returnArray["message"] = Mage::helper("wishlist")->__("Cannot specify product.");
                            $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                            return;
                        }
                        $result = $wishlist->addNewItem($product, $buyRequest);
                        if (is_string($result))
                            Mage::throwException($result);
                        $wishlist->save();
                        Mage::dispatchEvent("wishlist_add_product", array("wishlist" => $wishlist, "product" => $product, "item" => $result));

                        $returnArray["itemId"] = $result->getData('wishlist_item_id');

                        $returnArray["success"] = true;
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $returnArray["message"] = Mage::helper("wishlist")->__("An error occurred while adding item to wishlist: %s", $e->getMessage());
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            } catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        // public function searchSuggestionDataAction()    {
        //     try {
        //         $returnArray = array();
        //         $returnArray["authKey"]      = "";
        //         $returnArray["responseCode"] = 0;
        //         $returnArray["message"]      = "";
        //         $returnArray["keyWordList"]  = array();
        //         $this->getResponse()->setHeader("Content-type", "application/json");
        //         $authKey     = $this->getRequest()->getHeader("authKey");
        //         $apiKey      = $this->getRequest()->getHeader("apiKey");
        //         $apiPassword = $this->getRequest()->getHeader("apiPassword");
        //         $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
        //         if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
        //             $returnArray["authKey"]      = $authData["authKey"];
        //             $returnArray["responseCode"] = $authData["responseCode"];
        //             $productCollection = Mage::getModel("catalog/product")->getCollection()->addAttributeToSelect("name");
        //             $keyWordList = array();
        //             foreach ($productCollection as $eachProduct) {
        //                 if(!in_array($eachProduct->getName(), $keyWordList))
        //                     $keyWordList[] = $eachProduct->getName();
        //             }
        //             $returnArray["keyWordList"] = $keyWordList;
        //             $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
        //             return;
        //         } else {
        //             $returnArray["responseCode"] = $authData["responseCode"];
        //             $returnArray["message"]      = $authData["message"];
        //             $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
        //             return;
        //         }
        //     } catch (Exception $e) {
        //         $returnArray["message"] = $e->getMessage();
        //         Mage::log($e, null, "mobikul.log");
        //         $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
        //         return;
        //     }
        // }

        public function featuredProductListAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["productList"]  = array();
            $returnArray["totalCount"]   = 0;
            $returnArray["layeredData"]  = array();
            $returnArray["sortingData"]  = array();
            $returnArray["cartCount"]    = 0;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId    = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $width      = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $quoteId    = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $pageNumber = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $sortData   = isset($wholeData["sortData"])   ? $wholeData["sortData"]   : "[]";
                        $filterData = isset($wholeData["filterData"]) ? $wholeData["filterData"] : "[]";
                        $sortData   = Mage::helper("core")->jsonDecode($sortData);
                        $filterData = Mage::helper("core")->jsonDecode($filterData);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $featuredProductList = array();
                        if(Mage::getStoreConfig("mobikul/basic/featuredproduct", $storeId) == 1)    {
                            $productCollection = Mage::getResourceModel("catalog/product_collection")->addAttributeToSelect("*");
                            Mage::getModel("catalog/layer")->prepareProductCollection($productCollection);
                            $productCollection->getSelect()->order("rand()");
                        }
                        else{
                            $productCollection = Mage::getModel("catalog/product")->getCollection()
                                ->setStore($storeId)
                                ->addAttributeToSelect("*")
                                ->addAttributeToSelect("as_featured")
                                ->addAttributeToSelect("visibility")
                                ->addStoreFilter()
                                ->addAttributeToFilter("visibility", array("in" => array(2, 3, 4)))
                                ->addAttributeToFilter("as_featured", 1)
                                ->addAttributeToFilter("status", 1);
                        }
                        Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($productCollection);
                        if (Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 0) {
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
                            Mage::getSingleton("catalog/product_status")->addSaleableFilterToCollection($productCollection);
                        }
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($filterData) > 0) {
                            for ($i=0; $i<count($filterData[0]); $i++) {
                                if ($filterData[0][$i] != "") {
                                    $attribute = Mage::getModel("eav/entity_attribute")->loadByCode("catalog_product", $filterData[1][$i]);
                                    $attributeModel = Mage::getSingleton("catalog/layer_filter_attribute");
                                    $attributeModel->setAttributeModel($attribute);
                                    $attribute  = $attributeModel->getAttributeModel();
                                    $connection = Mage::getSingleton("core/resource")->getConnection("core_read");
                                    $tableAlias = $attribute->getAttributeCode() . "_idx";
                                    $conditions = array(
                                        "{$tableAlias}.entity_id = e.entity_id",
                                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                                        $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
                                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i])
                                    );
                                    $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_index_eav');
                                    $productCollection->getSelect()->join(
                                        array($tableAlias => $tableName),
                                        implode(" AND ", $conditions),
                                        array()
                                    );
                                }
                            }
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
                            if($eachProduct->isAvailable())
                                $featuredProductList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $featuredProductList;
                        $filters = Mage::getModel("catalog/layer")->getFilterableAttributes();
                        foreach ($filters as $filter) {
                            $doAttribute = 1;
                            if (count($filterData) > 0) {
                                if (in_array($filter->getAttributeCode(), $filterData[1]))
                                    $doAttribute = 0;
                            }
                            if ($doAttribute == 1) {
                                $attributeFilterModel = Mage::getModel("catalog/layer_filter_attribute")->setAttributeModel($filter);
                                if ($attributeFilterModel->getItemsCount()) {
                                    $each = array();
                                    $each["label"]    = $filter->getFrontendLabel();
                                    $each["code"]     = $filter->getAttributeCode();
                                    $attributeOptions = $this->getCustomAttributeFilter($productCollection, $attributeFilterModel, $filter);
                                    $each["options"]  = $attributeOptions;
                                    if(count($attributeOptions) > 0)
                                        $layeredData[] =  $each;
                                }
                            }
                        }
                        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
                        if(Mage::getStoreConfig("mobikul/basic/featuredproduct", $storeId) != 1) {
                            foreach ($toolbar->getAvailableOrders() as $key => $order) {
                                $each = array();
                                $each["code"]  = $key;
                                $each["label"] = $order;
                                $sortingData[] = $each;
                            }
                        }
                        $returnArray["layeredData"] = $layeredData;
                        $returnArray["sortingData"] = $sortingData;
                        if ($customerId != 0) {
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId)->getItemsQty() * 1;
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function getCustomAttributeFilter($collection, $attributeFilterModel, $_filter){
            $options = $_filter->getFrontend()->getSelectOptions();
            $select = clone $collection->getSelect();
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
            $connection = Mage::getSingleton("core/resource")->getConnection("core_read");
            $attribute  = $attributeFilterModel->getAttributeModel();
            $tableAlias = sprintf("%s_idx", $attribute->getAttributeCode());
            $conditions = array(
                "{$tableAlias}.entity_id = e.entity_id",
                $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                $connection->quoteInto("{$tableAlias}.store_id = ?", $attributeFilterModel->getStoreId()),
            );
            $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_index_eav');
            $select
                ->join(
                    array($tableAlias => $tableName),
                    join(" AND ", $conditions),
                    array("value", "count" => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")))
                ->group("{$tableAlias}.value");
            $optionsCount = $connection->fetchPairs($select);
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

        public function newProductListAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["productList"]  = array();
            $returnArray["totalCount"]   = 0;
            $returnArray["layeredData"]  = array();
            $returnArray["sortingData"]  = array();
            $returnArray["cartCount"]    = 0;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $width        = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $pageNumber   = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $sortData     = isset($wholeData["sortData"])   ? $wholeData["sortData"]   : "[]";
                        $filterData   = isset($wholeData["filterData"]) ? $wholeData["filterData"] : "[]";
                        $sortData     = Mage::helper("core")->jsonDecode($sortData);
                        $filterData   = Mage::helper("core")->jsonDecode($filterData);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $newProductList         = array();
                        $todayStartOfDayDate    = Mage::app()->getLocale()->date()->setTime("00:00:00")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $todayEndOfDayDate      = Mage::app()->getLocale()->date()->setTime("23:59:59")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $productCollection      = Mage::getResourceModel("catalog/product_collection")
                            ->setVisibility(Mage::getSingleton("catalog/product_visibility")->getVisibleInCatalogIds())
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addTaxPercents()
                            ->addAttributeToSelect("*");
                        $productCollection->addStoreFilter()
                            ->addAttributeToFilter("news_from_date", array("or"=> array(
                                0 => array("date" => true, "to" => $todayEndOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter("news_to_date", array("or"=> array(
                                0 => array("date" => true, "from" => $todayStartOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter(
                                array(
                                    array("attribute" => "news_from_date", "is"=>new Zend_Db_Expr("not null")),
                                    array("attribute" => "news_to_date", "is"=>new Zend_Db_Expr("not null"))
                                )
                            );
                        Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($productCollection);
                        if (Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 0) {
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
                            Mage::getSingleton("catalog/product_status")->addSaleableFilterToCollection($productCollection);
                        }
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($filterData) > 0) {
                            for ($i=0; $i<count($filterData[0]); $i++) {
                                if ($filterData[0][$i] != "") {
                                    $attribute = Mage::getModel("eav/entity_attribute")->loadByCode("catalog_product", $filterData[1][$i]);
                                    $attributeModel = Mage::getSingleton("catalog/layer_filter_attribute");
                                    $attributeModel->setAttributeModel($attribute);
                                    $attribute  = $attributeModel->getAttributeModel();
                                    $connection = Mage::getSingleton("core/resource")->getConnection("core_read");
                                    $tableAlias = $attribute->getAttributeCode() . "_idx";
                                    $conditions = array(
                                        "{$tableAlias}.entity_id = e.entity_id",
                                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                                        $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
                                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i])
                                    );
                                    $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_index_eav');
                                    $productCollection->getSelect()->join(
                                        array($tableAlias => $tableName),
                                        implode(" AND ", $conditions),
                                        array()
                                    );
                                }
                            }
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        else{
                            $productCollection->addAttributeToSort("news_from_date", "DESC");
                        }
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $newProductList = array();
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
                                $newProductList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $newProductList;
                        $filters = Mage::getModel("catalog/layer")->getFilterableAttributes();
                        foreach ($filters as $filter) {
                            $doAttribute = 1;
                            if (count($filterData) > 0) {
                                if (in_array($filter->getAttributeCode(), $filterData[1]))
                                    $doAttribute = 0;
                            }
                            if ($doAttribute == 1) {
                                $attributeFilterModel = Mage::getModel("catalog/layer_filter_attribute")->setAttributeModel($filter);
                                if ($attributeFilterModel->getItemsCount()) {
                                    $each = array();
                                    $each["label"]    = $filter->getFrontendLabel();
                                    $each["code"]     = $filter->getAttributeCode();
                                    $attributeOptions = $this->getCustomAttributeFilter($productCollection, $attributeFilterModel, $filter);
                                    $each["options"]  = $attributeOptions;
                                    if(count($attributeOptions) > 0)
                                        $layeredData[] =  $each;
                                }
                            }
                        }
                        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
                        foreach ($toolbar->getAvailableOrders() as $key => $order) {
                            $each = array();
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["layeredData"] = $layeredData;
                        $returnArray["sortingData"] = $sortingData;
                        if ($customerId != 0) {
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId)->getItemsQty() * 1;
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function hotDealListAction()   {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["productList"]  = array();
            $returnArray["totalCount"]   = 0;
            $returnArray["layeredData"]  = array();
            $returnArray["sortingData"]  = array();
            $returnArray["cartCount"]    = 0;
            $this->getResponse()->setHeader("Content-type", "application/json");
            try {
                $wholeData = $this->getRequest()->getPost();
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = Mage::helper("mobikul")->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId      = isset($wholeData["storeId"])    ? $wholeData["storeId"]    : 1;
                        $width        = isset($wholeData["width"])      ? $wholeData["width"]      : 1000;
                        $customerId   = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $quoteId      = isset($wholeData["quoteId"])    ? $wholeData["quoteId"]    : 0;
                        $pageNumber   = isset($wholeData["pageNumber"]) ? $wholeData["pageNumber"] : 1;
                        $sortData     = isset($wholeData["sortData"])   ? $wholeData["sortData"]   : "[]";
                        $filterData   = isset($wholeData["filterData"]) ? $wholeData["filterData"] : "[]";
                        $sortData     = Mage::helper("core")->jsonDecode($sortData);
                        $filterData   = Mage::helper("core")->jsonDecode($filterData);
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $hotDealProductList     = array();
                        $todayStartOfDayDate    = Mage::app()->getLocale()->date()->setTime("00:00:00")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $todayEndOfDayDate      = Mage::app()->getLocale()->date()->setTime("23:59:59")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                        $productCollection      = Mage::getResourceModel("catalog/product_collection")
                            ->setVisibility(Mage::getSingleton("catalog/product_visibility")->getVisibleInCatalogIds())
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addTaxPercents()
                            ->addAttributeToSelect("price")
                            ->addAttributeToSelect("special_price")
                            ->addAttributeToSelect("special_from_date")
                            ->addAttributeToSelect("special_to_date")
                            ->addAttributeToSelect("*");
                        $productCollection->addStoreFilter()
                            ->addAttributeToFilter("special_from_date", array("or"=> array(
                                0 => array("date" => true, "to" => $todayEndOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter("special_to_date", array("or"=> array(
                                0 => array("date" => true, "from" => $todayStartOfDayDate),
                                1 => array("is" => new Zend_Db_Expr("null")))
                            ), "left")
                            ->addAttributeToFilter(
                                array(
                                    array("attribute" => "special_from_date", "is"=>new Zend_Db_Expr("not null")),
                                    array("attribute" => "special_to_date", "is"=>new Zend_Db_Expr("not null"))
                                )
                            )
                            ->addAttributeToFilter("special_price", array('gteq' => 0))
                            ;
                            // $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_decimal');
                            // $hotDealCollection->getSelect()->join( array('special_attr'=> $tableName), 'special_attr.entity_id = e.entity_id', array('special_attr.*'));
                            // $hotDealCollection->getSelect()->where('special_attr.value < price_index.price');
                        Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($productCollection);
                        if (Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 0) {
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
                            Mage::getSingleton("catalog/product_status")->addSaleableFilterToCollection($productCollection);
                        }
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($filterData) > 0) {
                            for ($i=0; $i<count($filterData[0]); $i++) {
                                if ($filterData[0][$i] != "") {
                                    $attribute = Mage::getModel("eav/entity_attribute")->loadByCode("catalog_product", $filterData[1][$i]);
                                    $attributeModel = Mage::getSingleton("catalog/layer_filter_attribute");
                                    $attributeModel->setAttributeModel($attribute);
                                    $attribute  = $attributeModel->getAttributeModel();
                                    $connection = Mage::getSingleton("core/resource")->getConnection("core_read");
                                    $tableAlias = $attribute->getAttributeCode() . "_idx";
                                    $conditions = array(
                                        "{$tableAlias}.entity_id = e.entity_id",
                                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                                        $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
                                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i])
                                    );
                                    $tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_index_eav');
                                    $productCollection->getSelect()->join(
                                        array($tableAlias => $tableName),
                                        implode(" AND ", $conditions),
                                        array()
                                    );
                                }
                            }
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        if ($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $hotProductList = array();
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
                                $hotProductList[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $hotProductList;
                        $filters = Mage::getModel("catalog/layer")->getFilterableAttributes();
                        foreach ($filters as $filter) {
                            $doAttribute = 1;
                            if (count($filterData) > 0) {
                                if (in_array($filter->getAttributeCode(), $filterData[1]))
                                    $doAttribute = 0;
                            }
                            if ($doAttribute == 1) {
                                $attributeFilterModel = Mage::getModel("catalog/layer_filter_attribute")->setAttributeModel($filter);
                                if ($attributeFilterModel->getItemsCount()) {
                                    $each = array();
                                    $each["label"]    = $filter->getFrontendLabel();
                                    $each["code"]     = $filter->getAttributeCode();
                                    $attributeOptions = $this->getCustomAttributeFilter($productCollection, $attributeFilterModel, $filter);
                                    $each["options"]  = $attributeOptions;
                                    if(count($attributeOptions) > 0)
                                        $layeredData[] =  $each;
                                }
                            }
                        }
                        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
                        foreach ($toolbar->getAvailableOrders() as $key => $order) {
                            $each          = array();
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["layeredData"] = $layeredData;
                        $returnArray["sortingData"] = $sortingData;
                        if ($customerId != 0) {
                            $quoteCollection = Mage::getModel("sales/quote")->getCollection();
                            $quoteCollection->addFieldToFilter("customer_id", $customerId);
                            $quoteCollection->addFieldToFilter("is_active", 1);
                            $quoteCollection->addOrder("updated_at", "desc");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId)->getItemsQty() * 1;
                        }
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                        return;
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = Mage::helper("mobikul")->__("Invalid Request");
                    $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

    }
