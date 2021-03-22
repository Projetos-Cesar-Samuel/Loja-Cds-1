<?php

/**
 * Size resource model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Resource_Size extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function _construct()
    {
        $this->_init('ave_sizechart/size', 'entity_id');
    }
}
