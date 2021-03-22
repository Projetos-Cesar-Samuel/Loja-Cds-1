<?php

    class Webkul_MobiKul_Block_Adminhtml_Notification_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid    {

        public function __construct()    {
            parent::__construct();
            $this->setId("notification_products");
            $this->setDefaultSort("entity_id");
            $this->setUseAjax(true);
        }

        protected function _addColumnFilterToCollection($column)        {
            // Set custom filter for in category flag
            if ($column->getId() == "in_category") {
                $productIds = $this->_getSelectedProducts();
                if (empty($productIds)) {
                    $productIds = 0;
                }
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter("entity_id", array("in"=>$productIds));
                }
                elseif(!empty($productIds)) {
                    $this->getCollection()->addFieldToFilter("entity_id", array("nin"=>$productIds));
                }
            }
            else {
                parent::_addColumnFilterToCollection($column);
            }
            return $this;
        }

        protected function _prepareCollection()        {
            $collection = Mage::getModel("catalog/product")->getCollection()
                ->addAttributeToSelect("name")
                ->addAttributeToSelect("sku")
                ->addAttributeToSelect("price")
                ->addStoreFilter($this->getRequest()->getParam("store"));
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns()       {
            $this->addColumn("in_category", array(
                "header_css_class" => "a-center",
                "type"      => "checkbox",
                "name"      => "in_category",
                "values"    => $this->_getSelectedProducts(),
                "align"     => "center",
                "index"     => "entity_id"
            ));
            $this->addColumn("entity_id", array(
                "header"    => Mage::helper("catalog")->__("ID"),
                "sortable"  => true,
                "width"     => "60",
                "index"     => "entity_id"
            ));
            $this->addColumn("name", array(
                "header"    => Mage::helper("catalog")->__("Name"),
                "index"     => "name"
            ));
            $this->addColumn("sku", array(
                "header"    => Mage::helper("catalog")->__("SKU"),
                "width"     => "80",
                "index"     => "sku"
            ));
            $this->addColumn("price", array(
                "header"    => Mage::helper("catalog")->__("Price"),
                "type"      => "currency",
                "width"     => "1",
                "currency_code" => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                "index"     => "price"
            ));
            return parent::_prepareColumns();
        }

        public function getGridUrl()        {
            return $this->getUrl("*/*/grid", array("_current"=>true));
        }

        protected function _getSelectedProducts()        {
            $id = Mage::registry("id");
            if($id != ""){
                $notification = Mage::getModel("mobikul/notification")->load($id);
                if($notification->getId() > 0){
                    $filterData = unserialize($notification->getFilterData());
                    $productIds = explode(",", $filterData);
                    return $productIds;
                }
                else
                    return array();
            }
            else
                return array();
        }

    }