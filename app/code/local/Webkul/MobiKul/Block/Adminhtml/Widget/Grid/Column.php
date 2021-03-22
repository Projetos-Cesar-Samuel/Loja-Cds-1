<?php

    require_once "Mage/Adminhtml/Block/Widget/Grid/Column.php";
    class Webkul_MobiKul_Block_Adminhtml_Widget_Grid_Column extends Mage_Adminhtml_Block_Widget_Grid_Column {

        protected function _getRendererByType() {
            switch(strtolower($this->getType())) {
                case "bannerimage":
                    $rendererClass = "mobikul/adminhtml_widget_grid_column_renderer_bannerimage";
                    break;
                default:
                    $rendererClass = parent::_getRendererByType();
                    break;
            }
            return $rendererClass;
        }

        protected function _getFilterByType() {
            switch(strtolower($this->getType())) {
                case "bannerimage":
                    $filterClass = "mobikul/adminhtml_widget_grid_column_filter_bannerimage";
                    break;
                default:
                    $filterClass = parent::_getFilterByType();
                    break;
            }
            return $filterClass;
        }

    }