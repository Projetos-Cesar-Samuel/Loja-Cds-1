<?php

/**
 * Dimension source model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Dimension_Source extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    /**
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (null === $this->_options) {
            $this->_options = Mage::getResourceModel('ave_sizechart/dimension_collection')
                                                    ->addAttributeToSelect('name')
                                                    ->addAttributeToFilter('status', array('in' => 1))
                                                    ->setOrder('position')
                                                    ->load()
                                                    ->toOptionArray();
        }

        $storeId = $this->getAttribute()->getStoreId();
        $options = ($defaultValues && $storeId) ? $this->_optionsDefault[$storeId] : $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label'=>''));
        }

        return $options;
    }

    /**
     * @param bool $withEmpty
     * @return string
     */
    public function getOptionsArray($withEmpty = true)
    {
        $options = array();
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }
}
