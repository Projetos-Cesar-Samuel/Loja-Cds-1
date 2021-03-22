<?php

	class Webkul_MobiKul_Block_Adminhtml_Bannerimage extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_bannerimage";
	        $this->_blockGroup = "mobikul";
	        $this->_headerText = $this->__("Banner Image Manager");
	        $this->_addButtonLabel = $this->__("Add Images");
	        parent::__construct();
	    }

	}