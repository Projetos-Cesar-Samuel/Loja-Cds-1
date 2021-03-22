<?php

    require_once "Mage/Adminhtml/Block/Widget/Grid.php";
    class Webkul_MobiKul_Block_Adminhtml_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid {

        public function addColumn($columnId, $column) {
            if(is_array($column)){
                $this->_columns[$columnId] = $this->getLayout()->createBlock("mobikul/adminhtml_widget_grid_column")
                    ->setData($column)
                    ->setGrid($this);
            }
            else
               throw new Exception($this->__("Wrong column format"));
            $this->_columns[$columnId]->setId($columnId);
            $this->_lastColumnId = $columnId;
            return $this;
        }

    }