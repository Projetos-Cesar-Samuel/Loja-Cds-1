<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $joinedCategoryField;

    public function getViewedWith($productId, $block, $exclude=array())
    {
        if (is_null($productId) || ($productId === '')) {
            return new Varien_Data_Collection();
        }

        $product = Mage::getModel('catalog/product')->load($productId);

        $size = intVal($this->getBlockConfig($block, 'size'));
        if (!$size) {
            return new Varien_Data_Collection();
        }

        $exclude[] = $productId;

        $ignoreIds = false;
        switch ($this->getBlockConfig($block, 'data_source')) {
            case Amasty_Mostviewed_Model_Source_Datasource::SOURCE_VIEWED:
                $ids = $this->_getRelatedIdsViewed($product, $block);
                break;
            case Amasty_Mostviewed_Model_Source_Datasource::SOURCE_BOUGHT:
                $ids = $this->_getRelatedIdsBought($product, $block);
                break;
            default:
                $ignoreIds = true;
                break;
        }

        if (!$ignoreIds) {
            $ids = array_diff($ids, $exclude);
        }

        if (!$ignoreIds && !count($ids)) {
            return new Varien_Data_Collection();
        }

        $collection = Mage::getModel('catalog/product')->getResourceCollection();

        if (!$ignoreIds) {
            $collection->addIdFilter($ids);
        }

        $this->_addPricesAndAttributes($collection);
        $this->_addCommonFilters($collection, $block);

        if ($ignoreIds || $block != 'cross_sells') {
            $this->_addCategopryFilter($collection, $block, $productId);
            $this->_addBrandFilter($collection, $product, $block);
            $this->_addPriceFilter($collection, $product, $block);
            $this->_showOnlyCategories($collection);
        }

        if ($ignoreIds) {
            $collection->getSelect()
                ->group('e.entity_id')
                ->limit($size);
        } else {
            $this->_prepareSelect($collection, $ids, $size);
        }

        $used = $this->_getUsedIds($collection);

        // append is the last action, because we must display manually added products in any case
        if (Amasty_Mostviewed_Model_Source_Manually::APPEND == $this->getBlockConfig( $block , 'manually')) {
            $manuallyIds = $this->_getManuallyAddedIds($block, $productId);
            if (!empty($manuallyIds)) {
                $ids = array();
                $ids = array_merge($manuallyIds, $used);
                // unfortunately we need to load collection again
                $collection = Mage::getModel('catalog/product')->getResourceCollection();
                $collection->addIdFilter($ids);
                $this->_addPricesAndAttributes($collection);
                $this->_prepareSelect($collection, $ids, $size);
                
                $used = $this->_getUsedIds($collection, $manuallyIds);
            }
        }
        
        if (!empty($used)
            && !Mage::registry('ammostviewed_used')) {
            Mage::register('ammostviewed_used', $used, true);
        }
        
        return $collection;
        
        /** @todo:
         if the collection is empty, show items from the 
         same category or attribute set or with similar price (?)
        */
    }
    
    protected function _addPricesAndAttributes($collection)
    {
        // check how it works with the flat catalog
        $collection->addAttributeToSort('position', 'asc')
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        return $this;
    }
    
    protected function _addCommonFilters($collection, $block)
    {
        // remove items already in cart
        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
        if ($quoteId){
            Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($collection, $quoteId);
        }     
        // remove not visible in the catalog items 
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        
        // remove out of stock items
        $inStock = $this->getBlockConfig($block, 'in_stock');
        if ($inStock)
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $this;
    }
    
    protected function _getRelatedIdsViewed($product, $block)
    {
        if($product === null) {
            return array();
        }

        $id = $product->getId();
        $tbl = Mage::getSingleton('core/resource')->getTableName('reports/viewed_product_index');
        $db  = Mage::getSingleton('core/resource')->getConnection('ammostviewed_read');
        $storeId = Mage::app()->getStore()->getId();

        $period = $this->getBlockConfig($block, 'period');

        if (!$period) {
            $period = 1000;
        }

        $queryLimit = Mage::getStoreConfig('ammostviewed/general/limit', $storeId);
        if (!$queryLimit) {
            $queryLimit = 1000;
        }

        //get visitors who viewed this product
        $visitors = $db->select()->from(array('t2' => $tbl), array('visitor_id'))
            ->where('product_id = ?', $id)
            ->where('visitor_id IS NOT NULL')
            ->where('store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit($queryLimit);
        
        //get customers who viewed this product
        $customers = $db->select()->from(array('t2' => $tbl), array('customer_id'))
            ->where('product_id = ?', $id)
            ->where('customer_id IS NOT NULL')
            ->where('store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(added_at) <= ?', $period)
            ->limit($queryLimit);
            
        $visitors = array_unique($db->fetchCol($visitors));
        $customers = array_unique($db->fetchCol($customers));
        $customers = array_diff($customers, $visitors);

        // get related products
        $fields = array(
            'id'  => 't.product_id', 
            'cnt' => new Zend_Db_Expr('COUNT(*)'),
        );
        $productsByVisitor = $db->select()->from(array('t'=>$tbl), $fields)
            ->where('t.visitor_id IN (?)', $visitors)
            ->where('t.product_id != ?', $id)
            ->where('store_id = ?', $storeId)
            ->group('t.product_id')
            ->order('cnt DESC')
            ->limit($queryLimit);
        $productsByVisitor = $db->fetchAll($productsByVisitor);

        $productsByCustomer = $db->select()->from(array('t'=>$tbl), $fields)
            ->where('t.customer_id IN (?)', $customers)
            ->where('t.product_id != ?', $id)
            ->where('store_id = ?', $storeId)
            ->group('t.product_id')
            ->order('cnt DESC')
            ->limit($queryLimit);
        $productsByCustomer = $db->fetchAll($productsByCustomer);

        $data = array_merge($productsByVisitor, $productsByCustomer);

        $views = array();
        $products = array();
        foreach ($data as $key => $row) {
            $views[$key]  = $row['cnt'];
            $products[$key] = $row['id'];
        }

        array_multisort($views, SORT_DESC, $products);

        return array_unique($products);
    }
    
    protected function _addCategopryFilter($collection, $block, $productId = null)
    {
        if ($categoryIds = Mage::getStoreConfig('ammostviewed/general/exclude')) {
            $categoryIds = explode(',', $categoryIds);
            $collection->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left');
            $collection->addAttributeToFilter('category_id', array('nin' => $categoryIds));
            $this->joinedCategoryField = true;
        }


        $categorySetting = $this->getBlockConfig($block, 'category');

        if (!$categorySetting) {
            if (Mage::registry('ammostviewed_used')) {
                $collection->addIdFilter(Mage::registry('ammostviewed_used'), true);
            }
            return $this;
        }

        $category = Mage::registry('current_category');
        if (!$category) {
            $ids = array();
            
            $product  = Mage::registry('current_product');
            if (!$product) {
                $product = Mage::getModel('catalog/product')->load($productId);
            }

            if ($product) {
                $categories = $product->getCategoryCollection();

                $catPaths = array();
                if (0 < $categories->getSize()) {
                    foreach ($categories as $category)
                        $catPaths[] = array_reverse($category->getPathIds());
                }

                if (empty($catPaths)) {
                    $catPaths = array(array(Mage_Catalog_Model_Category::TREE_ROOT_ID));
                }

                $distances = array();

                foreach ($catPaths as $pathIndex => $path) {
                    foreach ($path as $categoryIndex => $category) {
                        if (isset($distances[$category])) {
                            $distances[$category]['distance'] = min(
                                $categoryIndex,
                                $distances[$category]
                            );
                        } else {
                            $distances[$category] = array(
                                'distance' => $categoryIndex,
                                'path'     => $pathIndex
                            );
                        }
                    }
                }

                $ids = array_keys($distances);
            }
            if ($ids) {
                $categoryId = array_pop($ids);
                $category = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($categoryId);
            }
        }
        
        if ($category) {
            $exclude = array();
            if (Amasty_Mostviewed_Model_Source_Condition_Category::SAME_CATEGORY_ONLY == $categorySetting) {
                $collection->addCategoryFilter($category);
            } else { // Amasty_Mostviewed_Model_Source_Condition_Category::SAME_CATEGORY_EXCLUDE
                $exclude = $category->getProductCollection()->getAllIds();
            }
            if (Mage::registry('ammostviewed_used')) {
                $exclude = array_merge($exclude, Mage::registry('ammostviewed_used'));
            }
            if (!empty($exclude)) {
                $collection->addIdFilter($exclude, true);
            }
        }
        
        return $this;
    }
    
    protected function _getManuallyAddedIds($block, $productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        switch ($block) {
            case 'related_products':
                $manuallyCollection = $product->getRelatedLinkCollection();
                break;
            case 'up_sells':
                $manuallyCollection = $product->getUpSellLinkCollection();
                break;
            case 'cross_sells':
                $manuallyCollection = $product->getCrossSellLinkCollection();
                break;
        }

        $manuallyCollection->getSelect()->order('position ASC');
        
        $manuallyIds = array();
        foreach ($manuallyCollection as $link) {
            $manuallyIds[] = $link->getLinkedProductId();
        }
        return $manuallyIds;
    }
    
    protected function _getUsedIds($collection, $except = array())
    {
        $used = array();
        if (0 < $collection->getSize()) {
            foreach ($collection as $product) {
                $product->setDoNotUseCategoryId(true);
                $id = $product->getId();
                if (!in_array($id, $except)) {
                    $used[] = $product->getId();
                }
            }
        }
        return $used;
    }
    
    protected function _prepareSelect($collection, $ids, $size)
    {
        $collection->getSelect()
            ->group('e.entity_id')
            ->reset(Zend_Db_Select::ORDER)
            ->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $ids) . ')'))
            ->limit($size);
        $collection->load();
        return $this;
    }

    public function getBlockConfig($block, $config)
    {
        return Mage::getStoreConfig('ammostviewed/'.$block.'/'.$config);
    }

    protected function _addBrandFilter($collection,$product, $block)
    {
        $enabled = $this->getBlockConfig($block, 'brand_condition');
        if (!$enabled || is_null($product->getId())) {
            return $this;
        }

        $brandAttribute = $this->getBlockConfig($block, 'brand_attribute');
        $brandAttributeValue = $product->getData($brandAttribute);
        if(is_null($brandAttributeValue)) {
            $resource = $product->getResource();
            $brandAttributeValue = $resource->getAttributeRawValue($product->getId(), $brandAttribute, $product->getStoreId());
        }

        if(($brandAttribute !== '') && ($brandAttributeValue !== '')) {
            $collection->addAttributeToFilter($brandAttribute, $brandAttributeValue);
        }
        return $this;
    }

    protected function _getRelatedIdsBought ($product, $block) {
        $tbl = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
        $db  = Mage::getSingleton('core/resource')->getConnection('ammostviewed_read');
        $storeId = Mage::app()->getStore()->getId();

        $period = $this->getBlockConfig($block, 'period');
        if (!$period) {
            $period = 1000;
        }

        $queryLimit = Mage::getStoreConfig('ammostviewed/general/limit');
        if (!$queryLimit) {
            $queryLimit = 1000;
        }
        $productIds = array();

        $productType = $product->getTypeId();
        $typeInstance = $product->getTypeInstance();

        switch($productType) {
            case 'grouped':
                $productIds = $typeInstance->getAssociatedProductIds($product);
                break;
            case 'configurable':
                $productIds = $typeInstance->getUsedProductIds($product);
                break;
            case 'bundle':
                $optionsIds = $typeInstance->getOptionsIds($product);
                $selections = $typeInstance->getSelectionsCollection($optionsIds, $product);
                foreach($selections as $selection) {
                    $productIds[] = $selection->getProductId();
                }
                break;
            default:
                $productIds[] = $product->getId();
        }

        //get customer who bought this product
        $customers = $db->select()->from(array('order_item' => $tbl), array())
            ->join(
                array('order' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                'order_item.order_id = order.entity_id',
                array('customer_id' => 'order.customer_id')
            )
            ->where('order_item.product_id IN(?)', $productIds)
            ->where('order.customer_id IS NOT NULL')
            ->where('order_item.store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(order.created_at) <= ?', $period)
            ->limit($queryLimit);
        $customers = array_unique($db->fetchCol($customers));

        $guests = $db->select()->from(array('order_item' => $tbl), array())
            ->join(
                array('order'=>Mage::getSingleton('core/resource')->getTableName('sales/order')),
                'order_item.order_id = order.entity_id',
                array('customer_id' => 'order.customer_email')
            )
            ->where('order_item.product_id IN(?)', $productIds)
            ->where('order.customer_is_guest = 1')
            ->where('order_item.store_id = ?', $storeId)
            ->where('TO_DAYS(NOW()) - TO_DAYS(order.created_at) <= ?', $period)
            ->limit($queryLimit);
        $guests = array_unique($db->fetchCol($guests));


        $productIdField = new \Zend_Db_Expr('
            IF(configurable.parent_id IS NOT NULL, configurable.parent_id, IF(bundle.parent_product_id IS NOT NULL, bundle.parent_product_id, order_item.product_id))
        ');
        $productsByCustomers = $db->select()->from(array('order_item' => $tbl), array('id'=>$productIdField, 'cnt' => new \Zend_Db_Expr('COUNT(*)')))
            ->join(
                array('order'=>Mage::getSingleton('core/resource')->getTableName('sales/order')),
                'order_item.order_id = order.entity_id',
                array()
            )
            ->joinLeft(
                array('configurable'=> Mage::getSingleton('core/resource')->getTableName('catalog/product_super_link')),
                'order_item.product_id = configurable.product_id',
                array()
            )
            ->joinLeft(
                array('bundle'=>Mage::getSingleton('core/resource')->getTableName('bundle/selection')),
                'order_item.product_id = bundle.product_id',
                array()
            )
            ->where('order_item.product_id NOT IN(?)', $productIds)
            ->where('order.customer_id IN(?)', $customers)
            ->where('order.status IN(?) ', explode(',', $this->getBlockConfig($block, 'order_status')))
            ->where('order_item.store_id = ?', $storeId)
            ->group('order_item.product_id')
            ->order('cnt DESC')
            ->limit($queryLimit);
        $productsByCustomers = $db->fetchAll($productsByCustomers);

        $productsByGuests = $db->select()->from(array('order_item' => $tbl), array('id'=>$productIdField, 'cnt' => new \Zend_Db_Expr('COUNT(*)')))
            ->join(
                array('order'=>Mage::getSingleton('core/resource')->getTableName('sales/order')),
                'order_item.order_id = order.entity_id',
                array()
            )
            ->joinLeft(
                array('configurable' => Mage::getSingleton('core/resource')->getTableName('catalog/product_super_link')),
                'order_item.product_id = configurable.product_id',
                array()
            )
            ->joinLeft(
                array('bundle'=>Mage::getSingleton('core/resource')->getTableName('bundle/selection')),
                'order_item.product_id = bundle.product_id',
                array()
            )
            ->where('order_item.product_id NOT IN(?)', $productIds)
            ->where('order.customer_email IN(?)', $guests)
            ->where('order_item.store_id = ?', $storeId)
            ->where('order.status IN(?) ', explode(',', $this->getBlockConfig($block, 'order_status')))
            ->group('order_item.product_id')
            ->order('cnt DESC')
            ->limit($queryLimit);

        $productsByGuests = $db->fetchAll($productsByGuests);

        $data = array_merge($productsByGuests, $productsByCustomers);

        $views = array();
        $products = array();
        foreach ($data as $key => $row) {
            $views[$key]  = $row['cnt'];
            $products[$key] = $row['id'];
        }

        array_multisort($views, SORT_DESC, $products);

        return array_unique($products);
    }

    protected function _addPriceFilter($collection, $product, $block)
    {
        $priceCondition = $this->getBlockConfig($block, 'price_condition');
        if (!$priceCondition || is_null($product->getId())) {
            return $this;
        }

        $price = $product->getPrice();

        switch($priceCondition) {
            case Amasty_Mostviewed_Model_Source_Condition_Price::SAME_AS:
                $collection->addFieldToFilter('price', $price);
                break;
            case Amasty_Mostviewed_Model_Source_Condition_Price::LESS:
                $collection->addFieldToFilter('price', array('lt'=>$price));
                break;
            case Amasty_Mostviewed_Model_Source_Condition_Price::MORE:
                $collection->addFieldToFilter('price', array('gt'=>$price));
                break;
        }

        return $this;
    }

//    protected function _ifExistShowCategory($collection)
//    {
//        if ($categoryIds = Mage::getStoreConfig('ammostviewed/general/show_only_exist')) {
//            $categoryIds = explode(',', $categoryIds);
//            $tempSelect = clone $collection->getSelect();
//            $tempSelect->where('at_category_id.category_id IN (?)', $categoryIds);
//            $result = $collection->getConnection()->fetchAll($tempSelect);
//            if (is_array($result) && count($result) > 0) {
//                $collection->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left');
//                $collection->addAttributeToFilter('category_id', array('in' => $categoryIds));
//            }
//        }
//    }

    protected function _showOnlyCategories($collection)
    {
        if ($categoryIds = Mage::getStoreConfig('ammostviewed/general/show_only')) {
            $categoryIds = explode(',', $categoryIds);
            if (!$this->joinedCategoryField) {
                $collection->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left');
            }
            $collection->addAttributeToFilter('category_id', array('in' => $categoryIds));
        }
    }
}
