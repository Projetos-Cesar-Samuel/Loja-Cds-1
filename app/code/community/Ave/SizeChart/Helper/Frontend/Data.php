<?php

/**
 * SizeChart frontend helper
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Helper_Frontend_Data extends Mage_Core_Helper_Abstract
{
    protected $dimensionSource;
    protected $measurements = array();
    protected $currentProductId;

    public function getData($productId = null)
    {
        if (!empty($productId)) {
            $this->currentProductId = $productId;
        }
        //return all data
        //todo: return data [
        //        this.sizes = params.sizes;
        //        this.isEnablePriority = params.isEnablePriority;
        //        this.dimensionList = params.dimensionList;
        //        this.setActiveUrl = params.setActiveUrl;
        //        this.setDimensionUrl = params.setDimensionUrl;
        //        this.sessionBaseUrl = params.sessionBaseUrl;
        //        this.sessionData = params.sessionData;
//        "paramBaseUrl" => Mage::getUrl('sizechart/param/'),
        //        this.membersMeasurements = params.membersMeasurements;
        //        this.currentDimension = params.currentDimension;
        //        this.translation.yourSizeLabel = params.translates[0];
        //        this.translation.memberSizeLabel = params.translates[1];
        //        this.translation.yourSizeUndefinedLabel = params.translates[2];
        //        this.translation.yourSizeOutOfRangeLabel = params.translates[3];
        //        this.translation.sizeChartButtonLabel = params.translates[4];
        //        this.translation.sizeChartButtonImage = params.translates[4;
        //]
        //todo: move all data to separate model/helper and use in few controllers
        //todo: group urls and translations
        $chart = $this->getChart();
        list($html, $sizesToJs) = $this->getGeneratedBodySizes($chart);
        $data = array(
            "sizes"               => $sizesToJs,
            'productId'           => $this->getCurrentProductId(),
            "currentDimension"    => $this->getDefaultDimension(),
            "members"             => $this->getCustomerMembers(),
            "membersMeasurements" => $this->getMembersMeasurements(),
            "setActiveUrl"        => Mage::getUrl('sizechart/setter/active'),
            "setDimensionUrl"     => Mage::getUrl('sizechart/setter/save'),
            "sessionBaseUrl"      => Mage::getUrl('sizechart/session/'),
            "paramBaseUrl"        => Mage::getUrl('sizechart/param/'),
            "dimensionList"       => $chart['dimensions'],
            "sessionData"         => $this->getSessionData(),
            "isEnablePriority"    => (int) Mage::getStoreConfig('ave_sizechart/general/dimension_priority'),
            "translation" => array(
                "yourSizeLabel"           => $this->__('Suggested Size'),
                "memberSizeLabel"         => $this->__('Suggested Size for'),
                "yourSizeUndefinedLabel"  => $this->__('Suggested Size is undefined'),
                "yourSizeOutOfRangeLabel" => $this->__('Suggested Size is out of range!'),
                "sizeChartButtonLabel"    => $this->__('Size Chart'),
                "sizeChartButtonImage"    => $this->__('Size Chart')
            )
        );

        return $data;
    }

    public function getChart($chartId = null)
    {
        $currentProduct = Mage::registry('current_product');
        if (empty($currentProduct) && !empty($this->currentProductId)) {
            $currentProduct = Mage::getModel('catalog/product')->load($this->currentProductId);
        }

        if (empty($chartId) && !empty($currentProduct)) {
            $chartId = $this->checkChartStatus($currentProduct->getData('ave_size_chart'));
        }

        if (empty($chartId) && !empty($currentProduct)) {
            $chartId = $this->getChartIdFromCategory($currentProduct);
        }

        if (empty($chartId)) {
            return '';
        }
        return $this->getChartById($chartId);
    }

    /**
     * @param $chartId
     * @return array|mixed|string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getChartById($chartId)
    {
        $chartId = (int) $chartId;
        if (!empty($chartId)) {
            /** @var Ave_SizeChart_Model_Chart $chart */
            $chart =
                Mage::getModel('ave_sizechart/chart')->setStoreId(Mage::app()->getStore()->getId())->load($chartId);
            if (!$chart->getId()) {
                return '';
            }
            return $chart->getData() + $chart->getSortSizes();
        }
        return '';
    }

    /**
     * @return array
     */
    public function getCustomerMembers()
    {
        if (!$this->isLoggedIn()) {
            return array();
        }
        $members = Mage::getModel('ave_sizechart/member')->getCustomerMembers();
        $memberList = array();
        if ($members && count($members) > 0) {
            foreach ($members as $member) {
                /** @var Ave_SizeChart_Model_Member $member */
                array_push(
                    $memberList,
                    array(
                        'id'     => $member->getId(),
                        'name'   => $member->getName(),
                        'active' => (int) $member->getActive()
                    )
                );
            }
        }
        return $memberList;
    }

    public function getMembersMeasurements()
    {
        if (!$this->isLoggedIn()) {
            return array();
        }

        if (empty($this->measurements)) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

            /** @var $m Ave_SizeChart_Model_Resource_MemberMeasure_Collection */
            $m = Mage::getModel('ave_sizechart/memberMeasure')->getCollection();
            $m->addFieldToFilter('customer_id', $customerId);
            $m->load();
            $data = $m->getData();
            $measurementsSorted = array();
            foreach ($data as $measure) {
                if (empty($measurementsSorted[$measure['member_id']])) {
                    $measurementsSorted[$measure['member_id']] = array();
                }

                $measurementsSorted[$measure['member_id']][$measure['dimension_id']] = $measure['value'];
            }

            $this->measurements = $measurementsSorted;
        }

        return $this->measurements;
    }

    public function getDefaultDimension()
    {
        $defaultDimension = Mage::getStoreConfig('ave_sizechart/general/dimension');
        if (empty($defaultDimension)) {
            $defaultDimension = $this->getDimensionSourceInstance();
            $defaultDimension = $defaultDimension::DIMENSION_DEFAULT;
        }

        return $defaultDimension;
    }

    public function getDimensionList()
    {
        $dimensionSource = $this->getDimensionSourceInstance();
        return $dimensionSource->getAllOptions(false);
    }

    public function getSessionData()
    {
        $filteredSessionData = array();
        $data = $this->getSession()->getData();
        if (!empty($data)) {
            foreach ($data as $name => $sessionValue) {
                if ($name && $sessionValue && !is_array($sessionValue) && strpos($name, 'ave_sizechart') === 0) {
                    $filteredSessionData[$name] = $sessionValue;
                }
            }
        }
        return $filteredSessionData;
    }

    /**
     * @param $chart
     * @return array
     */
    public function getGeneratedBodySizes($chart)
    {
        $sizesToJs = array();
        $html = '';
        for ($i = 0; $i <= $chart['maxSizeAmount']; $i++) {
            $class = $i % 2 ? ' class="odd"' : '';
            $mainSelected = 0;
            $html .= '<tr' . $class . '>';
            foreach ($chart['sizes'] as $dimensionId => $sizes) {
                if (!empty($sizes['sizes'][$i]) && array_key_exists('name', $sizes['sizes'][$i])
                    && !empty($sizes['sizes'][$i]['name'])
                ) {
                    $sizeName = $sizes['sizes'][$i]['name'];
                    $dashNamePosition = strpos($sizeName, '-');
                    if ($chart['dimensions']['dimension_' . $dimensionId]['type'] != 'region'
                        && $dashNamePosition > 0
                    ) {
                        $sizeName = explode('-', $sizeName);
                        for ($j = 0; $j < count($sizeName); $j++) {
                            $sizeName[$j] = floatval($sizeName[$j]);
                        }
                        $sizeName = implode('-', $sizeName);
                    }
                    $sizeId = 'size_' . $sizes['sizes'][$i]['id'];
                    $sizesToJs[$dimensionId][$sizeId] = $sizeName;
                    $mainClass = '';
                    if (!$mainSelected
                        && array_key_exists('main', $chart['dimensions']['dimension_' . $dimensionId])
                        && $chart['dimensions']['dimension_' . $dimensionId]['main'] == 1
                    ) {
                        $mainClass = ' class="ave-main"';
                        $mainSelected = 1;
                    }
                    $html .= '<td id="' . $sizeId . '"' . $mainClass . '>';
                    $html .= $sizeName;
                    $html .= '</td>';
                } else if (!empty($sizes['sizes'][$i]) && array_key_exists('name', $sizes['sizes'][$i])
                           && ($sizes['sizes'][$i]['name'] === '0')) {
                    $html .= '<td>0</td>';
                } else {
                    $html .= '<td></td>';
                }
            }
            $html .= '</tr>';
        }
        return array($html, $sizesToJs);
    }

    /**
     * @return Ave_SizeChart_Model_Source_Dimension
     */
    protected function getDimensionSourceInstance()
    {
        if (empty($this->dimensionSource)) {
            $this->dimensionSource = new Ave_SizeChart_Model_Source_Dimension();
        }

        return $this->dimensionSource;
    }

    /**
     * Retrieve customer session object
     *
     * @return Mage_Core_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * @return bool
     */
    protected function isLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * @param $chartId
     *
     * @return int
     */
    protected function checkChartStatus($chartId = null)
    {
        $chartId = (int) $chartId;
        if (empty($chartId)) {
            return 0;
        }

        $chart = Mage::getModel('ave_sizechart/chart')->load($chartId);
        $status = $chart->getData('status');
        if (empty($status)) {
            $chartId = 0;
        }

        return $chartId;
    }

    /**
     * @param $currentProduct
     * @return int|mixed|null|string
     * @throws Mage_Core_Exception
     */
    protected function getChartIdFromCategory($currentProduct)
    {
        $productCategories = $currentProduct->getCategoryIds();
        if (empty($productCategories)) {
            return '';
        }

        $chartId = null;
        rsort($productCategories);
        $parentIds = array();
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('ave_size_chart')
            ->addFieldToFilter('entity_id', array('in' => $productCategories));
        foreach ($collection as $category) {
            if (empty($chartId)) {
                $chartId = $category->getData('ave_size_chart');
                $parentIds = array_merge($category->getParentIds(), $parentIds);
            }
        }

        if (empty($chartId) && !empty($category)) {
            if (!empty($parentIds)) {
                $collection = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect('ave_size_chart')
                    ->addFieldToFilter('entity_id', array('in' => $parentIds));
                foreach ($collection as $category) {
                    if (empty($chartId)) {
                        $chartId = $category->getData('ave_size_chart');
                    }
                }
            }
        }

        $chartId = $this->checkChartStatus($chartId);

        return $chartId;
    }

    protected function getCurrentProductId()
    {
        $currentProduct = Mage::registry('current_product');
        $productId = null;
        if (!empty($currentProduct)) {
            $productId = $currentProduct->getId();
        } else if (!empty($this->currentProductId)) {
            $productId = $this->currentProductId;
        }
        return $productId;
    }
}
