<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Content_Edit_Form extends Codazon_Flexibletheme_Block_Adminhtml_Form
{
    protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('codazon_flexibletheme/edit/form.phtml');
	}
    
    protected function _prepareForm()
    {
        $model = Mage::registry('flexibletheme_data');
        $helper = Mage::helper('flexibletheme');
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' =>'multipart/form-data',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('general_section', array('legend' => $helper->__('General')));
        
        $fieldset->addField('store', 'hidden', array(
            'name' => 'store'
        ));
        
        $fieldset->addField('identifier', 'text', array(
            'label' => $helper->__('Identifier'),
            'name' => 'identifier',
            'required' => true,
            'note' => $helper->__('This field value is also the folder name of project package. Please consider carefully before modifying this field. The content style might be broken.
')
        ));
        
        $group = array(
			'title',
            'content',
            'layout_xml',
		);
        $attributes = $model->getAttributes($group);
        $attributes['content']->setCustomInputType('hidden');
        
        $this->_setFieldset(array($attributes['title'], $attributes['content']), $fieldset);
        
        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('flexibletheme')->__('Status'),
            'values'    => Codazon_Flexibletheme_Block_Adminhtml_Content_Grid::getStatus(),
            'name'      => 'is_active'
        ));
        $parentOptions = Mage::getModel('flexibletheme/system_config_source_contents');
        
        if ($model->getId()) {
            $parentOptions->setExcludeIds(array($model->getId()));
        }
        $fieldset->addField('parent', 'select', array(
            'label'     => $helper->__('Extends CSS from'),
            'values'    => $parentOptions->toOptionArray(),
            'name'      => 'parent'
        ));
        
        $this->_addTypographyVariableFields($form);
        
        $fieldset = $form->addFieldset('layout_section', array('legend' => $helper->__('Layout Update')));
        $fieldsetRenderer = Mage::getBlockSingleton('flexibletheme/adminhtml_form_renderer_fieldset');
        $fieldset->setRenderer($fieldsetRenderer);
        
        $this->_setFieldset(array($attributes['layout_xml']), $fieldset);
        
        
        $form->setDataObject($model);
        if (Mage::getSingleton('adminhtml/session')->getFlexiblethemeData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFlexiblethemeData());
            Mage::getSingleton('adminhtml/session')->setFlexiblethemeData(null);
        } elseif($model) {
            $form->setValues($model->getData());
        }
        return parent::_prepareForm();
    }
    
    protected function _addTypographyVariableFields($form)
    {
        $model = Mage::registry('flexibletheme_data');
        $model->loadVariableFields($form);
    }
    
}
