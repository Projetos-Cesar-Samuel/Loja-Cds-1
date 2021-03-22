<?php

/**
 * Chart edit form
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Chart_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ave_sizechart/form/view.phtml');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('ave/size_chart/size.js');
        return parent::_prepareLayout();
    }


    /**
     * prepare form
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Chart_Edit_Form
     * @author averun <dev@averun.com>
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'         => 'edit_form',
                'action'     => $this->getUrl(
                    '*/*/save',
                    array(
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => $this->getRequest()->getParam('store')
                    )
                ),
                'method'     => 'post',
                'enctype'    => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getSizes()
    {
        $chartId = $this->getRequest()->getParam('id');
        return Mage::getModel('ave_sizechart/chart')->load($chartId)->getSortSizes();
    }
}
