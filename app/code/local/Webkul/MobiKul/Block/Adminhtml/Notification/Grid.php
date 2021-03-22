<?php
 
    class Webkul_MobiKul_Block_Adminhtml_Notification_Grid extends Webkul_MobiKul_Block_Adminhtml_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("notificationGrid");
            $this->setDefaultSort("id");
            $this->setDefaultDir("ASC");
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("mobikul/notification")->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns() {
            $this->addColumn("id", array(
                "header"    => $this->__("ID"),
                "align"     => "center",
                "width"     => "30px",
                "index"     => "id"
            ));

            $this->addColumn("filename", array(
                "header"    => $this->__("Banner"),
                "align"     => "center",
                "index"     => "filename",
                "type"      => "bannerimage",
                "escape"    => true,
                "filter"    => false,
                "sortable"  => false
            ));

            $this->addColumn("title", array(
                "header"    => $this->__("Notification Title"),
                "align"     => "left",
                "index"     => "title"
            ));

            $this->addColumn("content", array(
                "header"    => $this->__("Notification Content"),
                "index"     => "content",
                "align"     => "center"
            ));

            $this->addColumn("type", array(
                "header"    => $this->__("Notification Type"),
                "index"     => "type",
                "type"      => "options",
                "align"     => "center",
                "options"   => array(
                        "product"  => "Product",
                        "category" => "Category",
                        "other"    => "Others",
                        "custom"   => "Custom Collection"
                    )
            ));

            $this->addColumn("store_id", array(
                "header"    => $this->__("Store Id"),
                "index"     => "store_id",
                "type"      => "options",
                "align"     => "center",
                "options"   => Mage::helper("mobikul")->getStoreforGrid()
            ));

            $this->addColumn("status", array(
                "header"    => $this->__("Status"),
                "align"     => "left",
                "index"     => "status",
                "type"      => "options",
                "options"   => array(1 => "Enabled",2 => "Disabled")
            ));

            $this->addColumn("action", array(
                "header"    => $this->__("Action"),
                "width"     => "80",
                "type"      => "action",
                "getter"    => "getId",
                "actions"   => array(array(
                        "caption"   => $this->__("Edit"),
                        "url"       => array("base" => "*/*/edit"),
                        "field"     => "id")),
                "filter"    => false,
                "sortable"  => false,
                "index"     => "stores",
                "is_system" => true
            ));

            $this->addExportType("*/*/exportCsv", $this->__("CSV"));
            $this->addExportType("*/*/exportXml", $this->__("XML"));
            return parent::_prepareColumns();
        }

        protected function _prepareMassaction() {
            $this->setMassactionIdField("id");
            $this->getMassactionBlock()->setFormFieldName("notifications");
            $this->getMassactionBlock()->addItem("delete", array(
                "label"     => $this->__("Delete"),
                "url"       => $this->getUrl("*/*/massDelete"),
                "confirm"   => $this->__("Are you sure?")
            ));
            $this->getMassactionBlock()->addItem("push", array(
                "label"     => $this->__("Push"),
                "url"       => $this->getUrl("*/*/masspush"),
                "confirm"   => $this->__("Are you sure?")
            ));
            $this->getMassactionBlock()->addItem("status", array(
                "label"     => $this->__("Change status"),
                "url"       => $this->getUrl("*/*/massStatus", array("_current" => true)),
                "additional"=> array(  "visibility"=> array(
                                        "name"      => "status",
                                        "type"      => "select",
                                        "class"     => "required-entry",
                                        "label"     => $this->__("Status"),
                                        "values"    => array(1=>"Enabled", 2=>"Disabled")))
            ));
            return $this;
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

    }