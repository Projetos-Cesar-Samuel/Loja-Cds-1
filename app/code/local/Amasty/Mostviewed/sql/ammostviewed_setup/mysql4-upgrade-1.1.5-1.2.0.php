<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
$blocks = array('related_products', 'up_sells', 'cross_sells');
foreach ($blocks as $key => $block) {
    $oldSettings = Mage::getModel('core/config_data')->getCollection();
    $oldSettings->getSelect()->where('path = ?', 'ammostviewed/' . $block . '/replase');
    if (0 < $oldSettings->getSize()) {
        foreach ($oldSettings as $setting) {
            Mage::getConfig()
                ->saveConfig(
                    'ammostviewed/' . $block . '/manually',
                    $setting->getValue(),
                    $setting->getScope(),
                    $setting->getScopeId()
                );
            $setting->delete();
        }
    }
}
