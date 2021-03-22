<?php

	class Webkul_MobiKul_Block_Adminhtml_Categoryimages_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

		public function __construct() {
			parent::__construct();
			$this->_objectId = "id";
			$this->_blockGroup = "mobikul";
			$this->_controller = "adminhtml_categoryimages";
			$this->_updateButton("save", "label", $this->__("Save Category"));
			$this->_updateButton("delete", "label", $this->__("Delete Category"));
			$this->_addButton("saveandcontinue", array(
				"label"     => $this->__("Save And Continue Edit"),
				"onclick"   => "saveAndContinueEdit()",
				"class"     => "save"
			), -100);
			$script = "
                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }";
            if($this->getRequest()->getParam("id")){
                $script .= "if($('banner_image') == undefined){
                    $('banner').addClassName('required-entry');
                }
                document.observe('click', function(event) {
                    var element = Event.element(event);
                    if ('banner_delete' == element.readAttribute('id')){
                        var checked = element.checked;
                        if(checked)
                            $('banner').addClassName('required-entry');
                        else
                            $('banner').removeClassName('required-entry');
                    }
                });
                if($('icon_image') == undefined){
                    $('icon').addClassName('required-entry');
                }
                document.observe('click', function(event) {
                    var element = Event.element(event);
                    if ('icon_delete' == element.readAttribute('id')){
                        var checked = element.checked;
                        if(checked)
                            $('icon').addClassName('required-entry');
                        else
                            $('icon').removeClassName('required-entry');
                    }
                });";
            }
            else{
                $script .= "$('banner').addClassName('required-entry');$('icon').addClassName('required-entry');";
            }
            $this->_formScripts[] = $script;
		}

		public function getHeaderText() {
			if(Mage::registry("categoryimages_data") && Mage::registry("categoryimages_data")->getId())
				return $this->__("Edit Category '%s (%d)'", $this->htmlEscape(Mage::registry("categoryimages_data")->getCategoryName()), Mage::registry("categoryimages_data")->getId());
			else
				return $this->__("Add Category Image");
		}

	}