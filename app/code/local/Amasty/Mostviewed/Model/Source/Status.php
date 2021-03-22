<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Status
{
    /**
     * @var array
     */
    protected $optionArray = array();


    public function __construct()
    {
        $stats = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach ($stats as $code => $label) {
            $this->optionArray[] = array(
                'value' => $code,
                'label' => $label,
            );
        }
    }

    public function toOptionArray()
    {
        return $this->optionArray;
    }
}