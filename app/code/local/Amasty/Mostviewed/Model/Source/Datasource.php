<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Datasource
{
    const SOURCE_VIEWED = 0;
    const SOURCE_BOUGHT = 1;
    const SOURCE_PRODUCT = 2;

    public function toOptionArray()
    {
        $hlp = Mage::helper('ammostviewed');
        return array(
            array(
                'value' => self::SOURCE_VIEWED,
                'label' => $hlp->__('Viewed together')
            ),
            array(
                'value' => self::SOURCE_BOUGHT,
                'label' => $hlp->__('Bought together')
            ),
            array(
                'value' => self::SOURCE_PRODUCT,
                'label' => $hlp->__('Product currently viewed')
            )
        );
    }
}