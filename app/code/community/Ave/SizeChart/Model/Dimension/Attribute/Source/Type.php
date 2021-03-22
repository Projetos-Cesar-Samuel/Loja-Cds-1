<?php

/**
 * Dimension source model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Dimension_Attribute_Source_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get all options
     *
     * @access public
     * @param bool $withEmpty
     * @return array
     * @author averun <dev@averun.com>
     */
    public function getAllOptions($withEmpty = false)
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => '',
                    'label' => 'Select one',
                ),
                array(
                    'value' => 'region',
                    'label' => 'Region',
                ),
                array(
                    'value' => 'dimension',
                    'label' => 'Dimension',
                )
            );
        }

        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label'=>''));
        }

        return $options;
    }
}
