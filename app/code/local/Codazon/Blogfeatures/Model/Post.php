<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.16
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class Codazon_Blogfeatures_Model_Post extends AW_Blog_Model_Post
{
    public function getCreatedTimeDate(){
    	$date = Mage::app()->getLocale()->date(
		    $this->getData('created_time'),
		    Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
		    null, 
		    false
		);
        if (!$this->getData('date_format')) {
            $dateFormat = 'F j, Y';
        }
		$formatedDate = Mage::getModel('core/date')->date($dateFormat, $date->getTimestamp());
		$formatedDate = $date->toString('F');
        return $formatedDate;
    }
}
