<?php

	class Webkul_MobiKul_Block_Adminhtml_Notification_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

		public function __construct() {
			parent::__construct();
			$this->_objectId = "id";
			$this->_blockGroup = "mobikul";
			$this->_controller = "adminhtml_notification";
			$this->_removeButton("reset");
			$this->_updateButton("save", "label", $this->__("Save Notification"));
			$this->_updateButton("save", "onclick", "save()");
			$this->_updateButton("delete", "label", $this->__("Delete Notification"));
			$this->_addButton("saveandcontinue", array(
				"label"     => $this->__("Save And Continue Edit"),
				"onclick"   => "saveAndContinueEdit()",
				"class"     => "save"
			), -100);
			$this->_addButton("push", array(
				"label"     => $this->__("Push"),
				"onclick"   => "push()",
				"class"     => "save"
			), -100);
			$script = "
                function saveAndContinueEdit(){
					if($('push') != undefined)
						$('push').remove();
					editForm.submit($('edit_form').action+'back/edit/');
				}
				function save(){
					if($('push') != undefined)
						$('push').remove();
					editForm.submit();
				}
				function push(){
					if($('push') != undefined)
						$('push').remove();
					$('edit_form').insert(\"<input id='push' type='hidden' name='push' value='push'/>\");
					editForm.submit();
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
			if(Mage::registry("notification_data") && Mage::registry("notification_data")->getId())
				return $this->__("Edit Notification with id '%s'", $this->htmlEscape(Mage::registry("notification_data")->getId()));
			else
				return $this->__("Add Notification");
		}

	}