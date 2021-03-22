<?php

	class Webkul_MobiKul_Block_Adminhtml_Categoryimages extends Mage_Adminhtml_Block_Widget_Grid_Container {

		public function __construct() {
			$this->_controller = "adminhtml_categoryimages";
			$this->_blockGroup = "mobikul";
			$this->_headerText = $this->__("Add/Manage Category's Banners n Images");
			$this->_addButtonLabel = $this->__("Add Images to Category");
			parent::__construct();
		}

	}