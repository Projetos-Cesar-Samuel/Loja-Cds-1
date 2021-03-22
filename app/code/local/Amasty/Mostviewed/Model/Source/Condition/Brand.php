<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Condition_Brand
{
    const ANY       = 0;
    const SAME_AS   = 1;

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
        );
    }
}