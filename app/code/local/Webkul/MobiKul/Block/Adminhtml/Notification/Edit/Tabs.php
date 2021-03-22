<?php

    class Webkul_MobiKul_Block_Adminhtml_Notification_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("notification_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle($this->__("Notification Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => $this->__("Notification Information"),
                "alt"       => $this->__("Notification information"),
                "content"   => $this->getLayout()->createBlock("mobikul/adminhtml_notification_edit_tab_form")->toHtml()
            ));
            $this->addTab("customCollection_section", array(
                "label"     => $this->__("Custom Collection Builder"),
                "alt"       => $this->__("Custom Collection Builder"),
                "content"   => $this->getLayout()->createBlock("mobikul/adminhtml_notification_edit_tab_conditions")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }