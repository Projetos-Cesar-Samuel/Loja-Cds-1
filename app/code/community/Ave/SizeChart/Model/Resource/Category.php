<?php

/**
 * Category of sizes resource model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Resource_Category extends Mage_Catalog_Model_Resource_Abstract
{


    /**
     * constructor
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('ave_sizechart_category')
            ->setConnection(
                $resource->getConnection('category_read'),
                $resource->getConnection('category_write')
            );

    }

    /**
     * wrapper for main table getter
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getMainTable()
    {
        return $this->getEntityTable();
    }
}
