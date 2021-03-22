<?php

    require_once "Mage/Adminhtml/Block/Widget/Grid/Column/Renderer/Action.php";
    class Webkul_MobiKul_Block_Adminhtml_Widget_Grid_Column_Renderer_Bannerimage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

        public function __construct() {}

        public function render(Varien_Object $row) {
            return $this->_getValue($row);
        }

        protected function _getValue(Varien_Object $row) {
            $dored = false;
            $out = "";
            if($getter = $this->getColumn()->getGetter())
                $val = $row->$getter();
            $val = $row->getData($this->getColumn()->getIndex());
            $url = Mage::getBaseUrl("media").$val;
            list($width, $height, $type, $attr) = getimagesize(Mage::getBaseDir("media").DS.$val);
            $a_height = (int) ((100 / $width) * $height);
            $size = Array("width" => 100, "height" => $a_height);
            $popLink = "popWin('$url', 'image', 'width=800, height=600, resizable=yes, scrollbars=yes')";
            if(is_array($size))
                $out = '<a href="javascript:;" onclick="'.$popLink.'"><img src="'.$url.'" width="'.$size['width'].'" height="'.$size['height'].'" style="border:2px solid #CCCCCC;"/></a>';
            return $out;
        }

    }