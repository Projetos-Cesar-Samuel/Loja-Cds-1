<?php

class Ave_SizeChart_Model_Observer
{

    public function saveCategoryData(Varien_Event_Observer $observer)
    {
        /** @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getData('category');
        $chartId = (int)$category->getData('ave_size_chart');
        $lastChartId = (int)$category->getOrigData('ave_size_chart');
        if ($chartId == $lastChartId) {
            return $this;
        }

        $chartModel = Mage::getModel('ave_sizechart/chart');
        $categoryId = (int)$category->getId();
        if (!empty($lastChartId)) {             //delete in existing chart
            $chartModel->load($lastChartId);
            $existingCategoryIds = $chartModel->getData('product_category');
            if (!empty($existingCategoryIds)) {
                $existingCategoryIds = explode(',', $existingCategoryIds);
                if (($key = array_search($categoryId, $existingCategoryIds)) !== false) {
                    unset($existingCategoryIds[$key]);
                }

                $chartModel->setData('product_category', implode(',', $existingCategoryIds));
                $chartModel->save();
            }
        }

        if (!empty($chartId)) {                 //add to new chart
            $chartModel->load($chartId);
            $existingCategoryIds = $chartModel->getData('product_category');
            if (empty($existingCategoryIds)) {
                $existingCategoryIds = array($categoryId);
            } else {
                $existingCategoryIds = explode(',', $existingCategoryIds);
                if (!in_array($categoryId, $existingCategoryIds)) {
                    $existingCategoryIds[] = $categoryId;
                }
            }

            $chartModel->setData('product_category', implode(',', $existingCategoryIds));
            $chartModel->save();
        }

        return $this;
    }

    public function onSalesQuoteItemSetProduct(Varien_Event_Observer $observer)
    {
        /** @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $observer->getQuoteItem();
        if ($quoteItem->getData('ave_dimensions')) {
            return ;
        }
        $allSessions = Mage::getSingleton('core/session')->getData();
        $dimensions = '';
        foreach ($allSessions as $name => $value) {
            if (strpos($name, 'ave_sizechart_dimension') === 0) {
                $dimensions .= $name . '=' . $value . ';';
            }
        }

        if (!empty($dimensions)) {
            $quoteItem->setData('ave_dimensions', $dimensions);
        }
    }
}
