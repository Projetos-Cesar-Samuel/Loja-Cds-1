<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Condition_Category
{
    const SAME_CATEGORY_ANY     = 0;
    const SAME_CATEGORY_ONLY    = 1;
    const SAME_CATEGORY_EXCLUDE = 2;

    public function toOptionArray()
    {
        $hlp = Mage::helper('ammostviewed');
        return array(
            array(
                'value' => self::SAME_CATEGORY_ANY,
                'label' => $hlp->__('Any')
            ),
            array(
                'value' => self::SAME_CATEGORY_ONLY,
                'label' => $hlp->__('Only')
            ),
            array(
                'value' => self::SAME_CATEGORY_EXCLUDE,
                'label' => $hlp->__('Exclude From Result')
            ),
        );
    }
}