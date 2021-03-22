<?php

	class Webkul_MobiKul_Block_Adminhtml_Categoryimages_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

		protected function _prepareForm() {
			$form = new Varien_Data_Form();
			$this->setForm($form);
			$fieldset = $form->addFieldset("categoryimages_form", array("legend" => $this->__("Category Image Information")));

			$fieldset->addField("banner", "image", array(
				"label"     => $this->__("Banner"),
				"name"      => "banner",
				"class"     => "required-entry",
				"required"  => true,
				"after_element_html" => "<br><b style='color:#d40707;'>not valid if empty</b>"
			));

			$fieldset->addField("icon", "image", array(
				"label"     => $this->__("Icon"),
				"name"      => "icon",
				"class"     => "required-entry",
				"required"  => true,
				"after_element_html" => "<br><b style='color:#d40707;'>not valid if empty</b>"
			));

			if(Mage::getSingleton("adminhtml/session")->getCategoryimagesData()) {
				$form->setValues(Mage::getSingleton("adminhtml/session")->getCategoryimagesData());
				Mage::getSingleton("adminhtml/session")->setCategoryimagesData(null);
			}
			else
			if(Mage::registry("categoryimages_data"))
				$form->setValues(Mage::registry("categoryimages_data")->getData());
			return parent::_prepareForm();
		}

	}