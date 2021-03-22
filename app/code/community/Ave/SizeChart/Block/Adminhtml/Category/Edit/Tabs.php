<?php

/**
 * Category of sizes admin edit tabs
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('category_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ave_sizechart')->__('Category of sizes Information'));
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Category_Edit_Tabs
     * @author averun <dev@averun.com>
     */
    protected function _prepareLayout()
    {
        $category = $this->getCategory();
        $entity = Mage::getModel('eav/entity_type')
            ->load('ave_sizechart_category', 'entity_type_code');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($entity->getEntityTypeId());
        $attributes->getSelect()->order('additional_table.position', 'ASC');

        $this->addTab(
            'info',
            array(
                'label'   => Mage::helper('ave_sizechart')->__('Category of sizes Information'),
                'content' => $this->getLayout()->createBlock(
                    'ave_sizechart/adminhtml_category_edit_tab_attributes'
                )
                ->setAttributes($attributes)
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve category of sizes entity
     *
     * @access public
     * @return Ave_SizeChart_Model_Category
     * @author averun <dev@averun.com>
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }
}
