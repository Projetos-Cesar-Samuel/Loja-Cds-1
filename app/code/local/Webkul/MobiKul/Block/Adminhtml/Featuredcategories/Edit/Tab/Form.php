<?php

	class Webkul_MobiKul_Block_Adminhtml_Featuredcategories_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

		protected function _prepareForm() {
			$form = new Varien_Data_Form();
			$this->setForm($form);
			$fieldset = $form->addFieldset("Featuredcategories_form", array("legend" => $this->__("Featured Category Information")));

			$fieldset->addField("filename", "image", array(
				"label"     => $this->__("Image"),
				"name"      => "filename",
				"class"     => "required-entry",
				"required"  => true
			));

			$fieldset->addField("sort_order", "text", array(
				"label"     => $this->__("Sort Order"),
				"name"      => "sort_order",
				"class"     => "required-entry",
				"required"  => true
			));

			$fieldset->addField("store_id", "multiselect", array(
				"name"      => "store_id",
				"label"     => $this->__("Select Store"),
				"id"        => "main_category",
				"title"     => $this->__("Select Store"),
				"class"     => "input-select required-entry",
				"required"  => true,
				"values"    => Mage::helper("mobikul")->getStoreIds()
			));

			$fieldset->addField("status", "select", array(
				"label"     => $this->__("Status"),
				"class"     => "required-entry",
				"name"      => "status",
				"values"    => array(
					array(
						"value" => 1,
						"label" => $this->__("Enabled")
					),
					array(
						"value" => 2,
						"label" => $this->__("Disabled")
					)
				)
			));

			if(Mage::getSingleton("adminhtml/session")->getFeaturedcategoriesData()) {
				$form->setValues(Mage::getSingleton("adminhtml/session")->getFeaturedcategoriesData());
				Mage::getSingleton("adminhtml/session")->setFeaturedcategoriesData(null);
			}
			else
			if(Mage::registry("featuredcategories_data"))
				$form->setValues(Mage::registry("featuredcategories_data")->getData());
			return parent::_prepareForm();
		}

	}