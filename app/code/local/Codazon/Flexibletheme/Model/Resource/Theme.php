<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Resource_Theme extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("flexibletheme/theme", "theme_id");
    }
}