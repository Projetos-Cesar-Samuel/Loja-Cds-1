<?php

    class Webkul_MobiKul_Model_Category_Api extends Mage_Catalog_Model_Category_Api    {

        public function tree($parentId = null, $store = null)    {
            if(is_null($parentId) && !is_null($store))
                $parentId = Mage::app()->getStore($this->_getStoreId($store))->getRootCategoryId();
            else
            if(is_null($parentId))
                $parentId = 1;
            /* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
            $tree = Mage::getResourceSingleton("catalog/category_tree")->load();
            $root = $tree->getNodeById($parentId);
            if($root && $root->getId() == 1)
                $root->setName(Mage::helper("catalog")->__("Root"));
            $collection = Mage::getModel("catalog/category")->getCollection()
                            ->setStoreId($this->_getStoreId($store))
                            ->addAttributeToSelect("name")
                            ->addAttributeToSelect("include_in_menu")
                            ->addAttributeToSelect("is_active")
                            ->addAttributeToFilter("include_in_menu", 1)
                            ->addAttributeToFilter("is_active", 1);
            $tree->addCollectionData($collection, true);
            return $this->_nodeToArray($root);
        }

    }