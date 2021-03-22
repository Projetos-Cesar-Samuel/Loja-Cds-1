<?php

/**
 * Size edit form tab
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Size_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Size_Edit_Tab_Form
     * @author averun <dev@averun.com>
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('size_');
        $form->setFieldNameSuffix('size');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'size_form',
            array('legend' => Mage::helper('ave_sizechart')->__('Size'))
        );
        $values = Mage::getResourceModel('ave_sizechart/chart_collection')
            ->addAttributeToSelect('name')->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="size_chart_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeChartIdLink() {
                if ($(\'size_chart_id\').value == \'\') {
                    $(\'size_chart_id_link\').hide();
                } else {
                    $(\'size_chart_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/sizechart_chart/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'size_chart_id\').value);
                    $(\'size_chart_id_link\').href = realUrl;
                    $(\'size_chart_id_link\').innerHTML = text.replace(\'{#name}\', ' .
                                        '$(\'size_chart_id\').options[$(\'size_chart_id\').selectedIndex].innerHTML);
                }
            }
            $(\'size_chart_id\').observe(\'change\', changeChartIdLink);
            changeChartIdLink();
            </script>';

        $fieldset->addField(
            'name',
            'text',
            array(
                'label'    => Mage::helper('ave_sizechart')->__('Name'),
                'name'     => 'name',
                'required' => true,
                'class'    => 'required-entry',
            )
        );

        $fieldset->addField(
            'chart_id',
            'select',
            array(
                'label'              => Mage::helper('ave_sizechart')->__('Chart'),
                'name'               => 'chart_id',
                'required'           => false,
                'values'             => $values,
                'after_element_html' => $html
            )
        );
        $values = Mage::getResourceModel('ave_sizechart/dimension_collection')
            ->addAttributeToSelect('name')->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="size_dimension_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeDimensionIdLink() {
                if ($(\'size_dimension_id\').value == \'\') {
                    $(\'size_dimension_id_link\').hide();
                } else {
                    $(\'size_dimension_id_link\').show();
                    var url = \''
                            .$this->getUrl('adminhtml/sizechart_dimension/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'size_dimension_id\').value);
                    $(\'size_dimension_id_link\').href = realUrl;
                    $(\'size_dimension_id_link\').innerHTML = text.replace(\'{#name}\', ' .
                                '$(\'size_dimension_id\').options[$(\'size_dimension_id\').selectedIndex].innerHTML);
                }
            }
            $(\'size_dimension_id\').observe(\'change\', changeDimensionIdLink);
            changeDimensionIdLink();
            </script>';

        $fieldset->addField(
            'dimension_id',
            'select',
            array(
                'label'              => Mage::helper('ave_sizechart')->__('Dimension'),
                'name'               => 'dimension_id',
                'required'           => false,
                'values'             => $values,
                'after_element_html' => $html
            )
        );

        $fieldset->addField(
            'position',
            'text',
            array(
                'label'    => Mage::helper('ave_sizechart')->__('Position'),
                'name'     => 'position',
                'required' => true,
                'class'    => 'required-entry',
            )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('ave_sizechart')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('ave_sizechart')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('ave_sizechart')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_size')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }

        if (Mage::getSingleton('adminhtml/session')->getSizeData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getSizeData());
            Mage::getSingleton('adminhtml/session')->setSizeData(null);
        } elseif (Mage::registry('current_size')) {
            $formValues = array_merge($formValues, Mage::registry('current_size')->getData());
        }

        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
