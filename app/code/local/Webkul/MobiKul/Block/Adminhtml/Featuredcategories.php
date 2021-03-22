<?php

	class Webkul_MobiKul_Block_Adminhtml_Featuredcategories extends Mage_Adminhtml_Block_Widget_Grid_Container {

		public function __construct() {
			$this->_controller = "adminhtml_featuredcategories";
			$this->_blockGroup = "mobikul";
			$this->_headerText = $this->__("Featured Categories Manager");
			$this->_addButtonLabel = $this->__("Add Featured Categories");
			parent::__construct();
		}

	}