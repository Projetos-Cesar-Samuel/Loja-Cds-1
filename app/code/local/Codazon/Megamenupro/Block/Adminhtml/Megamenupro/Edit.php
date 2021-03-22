<?php
	
class Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "menu_id";
				$this->_blockGroup = "megamenupro";
				$this->_controller = "adminhtml_megamenupro";
				$this->_updateButton("save", "label", Mage::helper("megamenupro")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("megamenupro")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("megamenupro")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);
				if( Mage::registry("megamenupro_data") && Mage::registry("megamenupro_data")->getId() ){
					$this->_addButton("saveandduplicate", array(
						"label"     => Mage::helper("megamenupro")->__("Save And Duplicate"),
						"onclick"   => "saveAndDuplicate()",
						"class"     => "save",
					), -200);
				}


				$this->_formScripts[] = "
							function saveAndDuplicate(){
								editForm.submit($('edit_form').action+'duplicate/1');
							}
							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
			
		}

		public function getHeaderText()
		{
				if( Mage::registry("megamenupro_data") && Mage::registry("megamenupro_data")->getId() ){

				    return Mage::helper("megamenupro")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("megamenupro_data")->getTitle()));

				} 
				else{

				     return Mage::helper("megamenupro")->__("Add Item");

				}
		}
}