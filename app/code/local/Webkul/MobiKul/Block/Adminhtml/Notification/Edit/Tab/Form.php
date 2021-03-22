<?php

    class Webkul_MobiKul_Block_Adminhtml_Notification_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

        protected function _prepareForm() {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset("notification_form", array("legend" => $this->__("Notification Information")));

            $fieldset->addField("title", "text", array(
                "label"     => $this->__("Notification Title"),
                "name"      => "title",
                "class"     => "required-entry",
                "required"  => true
            ));

            $fieldset->addField("content", "textarea", array(
                "label"     => $this->__("Notification Content"),
                "name"      => "content",
    			"class"     => "required-entry",
                "required"  => true
            ));

            $fieldset->addField("type", "select", array(
                "label"     => $this->__("Notification Type"),
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
                    ),
                    array(
                        "value" => "other",
                        "label" => $this->__("Others")
                    )
                    ,
                    array(
                        "value" => "custom",
                        "label" => $this->__("Custom Collection")
                    )
                )
            ));

            $fieldset->addField("filename", "image", array(
                "label"     => $this->__("Notification Banner"),
                "name"      => "filename"
            ));

            $fieldset->addField("pro_cat_id", "text", array(
                "label"     => $this->__("Product/Category Id"),
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

            if(Mage::getSingleton("adminhtml/session")->getNotificationData()) {
                $form->setValues(Mage::getSingleton("adminhtml/session")->getNotificationData());
                Mage::getSingleton("adminhtml/session")->setNotificationData(null);
            }
            else
            if(Mage::registry("notification_data"))
                $form->setValues(Mage::registry("notification_data")->getData());
            return parent::_prepareForm();
        }

    }