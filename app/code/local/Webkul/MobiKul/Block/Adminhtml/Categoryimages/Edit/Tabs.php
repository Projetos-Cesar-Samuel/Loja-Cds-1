<?php

	class Webkul_MobiKul_Block_Adminhtml_Categoryimages_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

		public function __construct() {
			parent::__construct();
			$this->setId("categoryimages_tabs");
			$this->setDestElementId("edit_form");
			$this->setTitle($this->__("Category Image Information"));
		}

		protected function _beforeToHtml() {
			$this->addTab("form_section", array(
				"label"     => $this->__("Category Image Information"),
				"alt"       => $this->__("Category Image Information"),
				"content"   => $this->getLayout()->createBlock("mobikul/adminhtml_categoryimages_edit_tab_form")->toHtml()
			));
			$this->addTab("categories", array(
				"label"     => Mage::helper("catalog")->__("Categories"),
				"content"   => $this->getLayout()->createBlock("mobikul/adminhtml_categoryimages_edit_tab_categories")->toHtml()
			));
			return parent::_beforeToHtml();
		}

	}