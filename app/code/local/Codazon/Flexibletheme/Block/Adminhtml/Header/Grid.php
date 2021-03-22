<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Header_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
        $this->setId("headerGrid");
        $this->setDefaultSort("entity_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
	}
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel("flexibletheme/header")
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
        $this->addColumn("preview", array(
			'header' => $helper->__("Preview"),
			'index' => 'identifier',
            'frame_callback' => array($this, 'callbackImage'),
            'width'     => '80px'
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

    public function callbackImage($value) {
        $id = uniqid('preview_');
        $path = 'codazon/flexibletheme/header/' . $value . '/preview.jpg';
        $file = Mage::getBaseDir('media') . DS . $path;
        if (file_exists($file)) {
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $path;
        } else {
            $src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'codazon/flexibletheme/images/no-preview.jpg';
        }
        return "<a style='display: inline-block; padding: 5px 5px;' href='javascript:void(0)' onclick='imagePreview(\"{$id}\");'><img id='{$id}' style='max-width: 100%; border:1px solid #bdbdbd' src='{$src}' /></a>";
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
		$this->getMassactionBlock()->addItem('remove_header', array(
			 'label'=> Mage::helper('flexibletheme')->__('Remove Header'),
			 'url'  => $this->getUrl('*/adminhtml_header/massRemove'),
			 'confirm' => Mage::helper('flexibletheme')->__('Are you sure?')
		));
		return $this;
	}
	
}