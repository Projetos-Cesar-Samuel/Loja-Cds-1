<?php
/**
 * idnovate.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Idnovate
 * @package    Idnovate_WhatsAppChat
 * @version    Release: 1.0.0
 * @author     idnovate.com (info@idnovate.com)
 * @copyright  Copyright (c) 2017 idnovate.com (http://www.idnovate.com)
 */

class Idnovate_WhatsAppChat_Block_Adminhtml_System_Config_Schedule extends Mage_Adminhtml_Block_System_Config_Form_Field {
	protected function _getElementHtml( Varien_Data_Form_Element_Abstract $element )
	{
		$operationTime = '[{},{},{},{},{},{},{}]';
		if ($element->getValue() != '') {
			$operationTime = $element->getValue();
		}
		$html = '<div id="scheduleContainer"></div>';
		$html .= '
		<script type="text/javascript" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/idnovate/whatsappchat.js"></script>
		<script>
		var businessHoursManager = jQuery("#scheduleContainer").businessHours({
    		operationTime:'.$operationTime.',
    		weekdays:["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
    		defaultOperationTimeFrom:"00:00",
    		defaultOperationTimeTill:"23:59",
    		postInit:function(){
	        	jQuery(".operationTimeFrom, .operationTimeTill").timepicker({
    	    		"timeFormat": "H:i",
        			"step": 15
				});
    		},
    		dayTmpl:"<div class=\'dayContainer\' style=\'width: 80px;\'>" +
    		 "<div data-original-title=\'\' class=\'colorBox\'>" +
    		 	"<input type=\'checkbox\' class=\'invisible operationState\'>" +
    		 "</div>" +
    		 "<div class=\'weekday\'></div>" +
    		 "<div class=\'operationDayTimeContainer\'>" +
    		 	"<div class=\'operationTime input-group\'>" +
    		 		"<span class=\'input-group-addon\'>" +
    		 			"<i class=\'icon icon-sun\'></i>" +
    		 		"</span>" +
    		 		"<input type=\'text\' name=\'startTime\' class=\'mini-time form-control operationTimeFrom\' value=\'\'>" +
    		 	"</div>" +
    		 	"<div class=\'operationTime input-group\'>" +
    		 		"<span class=\'input-group-addon\'><i class=\'icon icon-moon\'></i></span>" +
    		 		"<input type=\'text\' name=\'endTime\' class=\'mini-time form-control operationTimeTill\' value=\'\'>" +
    		 	"</div>" +
    		 "</div></div>"});
		jQuery("document").ready(function() {
		    jQuery("div#scheduleContainer").on("click", function() {
		        jQuery("input#idnovate_whatsappchat_display_schedule").val("");
		        jQuery("input#idnovate_whatsappchat_display_schedule").val(JSON.stringify(businessHoursManager.serialize()));
		    });
		    jQuery("input.mini-time").each(function() {
				jQuery(this).change(function() {
		        jQuery("input#idnovate_whatsappchat_display_schedule").val("");
		        jQuery("input#idnovate_whatsappchat_display_schedule").val(JSON.stringify(businessHoursManager.serialize()));
				});
		    });
		});
		</script>';
		$schedule = new Varien_Data_Form_Element_Text();
		$data = array(
			'name'      => $element->getName(),
			'html_id'   => $element->getId(),
			'type'      => 'hidden'
		);
		$schedule->setData($data);
		$schedule->setValue($operationTime);
		$schedule->setForm($element->getForm());
		//$schedule->addClass('color '.$element->getClass());
		return $html.$schedule->getElementHtml();
	}
}
