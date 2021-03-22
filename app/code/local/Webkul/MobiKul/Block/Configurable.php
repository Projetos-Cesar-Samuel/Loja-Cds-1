<?php

	class Webkul_MobiKul_Block_Configurable extends Mage_Catalog_Block_Product_View_Abstract	{

	    protected $_prices = array();
	    protected $_resPrices   = array();
	    public function getAllowAttributes()    {
	        return $this->getProduct()->getTypeInstance(true)->getConfigurableAttributes($this->getProduct());
	    }

	    public function hasOptions()    {
	        $attributes = $this->getAllowAttributes();
	        if(count($attributes)) {
	            foreach($attributes as $attribute) {
	                /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */
	                if($attribute->getData("prices"))
	                    return true;
	            }
	        }
	        return false;
	    }

	    public function getAllowProducts()	 {
	        if(!$this->hasAllowProducts()) {
	            $products = array();
	            $skipSaleableCheck = Mage::helper("catalog/product")->getSkipSaleableCheck();
	            $allProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct());
	            foreach($allProducts as $product) {
	                if($product->isSaleable() || $skipSaleableCheck)
	                    $products[] = $product;
	            }
	            $this->setAllowProducts($products);
	        }
	        return $this->getData("allow_products");
	    }

	    public function getCurrentStore()	 {
	        return Mage::app()->getStore();
	    }

	    protected function _getAdditionalConfig()	 {
	        return array();
	    }

	    public function getJsonConfig()	    {
	        $attributes = array();
	        $options    = array();
	        $store      = $this->getCurrentStore();
	        $taxHelper  = Mage::helper("tax");
	        $currentProduct = $this->getProduct();
	        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
	        if($preconfiguredFlag) {
	            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
	            $defaultValues       = array();
	        }
	        foreach($this->getAllowProducts() as $product) {
	            $productId  = $product->getId();
	            foreach($this->getAllowAttributes() as $attribute) {
	                $productAttribute   = $attribute->getProductAttribute();
	                $productAttributeId = $productAttribute->getId();
	                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
	                if(!isset($options[$productAttributeId]))
	                    $options[$productAttributeId] = array();
	                if(!isset($options[$productAttributeId][$attributeValue]))
	                    $options[$productAttributeId][$attributeValue] = array();
	                $options[$productAttributeId][$attributeValue][] = $productId;
	            }
	        }
	        $this->_resPrices = array(
	            $this->_preparePrice($currentProduct->getFinalPrice())
	        );
	        foreach($this->getAllowAttributes() as $attribute) {
	            $productAttribute = $attribute->getProductAttribute();
	            $attributeId = $productAttribute->getId();
	            $info = array(
	               "id"        => $productAttribute->getId(),
	               "code"      => $productAttribute->getAttributeCode(),
	               "label"     => $attribute->getLabel(),
	               "options"   => array()
	            );
	            $optionPrices = array();
	            $prices = $attribute->getPrices();
	            if(is_array($prices)) {
	                foreach($prices as $value) {
	                    if(!$this->_validateAttributeValue($attributeId, $value, $options))
	                        continue;
	                    $currentProduct->setConfigurablePrice(
	                        $this->_preparePrice($value["pricing_value"], $value["is_percent"])
	                    );
	                    $currentProduct->setParentId(true);
	                    Mage::dispatchEvent(
	                        "catalog_product_type_configurable_price",
	                        array("product" => $currentProduct)
	                    );
	                    $configurablePrice = $currentProduct->getConfigurablePrice();
	                    if(isset($options[$attributeId][$value["value_index"]]))
	                        $productsIndex = $options[$attributeId][$value["value_index"]];
	                    else
	                        $productsIndex = array();
	                    $info["options"][] = array(
	                        "id"        => $value["value_index"],
	                        "label"     => $value["label"],
	                        "price"     => $configurablePrice,
	                        "formatedPrice"  => Mage::helper("core")->stripTags(Mage::helper("core")->currency($configurablePrice)),
	                        "oldPrice"  => $this->_prepareOldPrice($value["pricing_value"], $value["is_percent"]),
	                        "formatedOldPrice"  => Mage::helper("core")->stripTags(Mage::helper("core")->currency($this->_prepareOldPrice($value["pricing_value"], $value["is_percent"]))),
	                        "products"  => $productsIndex,
	                    );
	                    $optionPrices[] = $configurablePrice;
	                }
	            }
	            /**
	             * Prepare formated values for options choose
	             */
	            foreach($optionPrices as $optionPrice) {
	                foreach($optionPrices as $additional)
	                    $this->_preparePrice(abs($additional-$optionPrice));
	            }
	            if($this->_validateAttributeInfo($info))
	               $attributes[] = $info;
	               // $attributes[$attributeId] = $info;
	            // Add attribute default value (if set)
	            if($preconfiguredFlag) {
	                $configValue = $preconfiguredValues->getData("super_attribute/" . $attributeId);
	                if($configValue)
	                    $defaultValues[$attributeId] = $configValue;
	            }
	        }
	        $taxCalculation = Mage::getSingleton("tax/calculation");
	        if(!$taxCalculation->getCustomer() && Mage::registry("current_customer"))
	            $taxCalculation->setCustomer(Mage::registry("current_customer"));
	        $_request = $taxCalculation->getDefaultRateRequest();
	        $_request->setProductClassId($currentProduct->getTaxClassId());
	        $defaultTax = $taxCalculation->getRate($_request);
	        $_request = $taxCalculation->getRateRequest();
	        $_request->setProductClassId($currentProduct->getTaxClassId());
	        $currentTax = $taxCalculation->getRate($_request);
	        $taxConfig = array(
	            "includeTax"        => $taxHelper->priceIncludesTax(),
	            "showIncludeTax"    => $taxHelper->displayPriceIncludingTax(),
	            "showBothPrices"    => $taxHelper->displayBothPrices(),
	            "defaultTax"        => $defaultTax,
	            "currentTax"        => $currentTax,
	            "inclTaxTitle"      => Mage::helper("catalog")->__("Incl. Tax")
	        );
	        $config = array(
	            "attributes"        => $attributes,
	            "template"          => str_replace("%s", "#{price}", $store->getCurrentCurrency()->getOutputFormat()),
	            "basePrice"         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
	            "formatedBasePrice"  => Mage::helper("core")->stripTags(Mage::helper("core")->currency($this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())))),
	            "oldPrice"          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
	            "formatedOldPrice"  => Mage::helper("core")->stripTags(Mage::helper("core")->currency($this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())))),
	            "productId"         => $currentProduct->getId(),
	            "chooseText"        => Mage::helper("catalog")->__("Choose an Option..."),
	            "taxConfig"         => $taxConfig
	        );
	        if($preconfiguredFlag && !empty($defaultValues))
	            $config["defaultValues"] = $defaultValues;
	        $config = array_merge($config, $this->_getAdditionalConfig());
	        return $config;
	    }

	    protected function _validateAttributeValue($attributeId, &$value, &$options)    {
	        if(isset($options[$attributeId][$value["value_index"]]))
	            return true;
	        return false;
	    }

	    protected function _validateAttributeInfo(&$info)	    {
	        if(count($info["options"]) > 0)
	            return true;
	        return false;
	    }

	    protected function _preparePrice($price, $isPercent = false)	{
	        if($isPercent && !empty($price))
	            $price = $this->getProduct()->getFinalPrice() * $price / 100;
	        return $this->_registerJsPrice($this->_convertPrice($price, true));
	    }

	    protected function _prepareOldPrice($price, $isPercent = false)	    {
	        if($isPercent && !empty($price))
	            $price = $this->getProduct()->getPrice() * $price / 100;
	        return $this->_registerJsPrice($this->_convertPrice($price, true));
	    }

	    protected function _registerJsPrice($price)	    {
	        return str_replace(",", ".", $price);
	    }

	    protected function _convertPrice($price, $round = false)    {
	        if(empty($price))
	            return 0;
	        $price = $this->getCurrentStore()->convertPrice($price);
	        if($round)
	            $price = $this->getCurrentStore()->roundPrice($price);
	        return $price;
	    }

	}