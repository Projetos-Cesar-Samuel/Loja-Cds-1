<?php

	class Webkul_MobiKul_Model_Catalog_Api extends Mage_Api_Model_Resource_Abstract    {

		public function getcategoryList($data)    {
			$data = json_decode($data);
			$storeId == "";
			if(isset($data->storeId))
				$storeId = $data->storeId;
			$websiteId = $data->websiteId;
			if($storeId == "")
				$storeId = Mage::app()->getWebsite($websiteId)->getDefaultGroup()->getDefaultStoreId();
			$width = $data->width;
			$returnArray = array();$categories = array();$bannerImages = array();$featuredCategories = array();$featuredProducts = array();$newProducts = array();$storeData = array();$limit = 0;
			$appEmulation = Mage::getSingleton("core/app_emulation");
			$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
			$returnArray["storeId"] = $storeId;
			$returnArray["versionCode"] = 9;

			//getting category tree
			$categories = Mage::getModel("mobikul/category_api")->tree(null, $storeId);
			$returnArray["categories"] = $categories;

			//collecting banner images
			$collection = Mage::getModel("mobikul/bannerimage")->getCollection()->addFieldToFilter("status",1)
							->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
							->setOrder("sort_order","ASC");
			$height = $width/2;
			foreach($collection as $eachBanner) {
				$oneBanner = array();
				$new_url = "";
				$base_path = Mage::getBaseDir("media").DS.$eachBanner->getFilename();
				if(file_exists($base_path)){
					$new_path = Mage::getBaseDir("media").DS."mobikulresized".DS.$width."x".$height.DS.$eachBanner->getFilename();
					$new_url = Mage::getBaseUrl("media")."mobikulresized".DS.$width."x".$height.DS.$eachBanner->getFilename();
					if(!file_exists($new_path)) {
						$imageObj = new Varien_Image($base_path);
						$imageObj->keepAspectRatio(false);
						$imageObj->backgroundColor(array(255,255,255));
						$imageObj->keepFrame(false);
						$imageObj->resize($width,$height);
						$imageObj->save($new_path);
					}
				}
				$oneBanner["url"] = $new_url;
				$oneBanner["bannerType"] = $eachBanner->getType();
				if($eachBanner->getType() == "category"){  //for category
					if(Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId))
						$oneBanner["error"] = 0;
					else
						$oneBanner["error"] = 1;
					$oneBanner["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId);
					$oneBanner["categoryId"] = $eachBanner->getProCatId();
				}
				else
				if($eachBanner->getType() == "product"){  //for product
					if(Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId))
						$oneBanner["error"] = 0;
					else
						$oneBanner["error"] = 1;
					$oneBanner["productName"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId);
					$oneBanner["productType"] = Mage::getResourceSingleton("catalog/product")->getAttributeRawValue($eachBanner->getProCatId(), "type_id", $storeId);
					$oneBanner["productId"] = $eachBanner->getProCatId();
				}
				$bannerImages[] = $oneBanner;
			}
			$returnArray["bannerImages"] = $bannerImages;

			// collecting featured categories
			$featuredCategoryCollection = Mage::getModel("mobikul/featuredcategories")->getCollection()->addFieldToFilter("status", 1)
							->addFieldToFilter("store_id", array(array("finset" => array($storeId))))
							->setOrder("sort_order", "ASC");
			if($featuredCategoryCollection->getSize() == 3)
				$limit = 3;
			else
			if($featuredCategoryCollection->getSize() > 3 && $featuredCategoryCollection->getSize() < 6)
				$limit = 3;
			else
			if($featuredCategoryCollection->getSize() >= 6)
				$limit = 6;
			$featuredCategoryCollection->getSelect()->order("rand()")->limit($limit);
			if($limit > 0){
				$FCwidth = $width/3.3;
				$FCheight = (2*$width)/3.3;
				foreach($featuredCategoryCollection as $eachCategory) {
					$oneCategory = array();
					$new_url = "";
					$base_path = Mage::getBaseDir("media").DS.$eachCategory->getFilename();
					if(file_exists($base_path)){
						$new_path = Mage::getBaseDir("media").DS."mobikulresized".DS.$FCwidth."x".$FCheight.DS.$eachCategory->getFilename();
						$new_url = Mage::getBaseUrl("media")."mobikulresized".DS.$FCwidth."x".$FCheight.DS.$eachCategory->getFilename();
						if(!file_exists($new_path)) {
							$imageObj = new Varien_Image($base_path);
							$imageObj->keepAspectRatio(false);
							$imageObj->backgroundColor(array(255,255,255));
							$imageObj->keepFrame(false);
							$imageObj->resize($FCwidth, $FCheight);
							$imageObj->save($new_path);
						}
					}
					$oneCategory["url"] = $new_url;
					$oneCategory["categoryName"] = Mage::getResourceSingleton("catalog/category")->getAttributeRawValue($eachCategory->getCategoryId(), "name", $storeId);
					$oneCategory["categoryId"] = $eachCategory->getCategoryId();
					$featuredCategories[] = $oneCategory;
				}
			}
			$returnArray["featuredCategories"] = $featuredCategories;

			//collecting featured products
			$featuredProductCollection = Mage::getResourceModel("catalog/product_collection");
			Mage::getModel("catalog/layer")->prepareProductCollection($featuredProductCollection);
			$featuredProductCollection->getSelect()->order("rand()");
			$featuredProductCollection->addStoreFilter()->setPage(1, 5)->load();
			Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($featuredProductCollection);
			Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($featuredProductCollection);
			Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($featuredProductCollection);
			foreach($featuredProductCollection as $eachProduct){
				$eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
				$featuredProducts[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width);
			}
			$returnArray["featuredProducts"] = $featuredProducts;

			//collecting new products
			$newProductCollection = Mage::getResourceModel("catalog/product_collection")
				->addAttributeToSelect(Mage::getSingleton("catalog/config")->getProductAttributes());
			Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($newProductCollection);
			Mage::getSingleton("catalog/product_visibility")->addVisibleInCatalogFilterToCollection($newProductCollection);
			Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($newProductCollection);
			$newProductCollection->setOrder("entity_id","DESC");
			$ccn = 0;
			foreach($newProductCollection as $eachProduct){
				if($ccn > 4)
					break;
				$eachProduct = Mage::getModel("catalog/product")->load($eachProduct->getId());
				if($eachProduct->isAvailable()){
					$newProducts[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($eachProduct, $storeId, $width);
					$ccn++;
				}
			}
			$returnArray["newProducts"] = $newProducts;

			//getting website data			
			$returnArray["storeData"] = Mage::helper("mobikul/catalog")->getStoreData();
			if(isset($data->customerId) && $data->customerId != ""){
				$returnArray["cartCount"] = Mage::getModel("sales/quote")->setStoreId($storeId)->loadByCustomer($data->customerId)->getItemsQty()*1;
				if(isset($data->width))
					$width = $data->width;
				else
					$width = 1000;
				$height = $width/2;
				$collection = Mage::getModel("mobikul/userimage")->getCollection()->addFieldToFilter("customer_id", $data->customerId);
				$returnArray["customerBannerImage"] = "";
				$returnArray["customerProfileImage"] = "";
				if($collection->getSize() > 0){
					foreach($collection as $value) {
						if($value->getBanner() != ""){
							$base_path = Mage::getBaseDir("media").DS."customerpicture".DS.$data->customerId.DS.$value->getBanner();
							$new_url = "";
							if(file_exists($base_path)){
								$new_path = Mage::getBaseDir("media").DS."customerpicture".DS.$data->customerId.DS.$width."x".$height.DS.$value->getBanner();
								$new_url = Mage::getBaseUrl("media")."customerpicture".DS.$data->customerId.DS.$width."x".$height.DS.$value->getBanner();
								if(!file_exists($new_path)) {
									$imageObj = new Varien_Image($base_path);
									$imageObj->keepAspectRatio(false);
									$imageObj->backgroundColor(array(255,255,255));
									$imageObj->keepFrame(false);
									$imageObj->resize($width, $height);
									$imageObj->save($new_path);
								}
							}
							$returnArray["customerBannerImage"] = $new_url;
						}
						if($value->getProfile() != ""){
							$base_path = Mage::getBaseDir("media").DS."customerpicture".DS.$data->customerId.DS.$value->getProfile();
							$new_url = "";
							if(file_exists($base_path)){
								$new_path = Mage::getBaseDir("media").DS."customerpicture".DS.$data->customerId.DS."100x100".DS.$value->getProfile();
								$new_url = Mage::getBaseUrl("media")."customerpicture".DS.$data->customerId.DS."100x100".DS.$value->getProfile();
								if(!file_exists($new_path)) {
									$imageObj = new Varien_Image($base_path);
									$imageObj->keepAspectRatio(false);
									$imageObj->backgroundColor(array(255,255,255));
									$imageObj->keepFrame(false);
									$imageObj->resize(70, 70);
									$imageObj->save($new_path);
								}
							}
							$returnArray["customerProfileImage"] = $new_url;
						}
					}
				}
			}

			$categoryImageCollection = Mage::getModel("mobikul/categoryimages")->getCollection();
			$categoryImages = array();
			foreach($categoryImageCollection as $categoryImage){
				if($categoryImage->getBanner() != "" && $categoryImage->getIcon() != ""){
					$eachCategoryImage["id"] = $categoryImage->getCategoryId();
					if($categoryImage->getBanner() != ""){
						$new_url = Mage::getBaseUrl("media")."categoryimages".DS.$width."x".$height.DS.$categoryImage->getBanner();
						$new_path = Mage::getBaseDir("media").DS."categoryimages".DS.$width."x".$height.DS.$categoryImage->getBanner();
						if(!file_exists($new_path)){
							$base_path = Mage::getBaseDir("media").DS.$categoryImage->getBanner();
							if(file_exists($base_path)){
								$imageObj = new Varien_Image($base_path);
								$imageObj->keepAspectRatio(false);
								$imageObj->backgroundColor(array(255,255,255));
								$imageObj->keepFrame(false);
								$imageObj->resize($width, $height);
								$imageObj->save($new_path);
							}
						}
						$eachCategoryImage["banner"] = $new_url;
					}
					if($categoryImage->getIcon() != ""){
						$new_url = Mage::getBaseUrl("media")."categoryimages".DS."48x48".DS.$categoryImage->getIcon();
						$new_path = Mage::getBaseDir("media").DS."categoryimages".DS."48x48".DS.$categoryImage->getIcon();
						if(!file_exists($new_path)){
							$base_path = Mage::getBaseDir("media").DS.$categoryImage->getIcon();
							if(file_exists($base_path)){
								$imageObj = new Varien_Image($base_path);
								$imageObj->keepAspectRatio(false);
								$imageObj->backgroundColor(array(255,255,255));
								$imageObj->keepFrame(false);
								$imageObj->resize(48, 48);
								$imageObj->save($new_path);
							}
						}
						$eachCategoryImage["thumbnail"] = $new_url;
					}
					$categoryImages[] = $eachCategoryImage;
				}
			}
			$returnArray["categoryImages"] = $categoryImages;

			if(isset($data->quoteId))
				$returnArray["cartCount"] = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($data->quoteId)->getItemsQty()*1;
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			return Mage::helper("core")->jsonEncode($returnArray);
		}

		public function getcategoryproductList($data)    {
			try{
				$data = json_decode($data);
				$categoryId = $data->categoryId;
				$storeId = $data->storeId;
				$width = $data->width;
				$sortData = $data->sortData;
				$filterData = $data->filterData;
				$returnArray = array();$categoryData = array();$layeredData = array();$sortingData = array();$stateData = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$category = Mage::getModel("catalog/category")->setStoreId($storeId)->load($categoryId);
				Mage::register("current_category",$category);
				$categoryBlock = new Mage_Catalog_Block_Product_List();
				$productCollection = $categoryBlock->getLoadedProductCollection();
				if(Mage::getStoreConfig("cataloginventory/options/show_out_of_stock") == 0){
					Mage::getSingleton("cataloginventory/stock")->addInStockFilterToCollection($productCollection);
					Mage::getSingleton("catalog/product_status")->addSaleableFilterToCollection($productCollection);
				}
				//filtering product collection
				if(count($filterData) > 0){
					for($i=0; $i<count($filterData[0]); $i++) {
						if($filterData[0][$i] != ""){
							if($filterData[1][$i] == "price"){
								$minPossiblePrice = .01;
								$currencyRate = $productCollection->getCurrencyRate();
								$priceRange = explode("-", $filterData[0][$i]);
								$from = $priceRange[0];
								$to = $priceRange[1];
								$fromRange = ($from - ($minPossiblePrice / 2)) / $currencyRate;
								$toRange = ($to - ($minPossiblePrice / 2)) / $currencyRate;
								$select = $productCollection->getSelect();
								if($from !== "")
									$select->where("price_index.min_price".">=".$fromRange);
								if($to !== "")
									$select->where("price_index.min_price"."<".$toRange);
							}
							else
							if($filterData[1][$i] == "cat"){
								$categoryToFilter = Mage::getModel("catalog/category")->load($filterData[0][$i]);
								$productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
							}
							else{
								$attribute = Mage::getModel("eav/entity_attribute")->loadByCode("catalog_product", $filterData[1][$i]);
								$attributeModel = Mage::getSingleton("catalog/layer_filter_attribute");
								$attributeModel->setAttributeModel($attribute);
								Mage::getResourceModel("catalog/layer_filter_attribute")->applyFilterToCollection($attributeModel, $filterData[0][$i]);
							}
						}
					}
				}
				//sorting product collection
				if(count($sortData) > 0){
					$sortBy = $sortData[0];
					if($sortData[1] == 0)
						$productCollection->setOrder($sortBy,"ASC");
					else
						$productCollection->setOrder($sortBy,"DESC");
				}
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $productCollection->getSize();
					$productCollection->setPageSize(16)->setCurPage($pageNumber);
				}
				//creating product collection data
				foreach($productCollection as $_product)
					$categoryData[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($_product, $storeId, $width);
				$doCategory = 1;
				if(count($filterData) > 0){
					if(in_array("cat", $filterData[1]))
						$doCategory = 0;
				}
				if($doCategory == 1)	{
					$categoryFilterModel = new Mage_Catalog_Model_Layer_Filter_Category();
					if($categoryFilterModel->getItemsCount()){
						$each = array();
						$each["label"] = "Category";
						$each["code"] = "cat";
						$key = $categoryFilterModel->getLayer()->getStateKey()."_SUBCATEGORIES";
						$data = $categoryFilterModel->getLayer()->getAggregator()->getCacheData($key);
						if($data === null) {
							$category = $categoryFilterModel->getCategory();
							$categories = $category->getChildrenCategories();
							$categoryFilterModel->getLayer()->getProductCollection()->addCountToCategories($categories);
							$data = array();
							foreach($categories as $category) {
								if($category->getIsActive() && $category->getProductCount()) {
									$data[] = array(
										"label" => str_replace("&amp;", "&", Mage::helper("core")->stripTags($category->getName())),
										"id" => $category->getId(),
										"count" => $category->getProductCount(),
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
				if(count($filterData) > 0){
					if(in_array("price", $filterData[1]))
						$doPrice = 0;
				}
				$_filters = Mage::getModel("catalog/layer")->getFilterableAttributes();
				foreach($_filters as $_filter){
					if($_filter->getFrontendInput() == "price"){
						if($doPrice == 1){
							$priceFilterModel = new Mage_Catalog_Model_Layer_Filter_Price();
							if($priceFilterModel->getItemsCount()){
								$each = array();
								$each["label"] = $_filter->getFrontendLabel();
								$each["code"] = $_filter->getAttributeCode();
								$priceOptions = Mage::helper("mobikul/catalog")->getPriceFilter($priceFilterModel, $storeId);
								$each["options"] = $priceOptions;
								$layeredData[] = $each;
							}
						}
					}
					else{
						$doAttribute = 1;
						if(count($filterData) > 0){
							if(in_array($_filter->getAttributeCode(), $filterData[1]))
								$doAttribute = 0;
						}
						if($doAttribute == 1){
							$attributeFilterModel = Mage::getModel("catalog/layer_filter_attribute")->setAttributeModel($_filter);
							if($attributeFilterModel->getItemsCount()){
								$each = array();
								$each["label"] = $_filter->getFrontendLabel();
								$each["code"] = $_filter->getAttributeCode();
								$attributeOptions = Mage::helper("mobikul/catalog")->getAttributeFilter($attributeFilterModel, $_filter);
								$each["options"] = $attributeOptions;
								$layeredData[] = $each;
							}
						}
					}
				}
				$toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
				foreach($toolbar->getAvailableOrders() as $_key => $_order){
					$each = array();
					$each["code"] = $_key;
					$each["label"] = $_order;
					$sortingData[] = $each;
				}
				$returnArray["categoryData"] = $categoryData;
				$returnArray["layeredData"] = $layeredData;
				$returnArray["sortingData"] = $sortingData;
				if(isset($data->customerId) && $data->customerId != "") 	{
					$returnArray["cartCount"] = Mage::getModel("sales/quote")->setStoreId($storeId)->loadByCustomer($data->customerId)->getItemsQty()*1;
				}
				//getting website data
				$returnArray["storeData"] = Mage::helper("mobikul/catalog")->getStoreData();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getcatalogsearchResult($data)    {
			try{
				$data = json_decode($data);
				$sortData = $data->sortData;
				$returnArray = array(); $productCollection = array(); $sortingData = array();
				$searchQuery = $data->searchQuery;
				$width = $data->width;
				$storeId = $data->storeId;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				//getting product collection
				$query = Mage::getModel("catalogsearch/query")->setQueryText($searchQuery)->prepare();
				$collection = Mage::getResourceModel("catalog/product_collection")->addAttributeToSelect("*");
				Mage::getResourceModel("catalogsearch/fulltext")->prepareResult(Mage::getModel("catalogsearch/fulltext"),$searchQuery,$query);
				$collection->getSelect()->joinInner(array("search_result" => $collection->getTable("catalogsearch/result")), $collection->getConnection()->quoteInto("search_result.product_id=e.entity_id AND search_result.query_id=?", $query->getId()),array("relevance" => "relevance"));
				$collection->setStore(Mage::getModel("core/store")->load($storeId));
				$collection->addMinimalPrice();
				$collection->addFinalPrice();
				$collection->addTaxPercents();
				$collection->addStoreFilter();
				$collection->addUrlRewrite();
				Mage::getSingleton("catalog/product_status")->addVisibleFilterToCollection($collection);
				Mage::getSingleton("catalog/product_visibility")->addVisibleInSearchFilterToCollection($collection);
				//sorting product collection
				if(count($sortData) > 0){
					$sortBy = $sortData[0];
					if($sortData[1] == 0)
						$collection->setOrder($sortBy,"ASC");
					else
						$collection->setOrder($sortBy,"DESC");
				}
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $collection->getSize();
					$collection->setPageSize(16)->setCurPage($pageNumber);
				}
				foreach($collection as $_product)
					$productCollection[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($_product, $storeId, $width);
				$returnArray["productCollection"] = $productCollection;
				//getting sorting collection
				$toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
				$availableOrders = $toolbar->getAvailableOrders();
				unset($availableOrders["position"]);
				$availableOrders = array_merge(array("relevance" => "Relevance"), $availableOrders);
				foreach($availableOrders as $_key => $_order){
					$each = array();
					$each["code"] = $_key;
					$each["label"] = $_order;
					$sortingData[] = $each;
				}
				$returnArray["sortingData"] = $sortingData;
				//getting website data
				$returnArray["storeData"] = Mage::helper("mobikul/catalog")->getStoreData();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getadvancedsearchFields($data)    {
			try{
				$data = json_decode($data);
				$returnArray = array();
				$storeId = $data->storeId;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$attributes = Mage::getSingleton("catalogsearch/advanced")->getAttributes();
				foreach($attributes as $_attribute){
					$each = array();
					$_code = $_attribute->getAttributeCode();
					$label = $_attribute->getStoreLabel();
					$each["label"] = $label;
					$each["inputType"] = Mage::helper("mobikul/catalog")->getAttributeInputType($_attribute);
					$each["attributeCode"] = $_code;
					$each["maxQueryLength"] = Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_LENGTH, $storeId);
					$each["title"] = Mage::helper("core")->stripTags($label);
					$each["options"] = $_attribute->getSource()->getAllOptions(false);
					$returnArray[] = $each;
				}
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getadvancedsearchResult($data)    {
			try{
				$data = json_decode($data);
				$queryStringArray = json_decode($data->queryString);
				$storeId = $data->storeId;
				$width = $data->width;
				$sortData = json_decode($data->sortData);
				$returnArray = array();$productCollectionArray = array();$criteriaArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

				//Getting Product Collection
				$queryArray = Mage::helper("mobikul/catalog")->getQueryArray($queryStringArray);
				$productCollection = Mage::getSingleton("catalogsearch/advanced")->addFilters($queryArray)->getProductCollection();

				//sorting product collection
				if(count($sortData) > 0){
					$sortBy = $sortData[0];
					if($sortData[1] == 0)
						$productCollection->setOrder($sortBy,"ASC");
					else
						$productCollection->setOrder($sortBy,"DESC");
				}
				if(isset($data->pageNumber)){
					$pageNumber = $data->pageNumber;
					$returnArray["totalCount"] = $productCollection->getSize();
					$productCollection->setPageSize(16)->setCurPage($pageNumber);
				}
				foreach($productCollection as $_product)
					$productCollectionArray[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($_product, $storeId, $width);
				$returnArray["productCollection"] = $productCollectionArray;

				//Getting Sorating Collection
				$toolbar = new Mage_Catalog_Block_Product_List_Toolbar();
				$availableOrders = $toolbar->getAvailableOrders();
				unset($availableOrders["position"]);
				$availableOrders = array_merge(array("relevance" => "Relevance"), $availableOrders);
				foreach($availableOrders as $_key => $_order){
					$each = array();
					$each["code"] = $_key;
					$each["label"] = $_order;
					$sortingData[] = $each;
				}
				$returnArray["sortingData"] = $sortingData;

				//Getting Criteria
				$advancedSearchBlock = new Mage_CatalogSearch_Block_Advanced_Result();
				$searchCriterias = $advancedSearchBlock->getSearchCriterias();
				foreach(array("left", "right") as $side){
					if($searchCriterias[$side]){
						foreach($searchCriterias[$side] as $criteria){
							$criteriaArray[] = Mage::helper("core")->stripTags($criteria["name"])." : ".Mage::helper("core")->stripTags($criteria["value"]);
						}
					}
				}
				$returnArray["critariaData"] = $criteriaArray;
				//getting website data
				$returnArray["storeData"] = Mage::helper("mobikul/catalog")->getStoreData();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getproductratingDetails($data){
			try{
				$data = json_decode($data);
				$returnArray = array();$allReviews = array();$ratingArray = array();
				$storeId = $data->storeId;
				$productId = $data->productId;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

				// Getting Produc Rating Details
				$width = 150;
				if(isset($data->width))
					$width = $data->width;
				$_product = Mage::getModel("catalog/product")->load($productId);
				$returnArray["name"] = $_product->getName();
				$returnArray["thumbNail"] = Mage::helper("catalog/image")->init($_product, "small_image")->keepFrame(true)->resize($width)->__toString();
				$returnArray["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getPrice()));
				$returnArray["price"] = $_product->getPrice();
				$returnArray["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getFinalPrice()));
				$returnArray["finalPrice"] = $_product->getFinalPrice();
				if($_product->getTypeId() == "bundle"){
					$bundlePriceModel = Mage::getModel("bundle/product_price");
					$returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($_product,"min",1)));
					$returnArray["minPrice"] = $bundlePriceModel->getTotalPrices($_product,"min",1);
					$returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($_product,"max",1)));
					$returnArray["maxPrice"] = $bundlePriceModel->getTotalPrices($_product,"max",1);
				}
				else{
					$returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getMinPrice()));
					$returnArray["minPrice"] = $_product->getMinPrice();
					$returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getMaxPrice()));
					$returnArray["maxPrice"] = $_product->getMaxPrice();
				}
				$returnArray["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getSpecialPrice()));
				$returnArray["specialPrice"] = $_product->getSpecialPrice();
				$returnArray["msrpEnabled"] = $_product->getMsrpEnabled();
				$returnArray["msrpDisplayActualPriceType"] = $_product->getMsrpDisplayActualPriceType();
				$returnArray["msrp"] = $_product->getMsrp();
				$returnArray["formatedMsrp"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getMsrp()));
				$isInRange = 0;
				if(isset($fromdate) && isset($todate)){
					$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
					$today_time = strtotime($today);
					$from_time = strtotime($fromdate);
					$to_time = strtotime($todate);
					if($today_time >= $from_time && $today_time <= $to_time)
						$isInRange = 1;
				}
				if(isset($fromdate) && !isset($todate)){
					$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
					$today_time = strtotime($today);
					$from_time = strtotime($fromdate);
					if($today_time >= $from_time)
						$isInRange = 1;
				}
				$returnArray["isInRange"] = $isInRange;
				$returnArray["typeId"] = $_product->getTypeId();
				$ratingCollection = Mage::getModel("rating/rating")
					->getResourceCollection()
					->addEntityFilter("product")
					->setPositionOrder()
					->setStoreFilter($storeId)
					->addRatingPerStoreName($storeId)
					->load();
				$ratingCollection->addEntitySummaryToItem($productId, $storeId);
				foreach($ratingCollection as $_rating){
					if($_rating->getSummary()){
						$eachRating = array();
						$eachRating["ratingCode"] = Mage::helper("core")->stripTags($_rating->getRatingCode());
						$eachRating["ratingValue"] = number_format((5*$_rating->getSummary())/100, 2, ".", "");
						$ratingArray[] = $eachRating;
					}
				}
				$returnArray["ratingData"] = $ratingArray;
				$reviewCollection = Mage::getModel("review/review")->getResourceCollection()->addStoreFilter($storeId)
						->addEntityFilter("product", $productId)->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
						->setDateOrder()->addRateVotes();
				foreach($reviewCollection as $_review){
					$oneReview = array();$ratings = array();
					$oneReview["title"] = Mage::helper("core")->stripTags($_review->getTitle());
					$oneReview["details"] = Mage::helper("core")->stripTags($_review->getDetail());
					$_votes = $_review->getRatingVotes();
					if(count($_votes)){
						foreach($_votes as $_vote){
							$oneVote = array();
							$oneVote["label"] = Mage::helper("core")->stripTags($_vote->getRatingCode());
							$oneVote["value"] = number_format($_vote->getValue(), 2, ".", "");
							$ratings[] = $oneVote;
						}
					}
					$oneReview["ratings"] = $ratings;
					$oneReview["reviewBy"] = Mage::helper("core")->__("Review by %s", Mage::helper("core")->stripTags($_review->getNickname()));
					$oneReview["reviewOn"] = Mage::helper("core")->__("(Posted on %s)", Mage::helper("core")->formatDate($_review->getCreatedAt()), "long");
					$allReviews[] = $oneReview;
				}
				$returnArray["reviewList"] = $allReviews;
				$ratingCollection = Mage::getModel("rating/rating")
					->getResourceCollection()
					->addEntityFilter("product")
					->setPositionOrder()
					->addRatingPerStoreName($storeId)
					->setStoreFilter($storeId)
					->load()
					->addOptionToItems();
				$allRatingFormData = array();
				foreach($ratingCollection as $_rating){
					$eachTypeRating = array();
					$ratingFormData = array();
					foreach($_rating->getOptions() as $_option)
						$eachTypeRating[] = $_option->getId();
					$ratingFormData["id"] = $_rating->getId();
					$ratingFormData["name"] = Mage::helper("core")->stripTags($_rating->getRatingCode());
					$ratingFormData["values"] = $eachTypeRating;
					$allRatingFormData[] = $ratingFormData;
				}
				$returnArray["ratingFormData"] = $allRatingFormData;
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getproductDetails($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$productId = $data->productId;
				$width = $data->width;
				$allReviews = array();$imageGallery = array();$returnArray = array();$relatedProductData = array();$additionalInformation = array();$allOptions = array();$ratingArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$_product = Mage::getModel("catalog/product")->load($productId);
				Mage::register("current_product", $_product);
				Mage::register("product", $_product);
				$returnArray["id"] = $productId;
				$returnArray["productUrl"] = $_product->getProductUrl();
				$returnArray["name"] = $_product->getName();
				$returnArray["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getPrice()));
				$returnArray["price"] = $_product->getPrice();
				$returnArray["formatedFinalPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getFinalPrice()));
				$returnArray["finalPrice"] = $_product->getFinalPrice();
				if($_product->getTypeId() == "bundle"){
					$bundlePriceModel = Mage::getModel("bundle/product_price");
					$returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($_product,"min",1)));
					$returnArray["minPrice"] = $bundlePriceModel->getTotalPrices($_product,"min",1);
					$returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($bundlePriceModel->getTotalPrices($_product,"max",1)));
					$returnArray["maxPrice"] = $bundlePriceModel->getTotalPrices($_product,"max",1);
				}
				else{
					$returnArray["formatedMinPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getMinPrice()));
					$returnArray["minPrice"] = $_product->getMinPrice();
					$returnArray["formatedMaxPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getMaxPrice()));
					$returnArray["maxPrice"] = $_product->getMaxPrice();
				}
				$returnArray["formatedSpecialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getSpecialPrice()));
				$returnArray["specialPrice"] = $_product->getSpecialPrice();
				$returnArray["typeId"] = $_product->getTypeId();
				$returnArray["msrpEnabled"] = $_product->getMsrpEnabled();
				$returnArray["msrpDisplayActualPriceType"] = $_product->getMsrpDisplayActualPriceType();
				$returnArray["msrp"] = $_product->getMsrp();
				$returnArray["formatedMsrp"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_product->getMsrp()));
				$returnArray["shortDescription"] = Mage::helper("core")->stripTags($_product->getShortDescription());
				$returnArray["description"] = Mage::helper("core")->stripTags($_product->getDescription());
				$fromdate = $_product->getSpecialFromDate();
				$todate = $_product->getSpecialToDate();
				$isInRange = 0;
				if(isset($fromdate) && isset($todate)){
					$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
					$today_time = strtotime($today);
					$from_time = strtotime($fromdate);
					$to_time = strtotime($todate);
					if($today_time >= $from_time && $today_time <= $to_time)
						$isInRange = 1;
				}
				if(isset($fromdate) && !isset($todate)){
					$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
					$today_time = strtotime($today);
					$from_time = strtotime($fromdate);
					if($today_time >= $from_time)
						$isInRange = 1;
				}
				$returnArray["isInRange"] = $isInRange;
				if($_product->isAvailable()){
					$returnArray["availability"] = Mage::helper("mobikul")->__("In stock");
					$returnArray["isAvailable"] = 1;
				}
				else{
					$returnArray["availability"] =  Mage::helper("mobikul")->__("Out of stock");
					$returnArray["isAvailable"] = 0;
				}

				// getting price format
				$returnArray["priceFormat"] = Mage::app()->getLocale()->getJsPriceFormat();

				// getting image galleries
				$galleryCollection = $_product->getMediaGalleryImages();
				$imageGallery[0]["smallImage"] = Mage::helper("catalog/image")->init($_product, "image")->keepFrame(false)->resize($width/3)->__toString();
				$imageGallery[0]["largeImage"] = Mage::helper("catalog/image")->init($_product, "image")->keepFrame(false)->resize($width)->__toString();
				$imageCount = 0;
				foreach($galleryCollection as $_image) {
					$imageCount++;
					if($imageCount == 1)
						continue;
					$eachImage = array();
					$eachImage["smallImage"] = Mage::helper("catalog/image")->init($_product, "thumbnail", $_image->getFile())->keepFrame(false)->resize($width/3)->__toString();
					$eachImage["largeImage"] = Mage::helper("catalog/image")->init($_product, "thumbnail", $_image->getFile())->keepFrame(false)->resize($width)->__toString();
					$imageGallery[] = $eachImage;
				}
				$returnArray["imageGallery"] = $imageGallery;

				//getting additional information
				foreach($_product->getAttributes() as $attribute) {
					if($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), array())) {
						$value = $attribute->getFrontend()->getValue($_product);
						if(!$_product->hasData($attribute->getAttributeCode()))
							$value = Mage::helper("catalog")->__("N/A");
						elseif((string)$value == "")
							$value = Mage::helper("catalog")->__("No");
						elseif($attribute->getFrontendInput() == "price" && is_string($value))
							$value = Mage::app()->getStore()->convertPrice($value, true);
						if(is_string($value) && strlen($value)) {
							$eachAttribute = array();
							$eachAttribute["label"] = $attribute->getStoreLabel();
							$eachAttribute["value"] = html_entity_decode(Mage::helper("core")->stripTags($value));
							$additionalInformation[] = $eachAttribute;
						}
					}
				}
				$returnArray["additionalInformation"] = $additionalInformation;

				//getting rating list
				$ratingCollection = Mage::getModel("rating/rating")
					->getResourceCollection()
					->addEntityFilter("product")
					->setPositionOrder()
					->setStoreFilter($storeId)
					->addRatingPerStoreName($storeId)
					->load();
				$ratingCollection->addEntitySummaryToItem($productId, $storeId);
				foreach($ratingCollection as $_rating){
					if($_rating->getSummary()){
						$eachRating = array();
						$eachRating["ratingCode"] = Mage::helper("core")->stripTags($_rating->getRatingCode());
						$eachRating["ratingValue"] = number_format((5*$_rating->getSummary())/100, 2, ".", "");
						$ratingArray[] = $eachRating;
					}
				}
				$returnArray["ratingData"] = $ratingArray;

				//getting review list
				$reviewCollection = Mage::getModel("review/review")->getResourceCollection()->addStoreFilter($storeId)
						->addEntityFilter("product", $productId)->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
						->setDateOrder()->addRateVotes();
				foreach($reviewCollection as $_review){
					$oneReview = array();$ratings = array();
					$oneReview["title"] = Mage::helper("core")->stripTags($_review->getTitle());
					$oneReview["details"] = Mage::helper("core")->stripTags($_review->getDetail());
					$_votes = $_review->getRatingVotes();
					if(count($_votes)){
						foreach($_votes as $_vote){
							$oneVote = array();
							$oneVote["label"] = Mage::helper("core")->stripTags($_vote->getRatingCode());
							$oneVote["value"] = number_format($_vote->getValue(), 2, ".", "");
							$ratings[] = $oneVote;
						}
					}
					$oneReview["ratings"] = $ratings;
					$oneReview["reviewBy"] = Mage::helper("core")->__("Review by %s", Mage::helper("core")->stripTags($_review->getNickname()));
					$oneReview["reviewOn"] = Mage::helper("core")->__("(Posted on %s)", Mage::helper("core")->formatDate($_review->getCreatedAt()), "long");
					$allReviews[] = $oneReview;
				}
				$returnArray["reviewList"] = $allReviews;

				//getting custom options
				$optionBlock = new Mage_Catalog_Block_Product_View_Options();
				$_options = Mage::helper("core")->decorateArray($optionBlock->getOptions());
				if(count($_options)){
					$eachOption = array();
					foreach($_options as $_option){
						$eachOption = $_option->getData();
						$eachOption["unformated_default_price"] = Mage::helper("core")->currency($_option->getDefaultPrice(), false, false);
						$eachOption["formated_default_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_option->getDefaultPrice()));
						$eachOption["unformated_price"] = Mage::helper("core")->currency($_option->getPrice(), false, false);
						$eachOption["formated_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_option->getPrice()));
						$optionValueCollection = $_option->getValues();
						$eachOptionValue = array();
						foreach($optionValueCollection as $optionValue){
							$eachOptionValue[$optionValue->getId()] = $optionValue->getData();
							$eachOptionValue[$optionValue->getId()]["formated_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($optionValue->getPrice()));
							$eachOptionValue[$optionValue->getId()]["formated_default_price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($optionValue->getDefaultPrice()));
						}
						$eachOption["optionValues"] = $eachOptionValue;
						$allOptions[] = $eachOption;
					}
				}
				$returnArray["customOptions"] = $allOptions;

				// getting downloadable product data
				if($_product->getTypeId() == "downloadable"){
					$linkArray = array();
					$downloadableBlock = new Mage_Downloadable_Block_Catalog_Product_Links();
					$linkArray["title"] = $downloadableBlock->getLinksTitle();
					$linkArray["linksPurchasedSeparately"] = $downloadableBlock->getLinksPurchasedSeparately();
					$_links = $downloadableBlock->getLinks();
					$linkData = array();
					foreach($_links as $_link){
						$eachLink = array();
						$eachLink["id"] = $linkId = $_link->getId();
						$eachLink["linkTitle"] = $_link->getTitle()?$_link->getTitle():"";
						$eachLink["price"] = Mage::helper("core")->currency($_link->getPrice(), false, false);
						$eachLink["formatedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_link->getPrice()));
						if($_link->getSampleFile() || $_link->getSampleUrl())	{
							$link = Mage::getModel("downloadable/link")->load($linkId);
							if($link->getId()) {
								if($link->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
									$eachLink["url"] = $link->getSampleUrl();
									$fileArray = explode(DS, $link->getSampleUrl());
									$eachLink["fileName"] = end($fileArray);
								}
								else
								if($link->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
									$sampleLinkFilePath = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Link::getBaseSamplePath(), $link->getSampleFile());
									$eachLink["url"] = Mage::getUrl("mobikul/download/downloadlinksample", array("linkId" => $linkId));
									$fileArray = explode(DS, $sampleLinkFilePath);
									$eachLink["fileName"] = end($fileArray);
								}
							}
							$eachLink["haveLinkSample"] = 1;
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
					$_linkSamples = $downloadableSampleBlock->getSamples();
					$linkSampleData = array();
					foreach($_linkSamples as $_linkSample){
						$eachSample = array();
						$sampleId = $_linkSample->getId();
						$eachSample["sampleTitle"] = Mage::helper("core")->stripTags($_linkSample->getTitle());
						$sample = Mage::getModel("downloadable/sample")->load($sampleId);
						if($sample->getId()) {
							if($sample->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL){
								$eachSample["url"] = $sample->getSampleUrl();
								$fileArray = explode(DS, $sample->getSampleUrl());
								$eachSample["fileName"] = end($fileArray);
							}
							else
							if($sample->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE){
								$sampleFilePath = Mage::helper("downloadable/file")->getFilePath(Mage_Downloadable_Model_Sample::getBasePath(), $sample->getSampleFile());
								$eachSample["url"] = Mage::getUrl("mobikul/download/downloadsample", array("sampleId" => $sampleId));
								$fileArray = explode(DS, $sampleFilePath);
								$eachSample["fileName"] = end($fileArray);
							}
						}
						$linkSampleData[] = $eachSample;
					}
					$linkSampleArray["linkSampleData"] = $linkSampleData;
					$returnArray["samples"] = $linkSampleArray;
				}

				// getting grouped product data
				if($_product->getTypeId() == "grouped"){
					$groupedParentId = Mage::getModel("catalog/product_type_grouped")->getParentIdsByChild($_product->getId());
					$_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
					$min_price = array();$groupedData = array();
					foreach($_associatedProducts as $_associatedProduct) {
						$eachAssociatedProduct = array();
						$eachAssociatedProduct["name"] = Mage::helper("core")->stripTags($_associatedProduct->getName());
						$eachAssociatedProduct["id"] = $_associatedProduct->getId();
						if($_associatedProduct->isAvailable())
							$eachAssociatedProduct["isAvailable"] = $_associatedProduct->isAvailable();
						else
							$eachAssociatedProduct["isAvailable"] = 0;
						$fromdate = $_associatedProduct->getSpecialFromDate();
						$todate = $_associatedProduct->getSpecialToDate();
						$isInRange = 0;
						if(isset($fromdate) && isset($todate)){
							$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
							$today_time = strtotime($today);
							$from_time = strtotime($fromdate);
							$to_time = strtotime($todate);
							if($today_time >= $from_time && $today_time <= $to_time)
								$isInRange = 1;
						}
						if(isset($fromdate) && !isset($todate)){
							$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
							$today_time = strtotime($today);
							$from_time = strtotime($fromdate);
							if($today_time >= $from_time)
								$isInRange = 1;
						}
						if(!isset($fromdate) && isset($todate)){
							$today = Mage::getModel("core/date")->date("Y-m-d H:i:s");
							$today_time = strtotime($today);
							$from_time = strtotime($fromdate);
							if($today_time <= $from_time)
								$isInRange = 1;
						}
						$eachAssociatedProduct["isInRange"] = $isInRange;
						$eachAssociatedProduct["specialPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_associatedProduct->getSpecialPrice()));
						$eachAssociatedProduct["foramtedPrice"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_associatedProduct->getPrice()));
						$eachAssociatedProduct["thumbNail"] = Mage::helper("catalog/image")->init($_associatedProduct, "thumbnail")->keepFrame(true)->resize($width/5)->__toString();
						$groupedData[] = $eachAssociatedProduct;
					}
					$returnArray["groupedData"] = $groupedData;
				}

				// getting bundle product options
				if($_product->getTypeId() == "bundle"){
					$typeInstance = $_product->getTypeInstance(true);
					$typeInstance->setStoreFilter($_product->getStoreId(), $_product);
					$optionCollection = $typeInstance->getOptionsCollection($_product);
					$selectionCollection = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($_product), $_product);
					$bundleOptionCollection = $optionCollection->appendSelections($selectionCollection, false, Mage::helper("catalog/product")->getSkipSaleableCheck());
					$bundleOptionBlock = new Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option();
					$bundleOptions = array();
					foreach($bundleOptionCollection as $bundleOption) {
						$_oneOption = array();
						if(!$bundleOption->getSelections())
							continue;
						$_oneOption = $bundleOption->getData();
						$_selections = $bundleOption->getSelections();
						unset($_oneOption["selections"]);
						$bundleOptionValues = array();
						foreach($_selections as $_selection) {
							$eachBundleOptionValues = array();
							if($_selection->isSaleable()){
								$coreHelper = Mage::helper("core");
								$taxHelper  = Mage::helper("tax");
								$price = $_product->getPriceModel()->getSelectionPreFinalPrice($_product, $_selection, 1);
								$priceTax = $taxHelper->getPrice($_product, $price);
								if($_oneOption["type"] == "checkbox" || $_oneOption["type"] == "multi")
									$eachBundleOptionValues["title"] = str_replace("&nbsp;", " ", Mage::helper("core")->stripTags($bundleOptionBlock->getSelectionQtyTitlePrice($_selection)));
								if($_oneOption["type"] == "radio" || $_oneOption["type"] == "select")
									$eachBundleOptionValues["title"] = str_replace("&nbsp;", " ", Mage::helper("core")->stripTags($bundleOptionBlock->getSelectionTitlePrice($_selection, false)));
								$eachBundleOptionValues["isQtyUserDefined"] = $_selection->getSelectionCanChangeQty();
								$eachBundleOptionValues["isDefault"] = $_selection->getIsDefault();
								$eachBundleOptionValues["optionValueId"] = $_selection->getSelectionId();
								$eachBundleOptionValues["foramtedPrice"] = $coreHelper->currencyByStore($priceTax, $_product->getStore(), true, true);
								$eachBundleOptionValues["price"] = $coreHelper->currencyByStore($priceTax, $_product->getStore(), false, false);
								$eachBundleOptionValues["isSingle"] = (count($_selections) == 1 && $bundleOption->getRequired());
								$eachBundleOptionValues["defaultQty"] = $_selection->getSelectionQty();
								$bundleOptionValues[$_selection->getId()] = $eachBundleOptionValues;
							}
						}
						$_oneOption["optionValues"] = $bundleOptionValues;
						$bundleOptions[] = $_oneOption;
					}
					$returnArray["bundleOptions"] = $bundleOptions;
					$returnArray["priceView"] = $_product->getPriceView();
				}

				// getting bundle product options
				if($_product->getTypeId() == "configurable"){
					$configurableBlock = new Webkul_MobiKul_Block_Configurable();
					$returnArray["configurableData"] = $configurableBlock->getJsonConfig();
				}

				// getting tier prices
				$allTierPrices = array();
				$tierBlock = new Mage_Catalog_Block_Product_Price();
				$_tierPrices = $tierBlock->getTierPrices();
				foreach($_tierPrices as $_index => $_price){
					$allTierPrices[] = Mage::helper("core")->__("Buy %s for %s each", $_price["price_qty"], Mage::helper("core")->stripTags($_price['formated_price_incl_tax']))." ".Mage::helper("mobikul")->__("and")." ".Mage::helper("mobikul")->__("save")." ".$_price['savePercent']."%";
				}
				$returnArray["tierPrices"] = $allTierPrices;
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quote = Mage::getModel("sales/quote")->setStoreId($storeId)->loadByCustomer($customerId);
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$relatedProductCollection = Mage::getModel("catalog/product_link")->getCollection()->addFieldToFilter("product_id", $productId)->addFieldToFilter("link_type_id", "1");
				foreach($relatedProductCollection as $_product){
					$_product = Mage::getModel("catalog/product")->load($_product->getId());
					$relatedProductData[] = Mage::helper("mobikul/catalog")->getOneProductRelevantData($_product, $storeId, $width);
				}
				$returnArray["relatedProductData"] = $relatedProductData;
				if(isset($data->customerId) || isset($data->quoteId))
					$returnArray["cartCount"] = $quote->getItemsQty()*1;
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function addtoWishlist($data)    {
			try{
				$data = json_decode($data);
				$customerId = $data->customerId;
				$storeId = $data->storeId;
				$productId = $data->productId;
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
				$buyRequest = new Varien_Object();
				$product = Mage::getModel("catalog/product")->load($productId);
				$wishlist->addNewItem($product, $buyRequest);
				$customer = Mage::getModel("customer/customer")->load($customerId);
				$collection = $wishlist->getItemCollection()->setInStockFilter(true);
				if(Mage::getStoreConfig("wishlist/wishlist_link/use_qty"))
					$count = $collection->getItemsQty();
				else
					$count = $collection->getSize();
				$session = Mage::getSingleton("customer/session")->setCustomer($customer);
				$session->setWishlistDisplayType(Mage::getStoreConfig("wishlist/wishlist_link/use_qty"));
				$session->setDisplayOutOfStockProducts(
					Mage::getStoreConfig("cataloginventory/options/show_out_of_stock")
				);
				$session->setWishlistItemCount($count);
				$wishlist->save();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode(array("status" => 1));
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

	}