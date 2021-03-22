<?php

    class Webkul_MobiKul_ExtraController extends Mage_Core_Controller_Front_Action    {

        public function registerDeviceAction()   {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
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
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $token      = isset($wholeData["token"])      ? $wholeData["token"]      : "";
                        Mage::helper("mobikul/token")->saveToken($customerId, $token);
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

        public function logOutAction()   {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
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
                        $token      = isset($wholeData["token"])      ? $wholeData["token"]      : "";
                        $customerId = isset($wholeData["customerId"]) ? $wholeData["customerId"] : 0;
                        $collection = Mage::getModel("mobikul/devicetoken")
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("token", $token);
                        foreach($collection as $eachToken)
                            Mage::getModel("mobikul/devicetoken")->load($eachToken->getId())->setCustomerId(0)->save();
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

        public function notificationListAction()     {
            $returnArray = array();
            $returnArray["authKey"]          = "";
            $returnArray["responseCode"]     = 0;
            $returnArray["message"]          = "";
            $returnArray["notificationList"] = array();
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
                        $width        = isset($wholeData["width"])   ? $wholeData["width"]   : 1000;
                        $mFactor      = isset($wholeData["mFactor"]) ? $wholeData["mFactor"] : 1;
                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        $notificationCollection = Mage::getModel("mobikul/notification")
                            ->getCollection()
                            ->addFieldToFilter("status", 1)
                            ->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
                            ->setOrder("update_time", "DESC");
                        $height   = ($width/2) * $mFactor;
                        $width    *= $mFactor;
                        foreach ($notificationCollection as $notification) {
                            $eachNotification = array();
                            $eachNotification["id"] = $notification->getId();
                            $eachNotification["content"] = $notification->getContent();
                            $eachNotification["notificationType"] = $notification->getType();
                            $eachNotification["title"] = $notification->getTitle();
                            $basePath = Mage::getBaseDir("media").DS.$notification->getFilename();
                            
                            if (is_file($basePath)) {
                                $newPath = Mage::getBaseDir("media").DS."mobikul".DS."resized".DS."notificationBanner".DS.$width."x".$height.DS.$notification->getFilename();
                                Mage::helper("mobikul")->resizeNCache($basePath, $newPath, $width, $height);
                                $eachNotification["banner"] = Mage::getBaseUrl("media")."mobikul".DS."resized".DS."notificationBanner".DS.$width."x".$height.DS.$notification->getFilename();
                                $eachNotification["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($basePath); 
                            }
                            else
                                $eachNotification["banner"] = "";
                            if ($notification->getType() == "category") {
// for category /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                $category = Mage::getModel("catalog/category")->load($notification->getProCatId());
                                $eachNotification["categoryName"] = $category->getName();
                                $eachNotification["categoryId"]   = $notification->getProCatId();
                            } elseif ($notification->getType() == "product") {
// for product //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                $product = Mage::getModel("catalog/product")->load($notification->getProCatId());
                                $eachNotification["productName"] = $product->getName();
                                $eachNotification["productType"] = $product->getTypeId();
                                $eachNotification["productId"]   = $notification->getProCatId();
                            }
                            $returnArray["notificationList"][]   = $eachNotification;
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function searchTermListAction()     {
            $returnArray = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["termList"]     = array();
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
                        $termBlock = new Mage_CatalogSearch_Block_Term();
                        if (sizeof($termBlock->getTerms()) > 0) {
                            foreach ($termBlock->getTerms() as $term) {
                                $eachTerm                  = array();
                                $eachTerm["ratio"]         = $term->getRatio() * 70 + 75;
                                $eachTerm["term"]          = Mage::helper("core")->stripTags($term->getName());
                                $returnArray["termList"][] = $eachTerm;
                            }
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function customCollectionAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["cartCount"]    = 0;
            $returnArray["totalCount"]   = 0;
            $returnArray["productList"]  = array();
            $returnArray["responseCode"] = 0;
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
                        
                        $width            = isset($wholeData["width"])          ? $wholeData["width"]          : 1000;
                        $storeId          = isset($wholeData["storeId"])        ? $wholeData["storeId"]        : 1;
                        $quoteId          = isset($wholeData["quoteId"])        ? $wholeData["quoteId"]        : 0;
                        $sortData         = isset($wholeData["sortData"])       ? $wholeData["sortData"]       : "[]";
                        $filterData       = isset($wholeData["filterData"])     ? $wholeData["filterData"]     : "[]";
                        $pageNumber       = isset($wholeData["pageNumber"])     ? $wholeData["pageNumber"]     : 1;
                        $customerId       = isset($wholeData["customerId"])     ? $wholeData["customerId"]     : 0;
                        $notificationId   = isset($wholeData["notificationId"]) ? $wholeData["notificationId"] : 0;
                        $sortData         = Mage::helper("core")->jsonDecode($sortData);
                        $filterData       = Mage::helper("core")->jsonDecode($filterData);
                        $notification     = Mage::getModel("mobikul/notification")->load($notificationId);
                        $customFilterData = unserialize($notification->getFilterData());
                        $attributes       = Mage::getSingleton("catalog/config")->getProductAttributes();
                        if ($notification->getCollectionType() == "product_attribute") {
                            $productCollection = Mage::getModel("catalog/product")->getCollection()->addAttributeToSelect($attributes);
                            Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($productCollection);
                            Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($productCollection);
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
                            foreach ($customFilterData as $key => $filterValue) {
                                if ($key == "category_ids") {
                                    foreach (explode(",", $filterValue) as $value)
                                        $productCollection->addCategoryFilter(Mage::getModel("catalog/category")->load($value));
                                } else {
                                    $productCollection->addAttributeToSelect($key);
                                    $productCollection->addAttributeToFilter($key, array("in" => $filterValue));
                                }
                            }
                        } elseif ($notification->getCollectionType() == "product_ids") {    
                            $productCollection = Mage::getModel("catalog/product")->getCollection()->addAttributeToSelect($attributes);
                            Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($productCollection);
                            Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($productCollection);
                            Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
                            $productCollection->addAttributeToFilter("entity_id", array("in" => explode(",", $customFilterData)));
                        } elseif ($notification->getCollectionType() == "product_new") {
                            $todayStartOfDayDate = Mage::app()->getLocale()->date()->setTime("00:00:00")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                            $todayEndOfDayDate = Mage::app()->getLocale()->date()->setTime("23:59:59")->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                            $productCollection = Mage::getResourceModel("catalog/product_collection")
                                ->setVisibility(Mage::getSingleton("catalog/product_visibility")->getVisibleInCatalogIds())
                                ->addMinimalPrice()
                                ->addFinalPrice()
                                ->addTaxPercents()
                                ->addAttributeToSelect(Mage::getSingleton("catalog/config")->getProductAttributes());
                            $productCollection->addStoreFilter()
                                ->addAttributeToFilter("news_from_date", array("or" => array(
                                    0 => array("date" => true, "to" => $todayEndOfDayDate),
                                    1 => array("is" => new Zend_Db_Expr("null")))
                                ), "left")
                                ->addAttributeToFilter("news_to_date", array("or" => array(
                                    0 => array("date" => true, "from" => $todayStartOfDayDate),
                                    1 => array("is" => new Zend_Db_Expr("null")))
                                ), "left")
                                ->addAttributeToFilter(
                                    array(
                                        array("attribute" => "news_from_date", "is" =>new Zend_Db_Expr("not null")),
                                        array("attribute" => "news_to_date", "is" =>new Zend_Db_Expr("not null"))
                                    )
                                )
                            ->addAttributeToSort("news_from_date", "desc");
                            $returnArray["totalCount"] = $customFilterData;
                            if ($pageNumber >= 1)
                                $productCollection->setPageSize($customFilterData)->setCurPage($pageNumber);
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
                        if ($notification->getCollectionType() != "product_new" && $pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = Mage::getStoreConfig("mobikul/configuration/pagesize");
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if (count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if ($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        foreach ($productCollection as $product) {
                            $product = Mage::getModel("catalog/product")->load($product->getId());
                            if($product->isAvailable())
                                $returnArray["productList"][] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($product, $storeId, $width, $customerId);
                        }
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
                        $returnArray["layeredData"] = $layeredData;
                        $toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
                        foreach ($toolbar->getAvailableOrders() as $key => $order) {
                            $each          = array();
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
                        if ($customerId != 0) {
                            $quote = Mage::getModel("sales/quote")->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addOrder("updated_at", "desc")
                                ->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId)->getItemsQty() * 1;
                        }
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
            }
            catch (Exception $e) {
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

        public function otherNotificationDataAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["title"]        = "";
            $returnArray["content"]      = "";
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
                        $notificationId = isset($wholeData["notificationId"]) ? $wholeData["notificationId"] : 0;
                        $notification   = Mage::getModel("mobikul/notification")->load($notificationId);
                        $returnArray["title"]   = $notification->getTitle();
                        $returnArray["content"] = $notification->getContent();
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function cmsDataAction()     {
            $returnArray                 = array();
            $returnArray["authKey"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["message"]      = "";
            $returnArray["title"]        = "";
            $returnArray["content"]      = "";
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
                        $id = isset($wholeData["id"]) ? $wholeData["id"] : 0;
                        $cmsPage   = Mage::getModel("cms/page")->load($id);
                        $helper    = Mage::helper("cms");
                        $processor = $helper->getPageTemplateProcessor();
                        $returnArray["content"] = $processor->filter($cmsPage->getContent());
                        $returnArray["title"]   = $cmsPage->getTitle();
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
            }
            catch (Exception $e) {
                $returnArray["message"] = $e->getMessage();
                Mage::log($e, null, "mobikul.log");
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            }
        }

        public function searchSuggestionAction()   {
            $returnArray                        = array();
            $returnArray["authKey"]             = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["message"]             = "";
            $returnArray["suggestProductArray"] = array();
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
                        $searchQuery    = isset($wholeData["searchQuery"]) ? $wholeData["searchQuery"] : "";
                        $storeId        = isset($wholeData["storeId"]) ? $wholeData["storeId"] : 1;
                        $categoryId     = isset($wholeData["categoryId"])  ? $wholeData["categoryId"]  : 0;

                        $appEmulation = Mage::getSingleton("core/app_emulation");
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                        
                        $helper         = Mage::helper("mobikul/searchsuggestion");
                        $stringHelper   = Mage::helper("core/string");
                        $query          = is_array($searchQuery) ? "" : $stringHelper->cleanString(trim($searchQuery));
                        $maxQueryLength = Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_LENGTH);
                        $query          = $stringHelper->substr($searchQuery, 0, $maxQueryLength);
                        
                        if ($helper->displayTags())  {
                            $tagCollection   = Mage::getModel("catalogsearch/query")->getCollection()
                                ->addFieldToFilter("store_id", array(array("finset"=>array($storeId))))
                                ->setPopularQueryFilter($storeId)
                                ->addFieldToFilter("query_text", array("like"=>"%".$query."%"))
                                ->setPageSize($helper->getNumberOfTags())
                                ->load()
                                ->getItems();
                                
                            foreach ($tagCollection as $item)  {
                                if ($item->getQueryText() )
                                    $tagArray[] = array(
                                        "title"          => $item->getQueryText(),
                                        "count"          => $item->getNumResults(),
                                        "term"           => $query
                                    );
                            }
                        }

                        if ($helper->displayProducts()) {
                            if ($categoryId > 0) {
                                $productStatuses = Mage::getModel("catalog/product_status");
                                $productCollection = Mage::getModel("catalog/category")->load($categoryId)
                                    ->getProductCollection()
                                    ->addAttributeToSelect("*")
                                    ->addAttributeToFilter("status", array('in' => $productStatuses->getVisibleStatusIds()))
                                    ->addAttributeToFilter("visibility", array("in"=> array(2,3,4)))
                                    ->addAttributeToFilter(
                                                            array(
                                                                array(
                                                                    "attribute"=>"name",
                                                                    "like"=>"%".$query."%")
                                                                ),
                                                                array(
                                                                    "attribute"=>"sku",
                                                                    "like"=>"%".$query."%"
                                                                ),
                                                                array(
                                                                    "attribute"=>"description",
                                                                    "like"=>"%".$query."%"
                                                                )
                                                            );

                            } else {
                                $productStatuses = Mage::getModel("catalog/product_status");
                                $productCollection = Mage::getModel("catalog/product")
                                    ->getCollection()
                                    ->addAttributeToSelect("sku")
                                    ->addAttributeToSelect("price")
                                    ->addAttributeToSelect("name")
                                    ->addAttributeToSelect("small_image")
                                    ->addAttributeToSelect("description")
                                    ->addAttributeToSelect("special_from_date")
                                    ->addAttributeToSelect("special_to_date")
                                    ->addAttributeToSelect("short_description")
                                    ->addAttributeToFilter("status",
                                                             array(
                                                                 'in' => $productStatuses->getVisibleStatusIds()
                                                                 )
                                                            )
                                    ->addAttributeToFilter(
                                        "visibility",
                                        array(
                                            "in"=> array(
                                                    2,3,4
                                                    )
                                                )
                                            )
                                    ->addAttributeToFilter(
                                                        array(
                                                            array(
                                                                "attribute"=>"name",
                                                                "like"=>"%".$query."%")
                                                            ),
                                                            array(
                                                                "attribute"=>"sku",
                                                                "like"=>"%".$query."%"
                                                            ),
                                                                array(
                                                                    "attribute"=>"description",
                                                                    "like"=>"%".$query."%"
                                                                )
                                                        );
                            }
                            
                            $tableName = Mage::getSingleton('core/resource')->getTableName('review_entity_summary');
                            $productCollection->joinField("reviews_count", "{$tableName}", "reviews_count", "entity_pk_value=entity_id", array("entity_type"=>1, "store_id"=>Mage::app()->getStore()->getId()), "left")->setOrder("reviews_count", "desc");
                            if (!Mage::getStoreConfig("cataloginventory/options/show_out_of_stock")) {
                                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
                            }
                            $productCollection->setPageSize(20);
                            $isIncludeTaxInPrice = false;
                            if (Mage::getStoreConfig("tax/display/type", $storeId) == 2)
                                $isIncludeTaxInPrice = true;
                            $count = 0;
                            foreach ($productCollection as $item) {
                                $price       = $item->getPrice();
                                if ($isIncludeTaxInPrice) {
                                    $price       = Mage::helper("tax")->getPrice($item, $item->getPrice());
                                }
                                $isSalePrice = $helper->isOnSale($item);
                                $imageData = Mage::helper("mobikul/image")->init($item, "small_image")->resize(200, 200)->__toString();

                                $imgSrc      = $imageData[0];
                                $dominantColor      = $imageData[1];
                                if ($isSalePrice == true) {
                                    $specialPrice = $item->getSpecialPrice();
                                    if ($isIncludeTaxInPrice) {
                                        $specialPrice       = Mage::helper("tax")->getPrice($item, $item->getSpecialPrice());
                                    }
                                }
                                else
                                    $specialPrice = 0;
                                if ($item->getTypeId() == "grouped")  {
                                    $minPrice = 0;
                                    if($item->getMinimalPrice() == "")  {
                                        $associatedProducts = $item->getTypeInstance(true)->getAssociatedProducts($item);
                                        $minPriceArr = [];
                                        foreach ($associatedProducts as $associatedProduct)  {
                                            if ($ogPrice = $associatedProduct->getPrice())
                                                $minPriceArr[] = $ogPrice;
                                        }
                                        
                                        if (!empty($minPriceArr))
                                            $minPrice = min($minPriceArr);
                                            $price = $minPrice;
                                    } else  {
                                        $minPrice = $item->getMinimalPrice();
                                        $price = $minPrice;
                                    }
                                }   
                                if ($item->isAvailable() || Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 1) {
                                    $productArray[] = array(
                                        "title"        => $item->getName(),
                                        "image"        => $imgSrc,
                                        "dominantColor"=> $dominantColor,
                                        "productId"    => $item->getId(),
                                        "price"        => $price,
                                        "specialPrice" => $specialPrice,
                                        "term"         => $query
                                    );
                                    $count++;
                                }
                                if ($count >= 5)
                                break;
                            }
                        }
                        $suggestData = array($tagArray, $productArray);
                        $suggestProductArray = array();
                        if (count($suggestData[0]) != 0 || count($suggestData[1]) != 0) {
                            foreach ($suggestData[0] as $index => $item) {
                                $eachSuggestion = array();
                                $term    = Mage::helper("core")->escapeHtml($item["term"]);
                                $tagName = Mage::helper("core")->escapeHtml($item["title"]);
                                $title   = Mage::helper("core")->escapeHtml($item["title"]);
                                $len     = strlen($term);
                                $str     = $helper->matchString($term, $tagName);
                                $tagName = $helper->getBoldName($tagName, $str, $term);
                                $eachSuggestion["label"] = $tagName;
                                $eachSuggestion["count"] = $item["count"];
                                $suggestProductArray["tags"][] = $eachSuggestion;
                            }
                            if (count($suggestData[1]) > 0) {
                                foreach ($suggestData[1] as $index => $item) {
                                    $eachSuggestion = array();
                                    $term           = Mage::helper("core")->escapeHtml($item["term"]);
                                    $formattedPrice = Mage::helper("core")->currency(Mage::helper("core")->escapeHtml($item["price"]), true, false);
                                    $imgUrl         = Mage::helper("core")->escapeHtml($item["image"]);
                                    $productName    = Mage::helper("core")->escapeHtml($item["title"]);
                                    $specialPrice   = Mage::helper("core")->escapeHtml($item["specialPrice"]);
                                    $title          = Mage::helper("core")->escapeHtml($item["title"]);
                                    $str            = $helper->matchString($term, $productName);
                                    $productName    = $helper->getBoldName($productName, $str, $term);
                                    $eachSuggestion["productName"]     = $productName;
                                    $eachSuggestion["thumbNail"]       = $imgUrl;
                                    $eachSuggestion["dominantColor"] = Mage::helper("mobikul/catalog")->getDominantColor($imgUrl);
                                    $eachSuggestion["productId"]       = $item["productId"];
                                    $eachSuggestion["price"]           = $formattedPrice;
                                    $eachSuggestion["hasSpecialPrice"] = false;
                                    $eachSuggestion["specialPrice"]    = Mage::helper("core")->currency(0, true, false);
                                    if ($specialPrice > 0) {
                                        $eachSuggestion["hasSpecialPrice"] = true;
                                        $eachSuggestion["specialPrice"]    = Mage::helper("core")->currency($specialPrice, true, false);
                                    }
                                    $suggestProductArray["products"][] = $eachSuggestion;
                                }
                            }
                        }
                        $returnArray["suggestProductArray"] = $suggestProductArray;$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
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

    }