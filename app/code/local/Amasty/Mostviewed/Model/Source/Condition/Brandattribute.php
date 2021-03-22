<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Model_Source_Condition_Brandattribute
{
    /**
     * @var array
     */
    protected $optionArray = array(array('value'=>'', 'label'=>''));

    public function __construct()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->getItems();

        foreach ($attributes as $attribute){
            if($attribute->getIsVisibleOnFront()) {
                $this->optionArray[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }
        }
    }

    public function toOptionArray()
    {
        return $this->optionArray;
    }
}