<?php

/**
 * Adminhtml category of sizes attribute add/edit form options tab
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Category_Attribute_Edit_Tab_Options
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('eav/attribute/options.phtml');
    }

}
