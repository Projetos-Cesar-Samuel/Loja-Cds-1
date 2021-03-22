<?php

/**
 * Admin source model for Product Category
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Chart_Attribute_Source_Productcategory extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    /**
     * get possible values
     *
     * @access public
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     * @author averun <dev@averun.com>
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (null === $this->_options) {
            $this->_options = $this->getCategoriesArray();
        }

        $storeId = $this->getAttribute()->getStoreId();
        $options = ($defaultValues && $storeId) ? $this->_optionsDefault[$storeId] : $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label'=>''));
        }

        return $options;
    }

    /**
     * get options as array
     *
     * @access public
     * @param bool $withEmpty
     * @return array
     * @author averun <dev@averun.com>
     */
    public function getOptionsArray($withEmpty = true)
    {
        $options = array();
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }

    public function getCategoriesArray()
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('position')
            ->addAttributeToFilter('is_active','1')
            ->addAttributeToSort('path', 'asc');
        $categoriesArray = $collection->load()->toArray();
        $categories = array();
        foreach ($categoriesArray as $category) {
            if (isset($category['name']) && isset($category['level'])) {
                $multiplier = ($category['level'] - 1) * 4;
                $categories[] = array(
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $category['entity_id'],
                    'style' => 'padding-left: ' . 4 * $multiplier . 'px'
                );
            }
        }

        return $categories;
    }
}
