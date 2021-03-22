<?php


class Codazon_Megamenupro_Block_Adminhtml_Megamenupro extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

		$this->_controller = "adminhtml_megamenupro";
		$this->_blockGroup = "megamenupro";
		$this->_headerText = Mage::helper("megamenupro")->__("Megamenupro Manager");
		$this->_addButtonLabel = Mage::helper("megamenupro")->__("Add New Item");
		parent::__construct();
	
	}

}