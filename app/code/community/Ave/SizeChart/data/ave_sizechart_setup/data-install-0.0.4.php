<?php

/**
 * SizeChart module install script
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */

$transaction = Mage::getModel('core/resource_transaction');
/***********************************************************************************************************************
 * TYPE
 */
$types = array(
    array(
        'name'     => 'Top',
        'position' => '10',
        'status'   => 1,
    ),
    array(
        'name'     => 'Bottom',
        'position' => '12',
        'status'   => '1',
    ),
    array(
        'name'     => 'Dress',
        'position' => '14',
        'status'   => '1',
    ),
    array(
        'name'     => 'Swim',
        'position' => '16',
        'status'   => '1',
    ),
    array(
        'name'     => 'Suit',
        'position' => '18',
        'status'   => '1',
    ),
    array(
        'name'     => 'Outerwear',
        'position' => '20',
        'status'   => '1',
    ),
    array(
        'name'     => 'Shoes',
        'position' => '22',
        'status'   => '1',
    ),
    array(
        'name'     => 'Accessory',
        'position' => '24',
        'status'   => '1',
    ),
);
foreach ($types as $type) {
    $typeModel = Mage::getModel('ave_sizechart/type');
    $typeModel->setData($type);
    $transaction->addObject($typeModel);
}


/***********************************************************************************************************************
 * CATEGORY
 */

$categories = array(
    array(
        'name'     => 'Women',
        'position' => '10',
        'status'   => 1,
    ),
    array(
        'name'     => 'Men',
        'position' => '12',
        'status'   => 1,
    ),
    array(
        'name'     => 'Girl',
        'position' => '14',
        'status'   => 1,
    ),
    array(
        'name'     => 'Boy',
        'position' => '16',
        'status'   => 1,
    ),
    array(
        'name'     => 'Baby',
        'position' => '18',
        'status'   => 1,
    ),
    array(
        'name'     => 'Body',
        'position' => '20',
        'status'   => 1,
    ),
    array(
        'name'     => 'Maternity',
        'position' => '22',
        'status'   => 1,
    ),
);
foreach ($categories as $category) {
    $categoryModel = Mage::getModel('ave_sizechart/category');
    $categoryModel->setData($category);
    $transaction->addObject($categoryModel);
}


/***********************************************************************************************************************
 * DIMENSION
 */

$dimensions = array(
    array(
        'name'        => 'Neck',
        'description' => 'Neck Description',
        'type'        => 'dimension',
        'position'    => '10',
        'status'      => 1,
    ),
    array(
        'name'        => 'Torso',
        'description' => 'Torso Description',
        'type'        => 'dimension',
        'position'    => '12',
        'status'      => 1,
    ),
    array(
        'name'        => 'Arm length',
        'description' => 'Arm length Description',
        'type'        => 'dimension',
        'position'    => '14',
        'status'      => 1,
    ),
    array(
        'name'        => 'Chest',
        'description' => 'Chest Description',
        'type'        => 'dimension',
        'position'    => '16',
        'status'      => 1,
    ),
    array(
        'name'        => 'Bust',
        'description' => 'Bust Description',
        'type'        => 'dimension',
        'position'    => '18',
        'status'      => 1,
    ),
    array(
        'name'        => 'Natural waist',
        'description' => 'Natural waist Description',
        'type'        => 'dimension',
        'position'    => '20',
        'status'      => 1,
    ),
    array(
        'name'        => 'Waist',
        'description' => 'Waist Description',
        'type'        => 'dimension',
        'position'    => '22',
        'status'      => 1,
    ),
    array(
        'name'        => 'Seat',
        'description' => 'Seat Description',
        'type'        => 'dimension',
        'position'    => '24',
        'status'      => 1,
    ),
    array(
        'name'        => 'Hips',
        'description' => 'Hips Description',
        'type'        => 'dimension',
        'position'    => '26',
        'status'      => 1,
    ),
    array(
        'name'        => 'Inseam',
        'description' => 'Inseam Description',
        'type'        => 'dimension',
        'position'    => '28',
        'status'      => 1,
    ),
    array(
        'name'        => 'Foot',
        'description' => 'Foot Description',
        'type'        => 'dimension',
        'position'    => '30',
        'status'      => 1,
    ),
    array(
        'name'        => 'US',
        'description' => 'US Description',
        'type'        => 'region',
        'position'    => '50',
        'status'      => 1,
    ),
    array(
        'name'        => 'US (numeric size)',
        'description' => 'US Description',
        'type'        => 'region',
        'position'    => '55',
        'status'      => 1,
    ),
    array(
        'name'        => 'Canada',
        'description' => 'Canada Description',
        'type'        => 'region',
        'position'    => '60',
        'status'      => 1,
    ),
    array(
        'name'        => 'UK',
        'description' => 'UK Description',
        'type'        => 'region',
        'position'    => '65',
        'status'      => 1,
    ),
    array(
        'name'        => 'Germany',
        'description' => 'Germany Description',
        'type'        => 'region',
        'position'    => '70',
        'status'      => 1,
    ),
    array(
        'name'        => 'France',
        'description' => 'France Description',
        'type'        => 'region',
        'position'    => '75',
        'status'      => 1,
    ),
    array(
        'name'        => 'Italy',
        'description' => 'Italy Description',
        'type'        => 'region',
        'position'    => '80',
        'status'      => 1,
    ),
    array(
        'name'        => 'Spain',
        'description' => 'Spain Description',
        'type'        => 'region',
        'position'    => '85',
        'status'      => 1,
    ),
    array(
        'name'        => 'Europe',
        'description' => 'Europe Description',
        'type'        => 'region',
        'position'    => '90',
        'status'      => 1,
    ),
    array(
        'name'        => 'Japan',
        'description' => 'Japan Description',
        'type'        => 'region',
        'position'    => '95',
        'status'      => 1,
    ),
    array(
        'name'        => 'Australia',
        'description' => 'Australia Description',
        'type'        => 'region',
        'position'    => '100',
        'status'      => 1,
    ),
    array(
        'name'        => 'Height',
        'description' => 'Height Description',
        'type'        => 'dimension',
        'position'    => '32',
        'status'      => 1,
    ),
    array(
        'name'        => 'Weight, kgs',
        'description' => 'Weight Description',
        'type'        => 'dimension',
        'position'    => '34',
        'status'      => 1,
    ),
    array(
        'name'        => 'Age',
        'description' => 'Age Description',
        'type'        => 'dimension',
        'position'    => '36',
        'status'      => 1,
    ),
);
foreach ($dimensions as $dimension) {
    $dimensionModel = Mage::getModel('ave_sizechart/dimension');
    $dimensionModel->setData($dimension);
    $transaction->addObject($dimensionModel);
}


/***********************************************************************************************************************
 * CHARTS
 */
$defaultDescription =
    'All conversions are approximate. Fits may vary by style or personal preference; sizes may vary by manufacturer.';
$defaultDescriptionShoes =
    'All conversions are approximate. Fits may vary by style or personal preference; sizes may vary by manufacturer. '
    . 'Please note: When you select your size, "H" equals a half size';
$charts = array(
    array(
        'name'         => 'Women Tops',
        'category_id'  => '1',
        'type_id'      => '1',
        'dimension_id' => '5, 7, 12, 13, 15, 16, 17, 18, 19, 21, 22',
        'description'  => $defaultDescription,
        'image'        => '/w/o/women-top.png'
    ),
    array(
        'name'         => 'Men Tops',
        'category_id'  => '2',
        'type_id'      => '1',
        'dimension_id' => '1, 4, 7, 3, 12',
        'description'  => $defaultDescription,
        'image'        => '/m/e/men-top.png'
    ),
    array(
        'name'         => 'Girl Tops',
        'category_id'  => '3',
        'type_id'      => '1',
        'dimension_id' => '4, 7, 13, 25, 23, 24',
        'description'  => $defaultDescription,
        'image'        => '/g/i/girl-top.png'
    ),
    array(
        'name'         => 'Boy Tops',
        'category_id'  => '4',
        'type_id'      => '1',
        'dimension_id' => '4, 7, 13, 25, 23, 24',
        'description'  => $defaultDescription,
        'image'        => '/b/o/boy-top.png'
    ),
    array(
        'name'         => 'Women Bottoms',
        'category_id'  => '1',
        'type_id'      => '2',
        'dimension_id' => '7, 9, 12, 13, 15, 16, 17, 18, 19, 21, 22',
        'description'  => $defaultDescription,
        'image'        => '/w/o/women-bottom.png'
    ),
    array(
        'name'         => 'Men Bottoms',
        'category_id'  => '2',
        'type_id'      => '2',
        'dimension_id' => '7, 12, 15, 17, 20, 21',
        'description'  => $defaultDescription,
        'image'        => '/m/e/men-bottom.png'
    ),
    array(
        'name'         => 'Girl Bottoms',
        'category_id'  => '3',
        'type_id'      => '2',
        'dimension_id' => '12, 13, 25, 23, 24, 7, 8',
        'description'  => $defaultDescription,
        'image'        => '/g/i/girl-bottom.png'
    ),
    array(
        'name'         => 'Boy Bottoms',
        'category_id'  => '4',
        'type_id'      => '2',
        'dimension_id' => '12, 13, 25, 23, 24, 7, 8',
        'description'  => $defaultDescription,
        'image'        => '/b/o/boy-bottom.png'
    ),
    array(      //id = 9
        'name'         => 'Women Dresses',
        'category_id'  => '1',
        'type_id'      => '3',
        'dimension_id' => '5, 6, 9, 12, 13, 15, 16, 17, 18, 19, 21, 22',
        'description'  => $defaultDescription,
        'image'        => '/w/o/women-dresses.png'
    ),
    array(      //id = 10
        'name'         => 'Men Suit',
        'category_id'  => '2',
        'type_id'      => '5',
        'dimension_id' => '1, 3, 4, 7, 12, 13, 15, 20, 21',
        'description'  => $defaultDescription,
        'image'        => '/m/e/men-suiting.png'
    ),
    array(      //id = 11
        'name'         => 'Girl Dresses',
        'category_id'  => '3',
        'type_id'      => '3',
        'dimension_id' => '13, 24, 23, 25, 7, 4',
        'description'  => $defaultDescription,
        'image'        => '/g/i/girl-dresses.png'
    ),
    array(      //id = 12
        'name'         => 'Women Swim',
        'category_id'  => '1',
        'type_id'      => '4',
        'dimension_id' => '2, 5, 6, 9, 12, 13, 17, 20',
        'description'  => $defaultDescription,
        'image'        => '/w/o/women-swim.png'
    ),
    array(      //id = 13
        'name'         => 'Girl Swim',
        'category_id'  => '3',
        'type_id'      => '4',
        'dimension_id' => '13, 25, 23, 24, 7, 4',
        'description'  => $defaultDescription,
        'image'        => '/g/i/girl-swim.png'
    ),
    array(      //id = 14
        'name'         => 'Women Outerwear',
        'category_id'  => '1',
        'type_id'      => '6',
        'dimension_id' => '3, 5, 6, 9, 12, 13, 15, 16, 17, 18, 19, 21, 22',
        'description'  => $defaultDescription,
        'image'        => '/w/o/women-outerwear.png'
    ),
    array(      //id = 15
        'name'         => 'Men Outerwear',
        'category_id'  => '2',
        'type_id'      => '6',
        'dimension_id' => '1, 3, 4, 7, 12, 13, 15, 20, 21',
        'description'  => $defaultDescription,
        'image'        => '/m/e/men-outerwear.png'
    ),
    array(      //id = 16
        'name'         => 'Girl Outerwear',
        'category_id'  => '3',
        'type_id'      => '6',
        'dimension_id' => '13, 25, 23, 24, 7, 4',
        'description'  => $defaultDescription,
        'image'        => '/g/i/girl-outerwear.png'
    ),
    array(      //id = 17
        'name'         => 'Boy Outerwear',
        'category_id'  => '4',
        'type_id'      => '6',
        'dimension_id' => '13, 25, 23, 24, 7, 4',
        'description'  => $defaultDescription,
        'image'        => '/b/o/boy-outerwear.png'
    ),
    array(      //id = 18
        'name'         => 'Women Shoes',
        'category_id'  => '1',
        'type_id'      => '7',
        'dimension_id' => '12, 13, 20, 15, 22, 21, 11',
        'description'  => $defaultDescriptionShoes,
    ),
    array(      //id = 19
        'name'         => 'Men Shoes',
        'category_id'  => '2',
        'type_id'      => '7',
        'dimension_id' => '13, 20, 15, 22, 21, 11',
        'description'  => $defaultDescriptionShoes,
    ),
    array(      //id = 20
        'name'         => 'Girl Shoes',
        'category_id'  => '3',
        'type_id'      => '7',
        'dimension_id' => '12, 13, 15, 20, 11',
        'description'  => $defaultDescriptionShoes,
    ),
    array(      //id = 21
        'name'         => 'Boy Shoes',
        'category_id'  => '4',
        'type_id'      => '7',
        'dimension_id' => '12, 13, 15, 20, 11',
        'description'  => $defaultDescriptionShoes,
    ),
);
foreach ($charts as $chart) {
    $chartModel = Mage::getModel('ave_sizechart/chart');
    $chartModel->setData($chart);
    $transaction->addObject($chartModel);
}

    /*
      1 - Neck                              12 - US
      2 - Torso                             13 - US (numeric size)
      3 - Arm length                        14 - Canada
      4 - Chest                             15 - UK
      5 - Bust                              16 - Germany
      6 - Natural waist                     17 - France
      7 - Waist                             18 - Italy
      8 - Seat                              19 - Spain
      9 - Hips                              20 - Europe
      10 - Inseam                           21 - Japan
      11 - Foot                             22 - Australia
      23 - Height
      24 - Weight
      25 - Age
     */

$transaction->save();