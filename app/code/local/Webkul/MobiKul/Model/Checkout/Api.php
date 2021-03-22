<?php

	class Webkul_MobiKul_Model_Checkout_Api extends Mage_Api_Model_Resource_Abstract    {

		public function getcartDetails($data)    {
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$width = $data->width;
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				if(isset($data->customerId) || isset($data->quoteId)){
					$itemCollection = $quote->getAllVisibleItems();
					foreach($itemCollection as $item) {
						$eachItem = array();
						$eachItem["image"] = Mage::helper("catalog/image")->init($item->getProduct(), "thumbnail")->keepFrame(true)->resize($width/2.5)->__toString();
						$eachItem["name"] = Mage::helper("core")->stripTags($item->getName());
						$options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
						if($item->getProduct()->getTypeId() == "configurable"){
							$configurableOptions = $options["attributes_info"];
							foreach($configurableOptions as $configurableOption) {
								$eachConfigurableOption = array();
								$eachConfigurableOption["label"] = $configurableOption["label"];
								$eachConfigurableOption["value"][] = $configurableOption["value"];
								$eachItem["options"][] = $eachConfigurableOption;
							}
						}
						if($item->getProduct()->getTypeId() == "bundle"){
							$bundleOptions = $options["bundle_options"];
							foreach($bundleOptions as $bundleOption) {
								$eachBundleOption = array();
								$eachBundleOption["label"] = $bundleOption["label"];
								foreach($bundleOption["value"] as $bundleOptionValue) {
									$price = 0;
									if($bundleOptionValue["price"] > 0)
										$price = $bundleOptionValue["price"]/$bundleOptionValue["qty"];
									$price = Mage::helper("core")->stripTags(Mage::helper("core")->currency($price));
									$eachBundleOptionValue = $bundleOptionValue["qty"]." x ".$bundleOptionValue["title"]." ".$price;
									$eachBundleOption["value"][] = $eachBundleOptionValue;
								}
								$eachItem["options"][] = $eachBundleOption;
							}
						}
						if($item->getProduct()->getTypeId() == "downloadable"){
							$links = Mage::helper("downloadable/catalog_product_configuration")->getLinks($item);
							if(count($links) > 0){
								$downloadOption = array();
								$titles = array();
								foreach($links as $link_id)
									$titles[] = $link_id->getTitle();
								$downloadOption["label"] = Mage::helper("downloadable/catalog_product_configuration")->getLinksTitle($item->getProduct());
								$downloadOption["value"] = $titles;
								$eachItem["options"][] = $downloadOption;
							}
						}
						if(isset($options["options"]))	{
							$customOptions = $options["options"];
							foreach($customOptions as $customOption) {
								$eachCustomOption = array();
								$eachCustomOption["label"] = $customOption["label"];
								$eachCustomOption["value"][] = $customOption["print_value"];
								$eachItem["options"][] = $eachCustomOption;
							}
						}
						$eachItem["sku"] = Mage::helper("core")->stripTags(Mage::helper("core/string")->splitInjection($item->getSku()));
						$eachItem["price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getPrice()));
						$eachItem["qty"] = $item->getQty()*1;
						$eachItem["productId"] = $item->getProductId();
						$eachItem["typeId"] = $item->getProductType();
						$eachItem["subTotal"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($item->getRowTotal()));
						$eachItem["id"] = $item->getId();
						$baseMessages = $item->getMessage(false);
						if($baseMessages) {
							foreach($baseMessages as $message) {
								$messages = array();
								$messages[] = array(
									"text" => $message,
									"type" => $item->getHasError() ? "error" : "notice"
								);
								$eachItem["messages"] = $messages;
							}
						}
						$returnArray["items"][] = $eachItem;
					}
					$returnArray["couponCode"] = $quote->getCouponCode();
					$totals = $quote->getTotals();
					$subtotal = "";$discount = "";$grandtotal = "";
					if(isset($totals["subtotal"])){
						$subtotal = $totals["subtotal"];
						$returnArray["subtotal"]["title"] = $subtotal->getTitle();
						$returnArray["subtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($subtotal->getValue()));
					}
					if(isset($totals["discount"])){
						$discount = $totals["discount"];
						$returnArray["discount"]["title"] = $discount->getTitle();
						$returnArray["discount"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($discount->getValue()));
					}
					if(isset($totals["shipping"])){
						$shipping = $totals["shipping"];
						$returnArray["shipping"]["title"] = $shipping->getTitle();
						$returnArray["shipping"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($shipping->getValue()));
					}
					if(isset($totals["tax"])){
						$tax = $totals["tax"];
						$returnArray["tax"]["title"] = $tax->getTitle();
						$returnArray["tax"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($tax->getValue()));
					}
					if(isset($totals["grand_total"])){
						$grandtotal = $totals["grand_total"];
						$returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
						$returnArray["grandtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($grandtotal->getValue()));
					}
					if(isset($data->customerId) || isset($data->quoteId))
						$returnArray["cartCount"] = $quote->getItemsQty()*1;
				}
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function removecartItem($data)    {
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$itemId = $data->itemId;
				$returnArray = array();
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $data->customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$quote->removeItem($itemId);
				$quote->collectTotals()->save();
				$totals = $quote->getTotals();
				if(isset($totals["subtotal"])){
					$subtotal = $totals["subtotal"];
					$returnArray["subtotal"]["title"] = $subtotal->getTitle();
					$returnArray["subtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($subtotal->getValue()));
				}
				if(isset($totals["discount"])){
					$discount = $totals["discount"];
					$returnArray["discount"]["title"] = $discount->getTitle();
					$returnArray["discount"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($discount->getValue()));
				}
				if(isset($totals["tax"])){
					$tax = $totals["tax"];
					$returnArray["tax"]["title"] = $tax->getTitle();
					$returnArray["tax"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($tax->getValue()));
				}
				if(isset($totals["grand_total"])){
					$grandtotal = $totals["grand_total"];
					$returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
					$returnArray["grandtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($grandtotal->getValue()));
				}
				if(isset($data->customerId) || isset($data->quoteId))
					$returnArray["itemCount"] = $quote->getItemsQty()*1;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function emptyCart($data)    {
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$returnArray = array();
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$quote->removeAllItems()->collectTotals()->save();
				return Mage::helper("core")->jsonEncode(array("status" => 1));
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function wishlistfromCart($data)    {
			try{
				$data = json_decode($data);
				$customerId = $data->customerId;
				$storeId = $data->storeId;
				$itemId = $data->itemId;
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$quoteCollection = Mage::getModel("sales/quote")->getCollection();
				$quoteCollection->addFieldToFilter("customer_id", $customerId);
				$quoteCollection->addOrder("updated_at", "desc");
				$quote = $quoteCollection->getFirstItem();
				$wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customerId, true);
				$item = $quote->getItemById($itemId);
				$productId  = $item->getProductId();
				$buyRequest = $item->getBuyRequest();
				$wishlist->addNewItem($productId, $buyRequest);
				$quote->removeItem($itemId);
				$quote->collectTotals()->save();
				$customer = Mage::getModel("customer/customer")->load($customerId);
				$collection = $wishlist->getItemCollection()->setInStockFilter(true);
				if(Mage::getStoreConfig("wishlist/wishlist_link/use_qty"))
					$count = $collection->getItemsQty();
				else
					$count = $collection->getSize();
				$session = Mage::getSingleton("customer/session")->setCustomer($customer);
				$session->setWishlistDisplayType(Mage::getStoreConfig("wishlist/wishlist_link/use_qty"));
				$session->setDisplayOutOfStockProducts(Mage::getStoreConfig("cataloginventory/options/show_out_of_stock"));
				$session->setWishlistItemCount($count);
				$wishlist->save();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode(array("status" => 1));
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function applyCoupon($data)    {
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$couponCode = $data->couponCode;
				$removeCoupon = $data->removeCoupon;
				$returnArray = array();
				$error = 0;$message = "";
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				if($removeCoupon == 1)
					$couponCode = "";
				$oldCouponCode = $quote->getCouponCode();
				if(!strlen($couponCode) && !strlen($oldCouponCode))
					$error = 1;
				$codeLength = strlen($couponCode);
				$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
				$quote->getShippingAddress()->setCollectShippingRates(true);
				$quote->setCouponCode($isCodeLengthValid ? $couponCode : "")->collectTotals()->save();
				if($codeLength) {
					if($isCodeLengthValid && $couponCode == $quote->getCouponCode()){
						$error = 0;
						$message = Mage::helper("core")->__("Coupon code '%s' was applied.", Mage::helper("core")->stripTags($couponCode));
					}
					else{
						$error = 1;
						$message = Mage::helper("core")->__("Coupon code '%s' is not valid.", Mage::helper("core")->stripTags($couponCode));
					}
				}
				else {
					$error = 0;
					$message = Mage::helper("mobikul")->__("Coupon code was canceled.");
				}
				$returnArray["error"] = $error;
				$returnArray["message"] = $message;
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function updateCart($data)    {
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$itemIds = $data->itemIds;
				$itemQtys = $data->itemQtys;
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$cartData = array();
				foreach($itemIds as $key => $value)
					$cartData[$value] = array("qty" => $itemQtys[$key]);
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$filter = new Zend_Filter_LocalizedToNormalized(array("locale" => Mage::app()->getLocale()->getLocaleCode()));
				foreach($cartData as $index => $eachData) {
					if(isset($eachData["qty"]))
						$cartData[$index]["qty"] = $filter->filter(trim($eachData["qty"]));
				}
				$tempData = array();
				foreach($cartData as $itemId => $itemInfo) {
					if(!isset($itemInfo["qty"]))
						continue;
					$qty = (float) $itemInfo["qty"];
					if($qty <= 0)
						continue;
					$quoteItem = $quote->getItemById($itemId);
					if(!$quoteItem)
						continue;
					$product = $quoteItem->getProduct();
					if(!$product)
						continue;
					$stockItem = $product->getStockItem();
					if(!$stockItem)
						continue;
					$quoteItem->setQty($qty)->save();
				}
				$quote->collectTotals()->save();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function addtoCart($data)    {
			try{
				$data = json_decode($data);
				$quoteId = 0;
				$productId = $data->productId;
				if(isset($data->customerId))
					$customerId = $data->customerId;
				if(isset($data->quoteId))
					$quoteId = $data->quoteId;
				$storeId = $data->storeId;
				$store = Mage::getSingleton("core/store")->load($storeId);
				$returnArray = array();
				$returnArray["error"] = 0;$returnArray["message"] = "";
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				if(!isset($data->customerId) && !isset($data->quoteId)){
					$quote = Mage::getModel("sales/quote")
						->setStoreId($storeId)
						->setIsActive(true)
						->setIsMultiShipping(false)
						->save();
					$quote->getBillingAddress();
					$quote->getShippingAddress()->setCollectShippingRates(true);
					$quote->collectTotals()->save();
					$quoteId = (int)$quote->getId();
					$returnArray["quoteId"] = $quoteId;
				}
				$params = array();
				if(isset($data->qty))
					$qty = $data->qty;
				else
					$qty = 1;
				if(isset($data->customerId)){
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
					$quoteId = $quote->getId();
					if($quote->getId() < 0){
						$quote = Mage::getModel("sales/quote")
							->setStoreId($storeId)
							->setIsActive(true)
							->setIsMultiShipping(false)
							->save();
						$quoteId = (int)$quote->getId();
						$returnArray["quoteId"] = $quoteId;
						$customer = Mage::getModel("customer/customer")->load($customerId);
						$quote->assignCustomer($customer);
						$quote->setCustomer($customer);
						$quote->getBillingAddress();
						$quote->getShippingAddress()->setCollectShippingRates(true);
						$quote->collectTotals()->save();
					}
				}
				else
					$quote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
				$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($productId);
				if(isset($qty)) {
					$availableQty = Mage::getModel("cataloginventory/stock_item")->loadByProduct($product)->getQty();
					if($qty <= $availableQty){
						$filter = new Zend_Filter_LocalizedToNormalized(array("locale" => Mage::app()->getLocale()->getLocaleCode()));
						$qty = $filter->filter($qty);
					}
					else{
						if(!in_array($product->getTypeId(), array("grouped", "configurable", "bundle"))){
							$returnArray["error"] = 1;
							$returnArray["message"] = Mage::helper("mobikul")->__("The requested quantity is not available");
							return Mage::helper("core")->jsonEncode($returnArray);
						}
					}
				}
				$filesToDelete = array();
				$paramOption = array();
				if(isset($data->params->options)){
					$params = $data->params->options;
					foreach($params as $optionId => $values) {
						$_option = Mage::getModel("catalog/product_option")->load($optionId);
						$_optionType = $_option->getType();
						if(in_array($_optionType, array("multiple", "checkbox"))){
							foreach($values as $optionValue)
								$paramOption[$optionId][] = $optionValue;
						}
						else
						if(in_array($_optionType, array("radio", "drop_down", "area", "field"))){
							$paramOption[$optionId] = $values;
						}
						else
						if($_optionType == "file"){
							//downloading file
							$base64_string = $params->$optionId->encodeImage;
							$fileName = time().$params->$optionId->name;
							$fileType = $params->$optionId->type;
							$fileWithPath = Mage::getBaseDir().DS."media".DS.$fileName;
							$ifp = fopen($fileWithPath, "wb");
							fwrite($ifp, base64_decode($base64_string));
							//assigning file to option
							$fileOption = array(
								"type" => $fileType,
								"title" => $fileName,
								"quote_path" => DS."media".DS.$fileName,
								"fullpath" => $fileWithPath,
								"secret_key" => substr(md5(file_get_contents($fileWithPath)), 0, 20)
							);
							$filesToDelete[] = $fileWithPath;
							$paramOption[$optionId] = $fileOption;
						}
						else
						if($_optionType == "date"){
							$paramOption[$optionId]["month"] = $values->month;
							$paramOption[$optionId]["day"] = $values->day;
							$paramOption[$optionId]["year"] = $values->year;
						}
						else
						if($_optionType == "date_time"){
							$paramOption[$optionId]["month"] = $values->month;
							$paramOption[$optionId]["day"] = $values->day;
							$paramOption[$optionId]["year"] = $values->year;
							$paramOption[$optionId]["hour"] = $values->hour;
							$paramOption[$optionId]["minute"] = $values->minute;
							$paramOption[$optionId]["day_part"] = $values->day_part;
						}
						else
						if($_optionType == "time"){
							$paramOption[$optionId]["hour"] = $values->hour;
							$paramOption[$optionId]["minute"] = $values->minute;
							$paramOption[$optionId]["day_part"] = $values->day_part;
						}
					}
				}
				if($product->getTypeId() == "downloadable"){
					$links = array();
					if(isset($data->params->links)){
						foreach($data->params->links as $key => $value)
							$links[] = $data->params->links->$key;
						$params = array("related_product" => null, "links" => $links, "options" => $paramOption, "qty" => $qty, "product_id" => $productId);
					}
					else
						$params = array("related_product" => null, "options" => $paramOption, "qty" => $qty, "product_id" => $productId);
				}
				else
				if($product->getTypeId() == "grouped"){
					$super_group = array();
					foreach($data->params->super_group as $key => $value)
						$super_group[$key] = intval($data->params->super_group->$key);
					$params = array("related_product" => null, "super_group" => $super_group, "product_id" => $productId);
				}
				else
				if($product->getTypeId() == "configurable"){
					$super_attribute = array();
					foreach($data->params->super_attribute as $key => $value)
						$super_attribute[$key] = $data->params->super_attribute->$key;
					$params = array("related_product" => null, "super_attribute" => $super_attribute, "options" => $paramOption, "qty" => $qty, "product_id" => $productId);
				}
				else
				if($product->getTypeId() == "bundle"){
					Mage::register("product", $product);
					$selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
						$product->getTypeInstance(true)->getOptionsIds($product), $product
					);
					foreach($selectionCollection as $option){
						$selection_qty = $option->selection_qty * 1;
						$key = $option->option_id;
						if(isset($data->params->bundle_option_qty->$key))
							$probablyRequestedQty = $data->params->bundle_option_qty->$key;
						if($selection_qty > 1)
							$requestedQty = $selection_qty * $qty;
						elseif(isset($probablyRequestedQty))
							$requestedQty = $probablyRequestedQty * $qty;
						else
							$requestedQty = 1;
						$associateBundleProduct = Mage::getModel("catalog/product")->load($option->product_id);
						$availableQty = Mage::getModel("cataloginventory/stock_item")->loadByProduct($associateBundleProduct)->getQty();
						if($associateBundleProduct->getisSalable()){
							if($requestedQty > $availableQty){
								$returnArray["error"] = 1;
								$returnArray["message"] = Mage::helper("mobikul")->__("The requested quantity of ").$option->name.Mage::helper("mobikul")->__(" is not available");
								return Mage::helper("core")->jsonEncode($returnArray);
							}
						}
					}
					$bundle_option = array();
					if($data->params->bundle_option){
						foreach($data->params->bundle_option as $key => $value)
							$bundle_option[$key] = $data->params->bundle_option->$key;
					}
					$bundle_option_qty = array();
					if($data->params->bundle_option_qty){
						foreach($data->params->bundle_option_qty as $key => $value)
							$bundle_option_qty[$key] = intval($data->params->bundle_option_qty->$key);
					}
					$params = array("related_product" => null, "bundle_option" => $bundle_option, "bundle_option_qty" => $bundle_option_qty, "options" => $paramOption, "qty" => $qty, "product_id" => $productId);
				}
				else{
					$params = array("related_product" => null, "options" => $paramOption, "qty" => $qty, "product_id" => $productId);
				}
				$productAdded = Mage::getModel("checkout/cart_product_api")->add($quoteId, array($params), $store);
				if(!$productAdded){
					$returnArray["error"] = 1;
					$returnArray["message"] = Mage::helper("mobikul")->__("Unable to add product to cart.");
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				else{
					$quote = Mage::getModel("sales/quote")->setStore($store)->load($quoteId);
					$returnArray["cartCount"] = $quote->getItemsQty()*1;
				}
				$returnArray["message"] = Mage::helper("core")->__("%s was added to your shopping cart.", Mage::helper("core")->stripTags($product->getName()));
				//delete files uploaded for custom option
				foreach($filesToDelete as $eachFile)
					unlink($eachFile);
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Mage_Core_Exception $e){
				$returnArray["error"] = 1;
				$returnArray["message"] = $e->getCustomMessage();
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e) {
				$returnArray["error"] = 1;
				$returnArray["message"] = Mage::helper("mobikul")->__("Can't add the item to shopping cart.");
				return Mage::helper("core")->jsonEncode($returnArray);
			}
		}

		public function getsteponentwoData($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$addressIds = array();
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$customer = Mage::getModel("customer/customer")->load($customerId);
					$address = $customer->getPrimaryBillingAddress();
					if($address instanceof Varien_Object){
						$tempbillingAddress = array();
						$tempbillingAddress["value"] = $address->getFirstname()." ".$address->getLastname()." ";
						foreach($address->getStreet() as $street)
							$tempbillingAddress["value"] .= $street.", ";
						$tempbillingAddress["value"] .= $address->getCity().", ".$address->getRegion().", ".$address->getPostcode()." ".Mage::getModel("directory/country")->load($address->getCountryId())->getName();
						$tempbillingAddress["id"] = $address->getId();
						if(!in_array($address->getId(), $addressIds)){
							$addressIds[] = $address->getId();
							$returnArray["address"][] = $tempbillingAddress;
						}
					}
					$address = $customer->getPrimaryShippingAddress();
					if($address instanceof Varien_Object){
						$tempshippingAddress = array();
						$tempshippingAddress["value"] = $address->getFirstname()." ".$address->getLastname()." ";
						foreach($address->getStreet() as $street)
							$tempshippingAddress["value"] .= $street.", ";
						$tempshippingAddress["value"] .= $address->getCity().", ".$address->getRegion().", ".$address->getPostcode()." ".Mage::getModel("directory/country")->load($address->getCountryId())->getName();
						$tempshippingAddress["id"] = $address->getId();
						if(!in_array($address->getId(), $addressIds)){
							$addressIds[] = $address->getId();
							$returnArray["address"][] = $tempshippingAddress;
						}
					}
					$additionalAddress = $customer->getAdditionalAddresses();
					foreach($additionalAddress as $key => $eachAdditionalAddress) {
						if($eachAdditionalAddress instanceof Varien_Object){
							$eachAdditionalAddressArray = array();
							$eachAdditionalAddressArray["value"] = $eachAdditionalAddress->getFirstname()." ".$eachAdditionalAddress->getLastname()." ";
							foreach($eachAdditionalAddress->getStreet() as $street)
								$eachAdditionalAddressArray["value"] .= $street.", ";
							$eachAdditionalAddressArray["value"] .= $eachAdditionalAddress->getCity().", ".$eachAdditionalAddress->getRegion().", ".$eachAdditionalAddress->getPostcode()." ".Mage::getModel("directory/country")->load($eachAdditionalAddress->getCountryId())->getName()." ";
							$eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
							$returnArray["address"][] = $eachAdditionalAddressArray;
						}
					}
				}
				else
					$returnArray["address"][] = array();
				$countryCollection = Mage::getModel("directory/country")->getResourceCollection()->loadByStore()->toOptionArray(true);
				unset($countryCollection[0]);
				foreach($countryCollection as $country) {
					$eachCountry = array();
					$eachCountry["country_id"] = $country["value"];
					$eachCountry["name"] = $country["label"];
					$regionCollection = Mage::getModel("directory/region_api")->items($eachCountry["country_id"]);
					if(count($regionCollection) > 0)
						$eachCountry["states"] = $regionCollection;
					$returnArray["countryData"][] = $eachCountry;
				}
				if(isset($data->customerId)){
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId))
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($data->quoteId);
				$returnArray["isVirtual"] = $quote->isVirtual();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getstepthreenfourData($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$returnArray = array();
				$extraInformation = "";
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
					$quoteId = $quote->getId();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$use_for_shipping = 0;

				if(!empty($data->billingData))	{
					$billingData = $data->billingData;

					$save_in_address_book = 0;
					if(isset($billingData->newAddress->save_in_address_book))
						$save_in_address_book = $billingData->newAddress->save_in_address_book;
					if($data->checkoutMethod == "register")
						$save_in_address_book = 1;

					if(isset($billingData->use_for_shipping))
						$use_for_shipping = $billingData->use_for_shipping;

					$addressId = "";
					if(isset($billingData->addressId))
						$addressId = $billingData->addressId;

					$quote->setCheckoutMethod($data->checkoutMethod)->save();
					$newAddress = "";
					if(isset($billingData->newAddress))
						if(!empty($billingData->newAddress))
							$newAddress = $billingData->newAddress;
					$address = $quote->getBillingAddress();
					$addressForm = Mage::getModel("customer/form");
					$addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
					if(is_numeric($addressId)) {
						$customerAddress = Mage::getModel("customer/address")->load($addressId);
						if($customerAddress->getId()) {
							if($customerAddress->getCustomerId() != $quote->getCustomerId()){
								return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => Mage::helper("mobikul")->__("Customer Address is not valid.")));
							}
							$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
							$addressForm->setEntity($address);
							$addressErrors  = $addressForm->validateData($address->getData());
							if($addressErrors !== true){
								return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => $addressErrors));
							}
						}
					}
					else {
						$addressForm->setEntity($address);
						$addressData = array(
								"firstname" => 	$newAddress->firstName,
								"lastname" 	=> 	$newAddress->lastName,
								"company" 	=> 	$newAddress->company,
								"street" 	=> 	array(
													$newAddress->street[0],
													$newAddress->street[1]
												),
								"city" 		=> 	$newAddress->city,
								"country_id"=> 	$newAddress->country_id,
								"region" 	=> 	$newAddress->region,
								"region_id" => 	$newAddress->region_id,
								"postcode" 	=> 	$newAddress->postcode,
								"postcode" 	=> 	$newAddress->postcode,
								"telephone" => 	$newAddress->telephone,
								"fax" 		=> 	$newAddress->fax,
								"vat_id" 	=> 	""
							);
						$addressErrors  = $addressForm->validateData($addressData);
						if($addressErrors !== true){
							return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => array_values($addressErrors)));
						}
						$addressForm->compactData($addressData);
						$address->setCustomerAddressId(null);
						$address->setSaveInAddressBook($save_in_address_book);
						$quote->setCustomerFirstname($newAddress->firstName)->setCustomerLastname($newAddress->lastName);
					}
					if(in_array($data->checkoutMethod, array("register", "guest"))){
						$quote->setCustomerEmail(trim($newAddress->emailAddress));
						$address->setEmail(trim($newAddress->emailAddress));
					}
					if(!$address->getEmail() && $quote->getCustomerEmail()){
						$address->setEmail($quote->getCustomerEmail());
					}
					if(($validateRes = $address->validate()) !== true){
						return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => $validateRes));
					}
					$address->implodeStreetAddress();
					if(true !== ($result = $this->_validateCustomerData($data))){
						return Mage::helper("core")->jsonEncode($result);
					}
					if(!$quote->getCustomerId() && "register" == $quote->getCheckoutMethod()) {
						if($this->_customerEmailExists($address->getEmail(), Mage::app()->getStore()->getWebsiteId()))
							return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => Mage::helper("mobikul")->__("This email already exist.")));
					}
					if(!$quote->isVirtual()) {
						$usingCase = isset($use_for_shipping) ? (int)$use_for_shipping : 0;
						switch($usingCase) {
							case 0:
								$shipping = $quote->getShippingAddress();
								$shipping->setSameAsBilling(0);
								$setStepDataShipping = 0;
								break;
							case 1:
								$billing = clone $address;
								$billing->unsAddressId()->unsAddressType();
								$shipping = $quote->getShippingAddress();
								$shippingMethod = $shipping->getShippingMethod();
								$shipping->addData($billing->getData())
									->setSameAsBilling(1)
									->setSaveInAddressBook(0)
									->setShippingMethod($shippingMethod)
									->setCollectShippingRates(true);
								$setStepDataShipping = 1;
								break;
						}
					}
					$quote->collectTotals()->save();
					if(!$quote->isVirtual() && $setStepDataShipping)
						$quote->getShippingAddress()->setCollectShippingRates(true);
				}
				else{
					$returnArray = array("error" => 1, "message" => Mage::helper("mobikul")->__("Invalid Billing data."));
					return Mage::helper("core")->jsonEncode($returnArray);
				}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////                            ////////////////////////////////////////////////
/////////////////////////////////////////////// step 4 process starts here ////////////////////////////////////////////////
///////////////////////////////////////////////                            ////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				if($use_for_shipping == 0){
					if(isset($data->shippingData)){
						$shippingData = $data->shippingData;

						$same_as_billing = 0;
						if(isset($shippingData->same_as_billing))
							$same_as_billing = $shippingData->same_as_billing;

						$newAddress = "";
						if(isset($shippingData->newAddress))
							if(!empty($shippingData->newAddress))
								$newAddress = $shippingData->newAddress;

						$addressId = "";
						if(isset($shippingData->addressId))
							$addressId = $shippingData->addressId;

						$save_in_address_book = 0;
						if(isset($shippingData->newAddress->save_in_address_book))
							$save_in_address_book = $shippingData->newAddress->save_in_address_book;
						$address = $quote->getShippingAddress();
						$addressForm = Mage::getModel("customer/form");
						$addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
						if(is_numeric($addressId)) {
							$customerAddress = Mage::getModel("customer/address")->load($addressId);
							if($customerAddress->getId()) {
								if($customerAddress->getCustomerId() != $quote->getCustomerId())
									return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => Mage::helper("mobikul")->__("Customer Address is not valid.")));
								$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
								$addressForm->setEntity($address);
								$addressErrors  = $addressForm->validateData($address->getData());
								if($addressErrors !== true)
									return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => $addressErrors));
							}
						}
						else {
							$addressForm->setEntity($address);
							$addressData = array(
									"firstname" => 	$newAddress->firstName,
									"lastname" 	=> 	$newAddress->lastName,
									"company" 	=> 	$newAddress->company,
									"street" 	=> 	array(
														$newAddress->street[0],
														$newAddress->street[1]
													),
									"city" 		=> 	$newAddress->city,
									"country_id"=> 	$newAddress->country_id,
									"region" 	=> 	$newAddress->region,
									"region_id" => 	$newAddress->region_id,
									"postcode" 	=> 	$newAddress->postcode,
									"telephone" => 	$newAddress->telephone,
									"fax" 		=> 	$newAddress->fax,
									"vat_id" 	=> 	""
								);
							$addressErrors  = $addressForm->validateData($addressData);
							if($addressErrors !== true)
								return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => $addressErrors));
							$addressForm->compactData($addressData);
							$address->setCustomerAddressId(null);
							// Additional form data, not fetched by extractData (as it fetches only attributes)
							$address->setSaveInAddressBook($save_in_address_book);
							$address->setSameAsBilling($same_as_billing);
						}
						$address->implodeStreetAddress();
						$address->setCollectShippingRates(true);
						if(($validateRes = $address->validate()) !== true)
							return Mage::helper("core")->jsonEncode(array("error" => 1, "message" => $validateRes));
						$quote->collectTotals()->save();
					}
					else{
						$returnArray = array("error" => 1, "message" => Mage::helper("mobikul")->__("Invalid Shipping data."));
						return Mage::helper("core")->jsonEncode($returnArray);
					}
				}
				$quote->getShippingAddress()->collectShippingRates()->save();
				$_shippingRateGroups = $quote->getShippingAddress()->getGroupedAllShippingRates();
				foreach($_shippingRateGroups as $code => $_rates) {
					$oneShipping = array();
					$oneShipping["title"] = Mage::helper("core")->stripTags(Mage::getStoreConfig("carriers/".$code."/title"));
					foreach($_rates as $_rate){
						$oneMethod = array();
						if($_rate->getErrorMessage())
							$onemethod["error"] = $_rate->getErrorMessage();
						$oneMethod["code"] = $_rate->getCode();
						$oneMethod["label"] = $_rate->getMethodTitle();
						$oneMethod["price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency((float)$_rate->getPrice()));
						$oneShipping["method"][] = $oneMethod;
					}
					$returnArray["shippingMethods"][] = $oneShipping;
				}
				foreach(Mage::helper("payment")->getStoreMethods($storeId, $quote) as $method) {
					if($method->isApplicableToQuote($quote, 1|2|32) && $method->isApplicableToQuote($quote,128)) {
						if(!$method->isGateway()){
							if($method->getCode() == "checkmo"){
								$extraInformation = Mage::getStoreConfig("payment/".$method->getCode()."/mailing_address");
								if($extraInformation == "")
									$extraInformation = "You can Send Check at xxxxxxx";
							}
							else
							if($method->getCode() == "banktransfer"){
								$extraInformation = Mage::getStoreConfig("payment/".$method->getCode()."/instructions");
								if($extraInformation == "")
									$extraInformation = "Bank Details are xxxxxxx";
							}
							else
							if($method->getCode() == "cashondelivery"){
								$extraInformation = Mage::getStoreConfig("payment/".$method->getCode()."/instructions");
								if($extraInformation == "")
									$extraInformation = "Pay at the time of delivery";
							}
							else{
								$layout = Mage::app()->getLayout();
								$blockType = $method->getFormBlockType();
								$block = $layout->createBlock($blockType);
								$extraInformation = Mage::helper("core")->stripTags($block->toHtml());
							}
							$extraInformation = trim($extraInformation);
						}
						else{
							$allowedCc = array();
							$allowedCcTypesString = $method->getConfigData("cctypes");
							$allowedCcTypes = explode(",", $allowedCcTypesString);
							$_types = Mage::getConfig()->getNode("global/payment/cc/types")->asArray();
							uasort($_types, array("Mage_Payment_Model_Config", "compareCcTypes"));
							$types = array();
							foreach($_types as $data) {
								if(isset($data["code"]) && isset($data["name"]))
									$types[$data["code"]] = $data["name"];
							}
							foreach($allowedCcTypes as $value){
								$eachAllowedCc = array();
								$eachAllowedCc["code"] = $value;
								$eachAllowedCc["name"] = $types[$value];
								$allowedCc[] = $eachAllowedCc;
							}
							$extraInformation = $allowedCc;
						}
						$oneMethod = array();
						$oneMethod["code"] = $method->getCode();
						$oneMethod["title"] = $method->getTitle();
						$oneMethod["extraInformation"] = $extraInformation;
						$returnArray["paymentMethods"][] = $oneMethod;
					}
				}
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function getorderreviewData($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$orderReviewData = array();
				//saving shipping
				if(isset($data->shippingMethod) && $data->shippingMethod != ""){
					$shippingMethod = $data->shippingMethod;
					$rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
					if(!$rate)
						return array("error" => 1, "message" => Mage::helper("mobikul")->__("Invalid shipping method."));
					$quote->getShippingAddress()->setShippingMethod($shippingMethod);
				}
				//saving payment
				if(isset($data->method) && $data->method != ""){
					$method = $data->method;
					$paymentData = array();
					$paymentData["method"] = $method;
					if(isset($data->cc_cid))
						$paymentData["cc_cid"] = $data->cc_cid;
					if(isset($data->cc_exp_month))
						$paymentData["cc_exp_month"] = $data->cc_exp_month;
					if(isset($data->cc_exp_year))
						$paymentData["cc_exp_year"] = $data->cc_exp_year;
					if(isset($data->cc_number))
						$paymentData["cc_number"] = $data->cc_number;
					if(isset($data->cc_type))
						$paymentData["cc_type"] = $data->cc_type;
					if($quote->isVirtual())
						$quote->getBillingAddress()->setPaymentMethod(isset($method) ? $method : null);
					else
						$quote->getShippingAddress()->setPaymentMethod(isset($method) ? $method : null);
					if(!$quote->isVirtual() && $quote->getShippingAddress()) {
						$quote->getShippingAddress()->setCollectShippingRates(true);
					}
					$paymentData["checks"] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
					$payment = $quote->getPayment()->importData($paymentData);
					$quote->save();
				}				
				foreach($quote->getAllVisibleItems() as $_item) {
					$eachItem = array();
					$eachItem["productName"] = Mage::helper("core")->stripTags($_item->getName());
					$customoptions = $_item->getProduct()->getTypeInstance(true)->getOrderOptions($_item->getProduct());
					$result = array();
					if($customoptions) {
						if(isset($customoptions["options"]))
							$result = array_merge($result, $customoptions["options"]);
						if(isset($customoptions["additional_options"]))
							$result = array_merge($result, $customoptions["additional_options"]);
						if(isset($customoptions["attributes_info"]))
							$result = array_merge($result, $customoptions["attributes_info"]);
					}
					if($result){
						foreach($result as $_option){
							$eachOption = array();
							$eachOption["label"] = Mage::helper("core")->stripTags($_option["label"]);
							$eachOption["value"] = $_option["value"];
							$eachItem["option"][] = $eachOption;
						}
					}
					$eachItem["price"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_item->getCalculationPrice()));
					$eachItem["unformatedPrice"] = $_item->getCalculationPrice();
					$eachItem["qty"] = $_item->getQty();
					$eachItem["sku"] = $_item->getSku();
					$eachItem["subTotal"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($_item->getRowTotal()));
					$orderReviewData["items"][] = $eachItem;
				}
				$totals = $quote->getTotals();
				if(isset($totals["subtotal"])){
					$subtotal = $totals["subtotal"];
					$orderReviewData["subtotal"]["title"] = $subtotal->getTitle();
					$orderReviewData["subtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($subtotal->getValue()));
					$orderReviewData["subtotal"]["unformatedValue"] = $subtotal->getValue();
				}
				if(isset($totals["discount"])){
					$discount = $totals["discount"];
					$orderReviewData["discount"]["title"] = $discount->getTitle();
					$orderReviewData["discount"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($discount->getValue()));
					$orderReviewData["discount"]["unformatedValue"] = $discount->getValue();
				}
				if(isset($totals["tax"])){
					$tax = $totals["tax"];
					$orderReviewData["tax"]["title"] = $tax->getTitle();
					$orderReviewData["tax"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($tax->getValue()));
					$orderReviewData["tax"]["unformatedValue"] = $tax->getValue();
				}
				if(isset($totals["shipping"])){
					$shipping = $totals["shipping"];
					$orderReviewData["shipping"]["title"] = $shipping->getTitle();
					$orderReviewData["shipping"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($shipping->getValue()));
					$orderReviewData["shipping"]["unformatedValue"] = $shipping->getValue();
				}
				if(isset($totals["grand_total"])){
					$grandtotal = $totals["grand_total"];
					$orderReviewData["grandtotal"]["title"] = $grandtotal->getTitle();
					$orderReviewData["grandtotal"]["value"] = Mage::helper("core")->stripTags(Mage::helper("core")->currency($grandtotal->getValue()));
					$orderReviewData["grandtotal"]["unformatedValue"] = $grandtotal->getValue();
				}
				$orderReviewData["currencyCode"] = Mage::app()->getStore()->getCurrentCurrencyCode();
				$returnArray["orderReviewData"] = $orderReviewData;
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function saveOrder($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				if(isset($data->customerId)){
					$customerId = $data->customerId;
					$quoteCollection = Mage::getModel("sales/quote")->getCollection();
					$quoteCollection->addFieldToFilter("customer_id", $customerId);
					$quoteCollection->addOrder("updated_at", "desc");
					$quote = $quoteCollection->getFirstItem();
					$quoteId = $quote->getId();
				}
				if(isset($data->quoteId)){
					$quoteId = $data->quoteId;
					$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
				}
				$method = $data->method;
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

				// saving order
				$orderData = array();
				$orderData["method"] = $method;

				if(isset($data->cc_cid))
					$orderData["cc_cid"] = $data->cc_cid;
				if(isset($data->cc_exp_month))
					$orderData["cc_exp_month"] = $data->cc_exp_month;
				if(isset($data->cc_exp_year))
					$orderData["cc_exp_year"] = $data->cc_exp_year;
				if(isset($data->cc_number))
					$orderData["cc_number"] = $data->cc_number;
				if(isset($data->cc_type))
					$orderData["cc_type"] = $data->cc_type;
				$orderData["checks"] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
				$quote->getPayment()->importData($orderData);
				if($quote->getCheckoutMethod() == "customer")
					$customer = Mage::getModel("customer/customer")->load($customerId);
				if($quote->getCheckoutMethod() == Mage_Checkout_Model_Api_Resource_Customer::MODE_GUEST && !Mage::helper("checkout")->isAllowedGuestCheckout($quote, $quote->getStoreId())){
					$returnArray = array("error" => 1, "message" => Mage::helper("mobikul")->__("Guest Checkout is not Enabled"));
					return Mage::helper("core")->jsonEncode($returnArray);
				}
				$customerResource = Mage::getModel("checkout/api_resource_customer");
				$isNewCustomer = $customerResource->prepareCustomerForQuote($quote);
				$quote->collectTotals();
				$service = Mage::getModel("sales/service_quote", $quote);
				$service->submitAll();
				if($isNewCustomer)
					$customerResource->involveNewCustomer($quote);
				$order = $service->getOrder();
				if($order) {
					Mage::dispatchEvent("checkout_type_onepage_save_order_after", array("order" => $order, "quote" => $quote));
					try {
						$order->sendNewOrderEmail();
					}
					catch(Exception $e) {
						Mage::logException($e);
					}
				}
				Mage::dispatchEvent("checkout_submit_all_after", array("order" => $order, "quote" => $quote));
				$quote->removeAllItems();
				$quote->collectTotals()->save();
				$canReorder = 0;
				$customerApi = new Webkul_MobiKul_Model_Customer_Api();
				if($customerApi->canReorder($order) == 1)
					$canReorder = $customerApi->canReorder($order);
				else
					$canReorder = 0;
				$returnArray = array("error" => 0, "orderId" => $order->getId(), "incrementId" => $order->getIncrementId(), "canReorder" => $canReorder);
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		public function changeorderStatus($data){
			try{
				$data = json_decode($data);
				$storeId = $data->storeId;
				$customerId = $data->customerId;
				$incrementId = $data->orderId;
				$confirm = json_decode($data->confirm);
				$response = json_decode($confirm);
				$confirmPayment = $data->confirmPayment;
				$status = $data->status;
				$returnArray = array();
				$appEmulation = Mage::getSingleton("core/app_emulation");
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
				$order = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
				$payment = $order->getPayment();
				$payment->setTransactionId($confirm->response->id)
					->setPreparedMessage("status : ".$confirm->response->state)
					->setShouldCloseParentTransaction(true)
					->setIsTransactionClosed(0)
					->registerCaptureNotification($order->getGrandTotal());
				$order->save();
				if($status == 0){
					$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
						->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING)
						->save();
				}
				else{
					$order->setState(Mage_Sales_Model_Order::STATE_CANCELED)
						->setStatus(Mage_Sales_Model_Order::STATE_CANCELED)
						->save();
				}
				if($order->canInvoice()){
					$invoice = Mage::getModel("sales/service_order", $order)->prepareInvoice();
					$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
					$invoice->register();
					$transactionSave = Mage::getModel("core/resource_transaction")
						->addObject($invoice)
						->addObject($invoice->getOrder());
					$transactionSave->save();
				}
				$comment = "status :".$confirm->response->state."<br>";
				$comment .= "transaction id :".$confirm->response->id."<br>";
				$comment .= "date :".$confirm->response->create_time."<br>";
				$comment .= "from :".$confirm->client->product_name."<br>";
				$order->setIsCustomerNotified(false);
				$order->addStatusHistoryComment($comment);
				$order->save();
				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
				return Mage::helper("core")->jsonEncode($returnArray);
			}
			catch(Exception $e){
				Mage::log($e);
			}
		}

		protected function _customerEmailExists($email, $websiteId = null)    {
			$customer = Mage::getModel("customer/customer");
			if($websiteId)
				$customer->setWebsiteId($websiteId);
			$customer->loadByEmail($email);
			if($customer->getId())
				return $customer;
			return false;
		}

		protected function _validateCustomerData($data)    {
			$storeId = $data->storeId;
			$customerForm = Mage::getModel("customer/form")->setFormCode("checkout_register");
			if(isset($data->customerId)){
				$customerId = $data->customerId;
				$quoteCollection = Mage::getModel("sales/quote")->getCollection();
				$quoteCollection->addFieldToFilter("customer_id", $customerId);
				$quoteCollection->addOrder("updated_at", "desc");
				$quote = $quoteCollection->getFirstItem();
			}
			if(isset($data->quoteId)){
				$quoteId = $data->quoteId;
				$quote = Mage::getModel("sales/quote")->setStore(Mage::getSingleton("core/store")->load($storeId))->load($quoteId);
			}
			if($quote->getCustomerId()) {
				$customer = $quote->getCustomer();
				$customerForm->setEntity($customer);
				$customerData = $quote->getCustomer()->getData();
			}
			else {
				$customer = Mage::getModel("customer/customer");
				$customerForm->setEntity($customer);
				$newAddress = "";
				$billingData = $data->billingData;
				if(isset($billingData->newAddress))
					if(!empty($billingData->newAddress))
						$newAddress = $billingData->newAddress;
				$customerData = array(
						"firstname" => $newAddress->firstName,
						"lastname" => $newAddress->lastName,
						"email" => trim($newAddress->emailAddress)
					);
			}
			$customerErrors = $customerForm->validateData($customerData);
			if($customerErrors !== true)
				return array("error" => 1, "message" => implode(", ", $customerErrors));
			if($quote->getCustomerId())
				return true;
			$customerForm->compactData($customerData);
			if($quote->getCheckoutMethod() == "register") {
				$customer->setPassword($data->password);
				$customer->setConfirmation($data->confirmPassword);
				$customer->setPasswordConfirmation($data->confirmPassword);
			}
			else {
				$password = $customer->generatePassword();
				$customer->setPassword($password);
				$customer->setConfirmation($password);
				$customer->setPasswordConfirmation($password);
				$customer->setGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
			}
			$result = $customer->validate();
			if(true !== $result && is_array($result))
				return array("error"   => -1, "message" => implode(", ", $result));
			if($quote->getCheckoutMethod() == "register")
				$quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
			$quote->getBillingAddress()->setEmail($customer->getEmail());
			$quote->setCustomer($customer);
			Mage::helper("core")->copyFieldset("customer_account", "to_quote", $customer, $quote);
			return true;
		}

	}