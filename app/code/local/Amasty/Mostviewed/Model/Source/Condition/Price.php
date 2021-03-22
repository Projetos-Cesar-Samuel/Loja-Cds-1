<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Condition_Price
{
    const ANY       = 0;
    const SAME_AS   = 1;
    const MORE      = 2;
    const LESS      = 3;

    public function toOptionArray()
    {
        $hlp = Mage::helper('ammostviewed');
        return array(
            array(
                'value' => self::ANY,
                'label' => $hlp->__('Any')
            ),
            array(
                'value' => self::SAME_AS,
                'label' => $hlp->__('Same as')
            ),
            array(
                'value' => self::MORE,
                'label' => $hlp->__('More')
            ),
            array(
                'value' => self::LESS,
                'label' => $hlp->__('Less')
            ),
        );
    }
}