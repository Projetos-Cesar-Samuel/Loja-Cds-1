<?php

    class Webkul_MobiKul_Block_Adminhtml_Featuredcategories_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

        public function __construct() {
            parent::__construct();
            $this->_objectId = "id";
            $this->_blockGroup = "mobikul";
            $this->_controller = "adminhtml_featuredcategories";
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
                $script .= "if($('filename_image') == undefined){
                    $('filename').addClassName('required-entry');
                }
                document.observe('click', function(event) {
                    var element = Event.element(event);
                    if ('filename_delete' == element.readAttribute('id')){
                        var checked = element.checked;
                        if(checked)
                            $('filename').addClassName('required-entry');
                        else
                            $('filename').removeClassName('required-entry');
                    }
                });";
            }
            else{
                $script .= "$('filename').addClassName('required-entry');";
            }
            $this->_formScripts[] = $script;
        }

        public function getHeaderText() {
            if(Mage::registry("featuredcategories_data") && Mage::registry("featuredcategories_data")->getId())
                return $this->__("Edit Item with id '%s'", $this->htmlEscape(Mage::registry("featuredcategories_data")->getId()));
            else
                return $this->__("Create Featured Category");
        }

    }