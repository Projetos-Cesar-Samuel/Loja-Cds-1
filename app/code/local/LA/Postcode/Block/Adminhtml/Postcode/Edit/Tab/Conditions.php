<?php

/**
 * description
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
//class MW_Test_Block_Adminhtml_Test_Edit_Tab_Conditions
   // extends Mage_Adminhtml_Block_Widget_Form
    //implements Mage_Adminhtml_Block_Widget_Tab_Interface
class LA_Postcode_Block_Adminhtml_Postcode_Edit_Tab_Conditions
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
//    public function getTabLabel()
//    {
//        return Mage::helper('salesrule')->__('Conditions');
//    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
//    public function getTabTitle()
//    {
//        return Mage::helper('salesrule')->__('Conditions');
//    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
//    public function canShowTab()
//    {
//        return true;
//    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
//    public function isHidden()
//    {
//        return false;
//    }

    protected function _prepareForm()
    {
		$model = Mage::registry('postcode_data');
		//$model = Mage::getModel('salesrule/rule');
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/rule_conditions_fieldset'));
		//echo $this->getUrl('adminhtml/promo_quote/newConditionHtml/form/rule_conditions_fieldset'); 

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('postcode')->__('Config postcode show shopping and payment in check out')
        ))->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('postcode')->__('Conditions'),
            'title' => Mage::helper('postcode')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));
        
        $form->setValues($model->getData());
		
        //$form->setUseContainer(true);

		$fieldset->addField('title1', 'label', array(
          'after_element_html' => '<br /><br /><br />',
        ));
        
  		$fieldset->addField('note_rule_program', 'label', array(
          'after_element_html' => '<div style="width: 500px; float:left;">Note:</div>',
        ));
		
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
