<?php

	class Webkul_MobiKul_Block_Adminhtml_Featuredcategories_Grid extends Webkul_MobiKul_Block_Adminhtml_Widget_Grid {

		public function __construct() {
			parent::__construct();
			$this->setId("bannerGrid");
			$this->setDefaultSort("id");
			$this->setDefaultDir("ASC");
			$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection() {
			$collection = Mage::getModel("mobikul/featuredcategories")->getCollection();
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
				"header"    => $this->__("Image"),
				"align"     => "center",
				"index"     => "filename",
				"type"      => "bannerimage",
				"escape"    => true,
				"filter"    => false,
				"sortable"  => false
			));

			$this->addColumn("sort_order", array(
				"header"    => $this->__("Sort Order"),
				"index"     => "sort_order",
				"align"     => "center"
			));

			$this->addColumn("category_id", array(
				"header"    => $this->__("Category Id"),
				"index"     => "category_id",
				"align"     => "center"
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
			$this->getMassactionBlock()->setFormFieldName("banners");
			$this->getMassactionBlock()->addItem("delete", array(
				"label"     => $this->__("Delete"),
				"url"       => $this->getUrl("*/*/massDelete"),
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