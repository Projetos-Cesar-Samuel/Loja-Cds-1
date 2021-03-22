<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Header extends Mage_Adminhtml_Block_Widget_Grid_Container
{	
	public function __construct()
	{
		$helper = Mage::helper("flexibletheme");
		$this->_blockGroup = "flexibletheme";
		$this->_controller = "adminhtml_header";
		$this->_headerText = $helper->__("Manage Headers");
		$this->_addButtonLabel = $helper->__("Add New Header");
		parent::__construct();
	}
}