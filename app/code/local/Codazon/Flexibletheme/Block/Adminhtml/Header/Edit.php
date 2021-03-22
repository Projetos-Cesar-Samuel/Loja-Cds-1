<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Header_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
        $this->setTemplate('codazon_flexibletheme/edit/form/container.phtml');

		$this->_objectId = "entity_id";
		$this->_blockGroup = "flexibletheme";
		$this->_controller = "adminhtml_header";
		$this->_updateButton("save", "label", Mage::helper("flexibletheme")->__("Save"));
		$this->_updateButton("delete", "label", Mage::helper("flexibletheme")->__("Delete"));
        $this->_removeButton("reset");
        $this->_addButton("saveandcontinue", array(
			"label"     => Mage::helper("flexibletheme")->__("Save & Continue Edit"),
			"onclick"   => "saveAndContinueEdit()",
			"class"     => "save",
		), -100);
		
        if (Mage::registry('flexibletheme_data')->getId()) {
            $this->_addButton("reset", array(
                "label"     => Mage::helper("flexibletheme")->__("Reset to default"),
                "onclick"   => "resetToDefault()",
                "class"     => "reset",
            ), 0);
            $this->_addButton("export_as_default", array(
                "label"     => Mage::helper("flexibletheme")->__("Save & Export To Default"),
                "onclick"   => "exportAsDefault()",
                "class"     => "export",
            ), 0);
        }
        
        $exportText = Mage::helper("flexibletheme")->__("Default values of all fields will be replaced by current values. Are your sure?");
        $resetText = Mage::helper("flexibletheme")->__("Are your sure to reset all fields to default values?");
        
		$this->_formScripts[] = "
			function resetToDefault(){
                if (confirm('{$resetText}')) {
                    editForm.submit($('edit_form').action + 'reset/1/back/edit');
                }
			}
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action + 'back/edit/');
			}
            function exportAsDefault(){
                if (confirm('{$exportText}')) {
                    editForm.submit($('edit_form').action + 'export/1/back/edit');
                }
			}
		";
	}
    
    public function getHeaderText()
    {
        if (Mage::registry('flexibletheme_data')->getId()) {
            return $this->escapeHtml(Mage::registry('flexibletheme_data')->getData('title'));
        } else {
            return Mage::helper('flexibletheme')->__('New Header');
        }
    }
}