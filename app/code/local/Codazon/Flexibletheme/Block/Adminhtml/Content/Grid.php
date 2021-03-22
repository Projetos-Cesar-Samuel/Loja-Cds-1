<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Content_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
        $this->setId("contentGrid");
        $this->setDefaultSort("entity_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
	}
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel("flexibletheme/content")
            ->getCollection()
            ->addAttributeToSelect('title');
        $collection->getSelect()->group('e.entity_id');
        
        $this->setCollection($collection);
        
        
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
		$helper = Mage::helper("flexibletheme");
		$this->addColumn("entity_id", array(
			"header" => $helper->__("ID"),
			"align" =>"right",
			"width" => "50px",
			"type" => "number",
			"index" => "entity_id",
		));
		$this->addColumn("identifier", array(
			"header" => $helper->__("Identifier"),
			"index" => "identifier",
		));
		$this->addColumn("title", array(
			"header" => $helper->__("Title"),
			"index" => "title",
		));
		$this->addColumn('is_active', array(
			'header' => $helper->__('Status'),
			'index' => 'is_active',
			'type' => 'options',
			'options' => $this->getStatus(),				
		));
        
        return parent::_prepareColumns();
    }
	
	static public function getStatus()
	{
        $helper = Mage::helper("flexibletheme");
		$status = array(); 
		$status[0] = $helper->__('Disable');
		$status[1] = $helper->__('Enable');
		return $status;
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl("*/*/edit", array("id" => $row->getId()));
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('entity_ids');
		$this->getMassactionBlock()->setUseSelectAll(true);
		$this->getMassactionBlock()->addItem('remove_content', array(
			 'label'=> Mage::helper('flexibletheme')->__('Remove Content'),
			 'url'  => $this->getUrl('*/adminhtml_content/massRemove'),
			 'confirm' => Mage::helper('flexibletheme')->__('Are you sure?')
		));
		return $this;
	}
	
}