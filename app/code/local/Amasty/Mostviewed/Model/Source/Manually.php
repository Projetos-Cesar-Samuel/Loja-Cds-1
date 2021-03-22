<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Manually
{
    const NOTHING = 0;
    const REPLACE = 1;
    const APPEND  = 2;

    public function toOptionArray()
    {
        $hlp = Mage::helper('ammostviewed');
        return array(
            array(
                'value' => self::NOTHING,
                'label' => $hlp->__('Display Manually Added Products Only')
            ),
            array(
                'value' => self::REPLACE,
                'label' => $hlp->__('Replace Manually Added Products')
            ),
            array(
                'value' => self::APPEND,
                'label' => $hlp->__('Append to Manually Added Products')
            ),
        );
    }
}