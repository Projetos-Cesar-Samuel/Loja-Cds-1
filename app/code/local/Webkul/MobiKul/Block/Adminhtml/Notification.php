<?php

	class Webkul_MobiKul_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_notification";
	        $this->_blockGroup = "mobikul";
	        $this->_headerText = $this->__("Notification Manager");
	        $this->_addButtonLabel = $this->__("Add Notification");
	        parent::__construct();
	    }

	}