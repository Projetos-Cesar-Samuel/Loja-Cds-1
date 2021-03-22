<?php

/**
 * Chart admin edit tabs
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Chart_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('chart_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ave_sizechart')->__('Chart Information'));
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Chart_Edit_Tabs
     * @author averun <dev@averun.com>
     */
    protected function _prepareLayout()
    {
        $chart = $this->getChart();
        $entity = Mage::getModel('eav/entity_type')
            ->load('ave_sizechart_chart', 'entity_type_code');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($entity->getEntityTypeId());
        $attributes->getSelect()->order('additional_table.position', 'ASC');

        $this->addTab(
            'info',
            array(
                'label'   => Mage::helper('ave_sizechart')->__('Chart Information'),
                'content' => $this->getLayout()->createBlock(
                    'ave_sizechart/adminhtml_chart_edit_tab_attributes'
                )
                ->setAttributes($attributes)
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve chart entity
     *
     * @access public
     * @return Ave_SizeChart_Model_Chart
     * @author averun <dev@averun.com>
     */
    public function getChart()
    {
        return Mage::registry('current_chart');
    }
}
