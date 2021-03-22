<?php

/**
 * SizeChart module upgrade script
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun
 */


$model = Mage::getModel('ave_sizechart/dimension');

$dimension = $model->load(12); //US
if ($dimension && $dimension->getName() == 'US') {
    $model->setData('main', 1);
    $model->save();
}
