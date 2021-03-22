<?php

/**
 * parent entities column renderer
 * @category   Ave
 * @package    Ave_SizeChart
 * @author     averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Helper_Column_Renderer_Parent
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /**
     * render the column
     *
     * @access public
     * @param Varien_Object $row
     * @return string
     * @author averun <dev@averun.com>
     */
    public function render(Varien_Object $row)
    {
        $base = $this->getColumn()->getBaseLink();
        if (!$base) {
            return parent::render($row);
        }

        $options = $this->getColumn()->getOptions();
        if (!empty($options) && is_array($options)) {
            return $this->getHtmlLink($row, $options, $base);
        }

        return '';
    }

    /**
     * @param Varien_Object $row
     * @return array
     */
    protected function getParams(Varien_Object $row)
    {
        $params = array();
        $paramsData = $this->getColumn()->getData('params');
        if (is_array($paramsData)) {
            foreach ($paramsData as $name => $getter) {
                if (is_callable(array($row, $getter))) {
                    $params[$name] = $row->$getter();
                }
            }
        }

        $staticParamsData = $this->getColumn()->getData('static');
        if (is_array($staticParamsData)) {
            foreach ($staticParamsData as $key => $value) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param Varien_Object $row
     * @param $options
     * @param $base
     * @return string
     */
    protected function getHtmlLink(Varien_Object $row, $options, $base)
    {
        $params = $this->getParams($row);
        $value = $row->getData($this->getColumn()->getIndex());
        $html = '';
        if (!empty($value) && !is_array($value) && strpos($value, ',') !== false) {
            $value = explode(',', $value);
        }
        if (is_array($value)) {
            $showMissingOptionValues = (bool) $this->getColumn()->getShowMissingOptionValues();
            $res = array();
            foreach ($value as $item) {
                $item = trim($item);
                if (Mage::registry('csvExport')) {
                    $res[] = $this->escapeHtml($options[$item]);
                    continue;
                }
                if (isset($options[$item])) {
                    $res[] = '<a href="' . $this->getUrl($base, array('id' => $item)) . '" target="_blank">' . $this->escapeHtml(
                        $options[$item]
                    ) . '</a>';
                } elseif ($showMissingOptionValues) {
                    $res[] =
                        '<a href="' . $this->getUrl($base, $params) . '" target="_blank">' . $this->escapeHtml($item)
                        . '</a>';
                }
            }

            $html = implode(', ', $res);
        } elseif (isset($options[$value])) {
            if (Mage::registry('csvExport')) {
                $html = $this->escapeHtml($options[$value]);
            } else {
                $html = '<a href="' . $this->getUrl($base, $params) . '" target="_blank">' .
                    $this->escapeHtml($options[$value]) . '</a>';
            }
        } elseif (in_array($value, $options)) {
            $html =
                '<a href="' . $this->getUrl($base, $params) . '" target="_blank">' . $this->escapeHtml($value) . '</a>';
        }

        return $html;
    }
}
