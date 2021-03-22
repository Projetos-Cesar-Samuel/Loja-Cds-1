<?php

    class Webkul_MobiKul_Block_Adminhtml_Featuredcategories_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("featuredcategories_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle($this->__("Featured Category Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => $this->__("Featured Category Information"),
                "alt"       => $this->__("Featured Category information"),
                "content"   => $this->getLayout()->createBlock("mobikul/adminhtml_featuredcategories_edit_tab_form")->toHtml()
            ));
            $this->addTab('categories', array(
                "label"     => Mage::helper("catalog")->__("Categories"),
                "content"   => $this->getLayout()->createBlock("mobikul/adminhtml_featuredcategories_edit_tab_categories")->toHtml()
            ));
            return parent::_beforeToHtml();
        }

    }