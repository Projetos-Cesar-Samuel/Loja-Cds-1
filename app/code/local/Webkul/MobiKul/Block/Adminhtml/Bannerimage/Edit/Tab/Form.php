<?php

    class Webkul_MobiKul_Block_Adminhtml_Bannerimage_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

        protected function _prepareForm() {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset("bannerimage_form", array("legend" => $this->__("Banner Image Information")));

            $fieldset->addField("filename", "image", array(
                "label"     => $this->__("Image"),
                "required"  => false,
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

            $fieldset->addField("type", "select", array(
                "label"     => $this->__("Banner Type"),
                "name"      => "type",
                "class"     => "required-entry",
                "required"  => true,
                "values"    => array(
                    array(
                        "value" => "product",
                        "label" => $this->__("Product")
                    ),
                    array(
                        "value" => "category",
                        "label" => $this->__("Category")
                    )
                )
            ));

            $fieldset->addField("pro_cat_id", "text", array(
                "label"     => $this->__("Product/Category Id"),
                "class"     => "required-entry",
                "required"  => true,
                "name"      => "pro_cat_id"
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

            if(Mage::getSingleton("adminhtml/session")->getBannerimageData()) {
                $form->setValues(Mage::getSingleton("adminhtml/session")->getBannerimageData());
                Mage::getSingleton("adminhtml/session")->setBannerimageData(null);
            }
            else
            if(Mage::registry("bannerimage_data"))
                $form->setValues(Mage::registry("bannerimage_data")->getData());
            return parent::_prepareForm();
        }

    }