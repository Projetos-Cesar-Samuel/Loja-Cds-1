<?php 
class MGS_Oscheckout_Block_Adminhtml_Sales_Order_Comment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	//show each product in a row
	public function render(Varien_Object $row) 
	{
		$value =  $row->getId();
		$order =  Mage::getModel('sales/order')->load($value);
		return $order->getData('mgs_order_comment');
	}
	
	
}