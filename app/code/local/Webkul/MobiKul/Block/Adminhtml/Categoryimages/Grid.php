<?php

	class Webkul_MobiKul_Block_Adminhtml_Categoryimages_Grid extends Webkul_MobiKul_Block_Adminhtml_Widget_Grid {

		public function __construct() {
			parent::__construct();
			$this->setId("categoryimagesGrid");
			$this->setDefaultSort("id");
			$this->setDefaultDir("ASC");
			$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection() {
			$collection = Mage::getModel("mobikul/categoryimages")->getCollection();
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

			$this->addColumn("banner", array(
				"header"    => $this->__("Banner Image"),
				"align"     => "center",
				"index"     => "banner",
				"type"      => "bannerimage",
				"escape"    => true,
				"filter"    => false,
				"sortable"  => false
			));

			$this->addColumn("icon", array(
				"header"    => $this->__("Icon Image"),
				"align"     => "center",
				"index"     => "icon",
				"type"      => "bannerimage",
				"escape"    => true,
				"filter"    => false,
				"sortable"  => false
			));

			$this->addColumn("category_id", array(
				"header"    => $this->__("Category Id"),
				"index"     => "category_id",
				"align"     => "left"
			));

			$this->addColumn("category_name", array(
				"header"    => $this->__("Category Name"),
				"index"     => "category_name",
				"align"     => "left"
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
			$this->getMassactionBlock()->setFormFieldName("categoryIds");
			$this->getMassactionBlock()->addItem("delete", array(
				"label"     => $this->__("Delete"),
				"url"       => $this->getUrl("*/*/massDelete"),
				"confirm"   => $this->__("Are you sure?")
			));
			
			return $this;
		}

		public function getRowUrl($row) {
			return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}

	}