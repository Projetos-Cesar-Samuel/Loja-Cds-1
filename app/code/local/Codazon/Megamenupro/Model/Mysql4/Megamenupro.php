<?php
class Codazon_Megamenupro_Model_Mysql4_Megamenupro extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("megamenupro/megamenupro", "menu_id");
    }
}