<?php

    class Webkul_MobiKul_Block_Adminhtml_Bannerimage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("bannerimage_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle($this->__("Banner Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => $this->__("Banner Information"),
                "alt"       => $this->__("Banner information"),
                "content"   => $this->getLayout()->createBlock("mobikul/adminhtml_bannerimage_edit_tab_form")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }