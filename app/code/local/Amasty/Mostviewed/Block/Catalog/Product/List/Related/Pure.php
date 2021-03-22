<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


if (Mage::getEdition() == Mage::EDITION_ENTERPRISE) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Amasty_Mostviewed_Block_Catalog_Product_List_Related_Enterprise');
} else {
    class Amasty_Mostviewed_Block_Catalog_Product_List_Related_Pure extends Mage_Catalog_Block_Product_List_Related {}
}
