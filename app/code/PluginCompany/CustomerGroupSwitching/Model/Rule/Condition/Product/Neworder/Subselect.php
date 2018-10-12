<?php
/*
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */

/**
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Subselect
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder;

use Magento\Framework\DataObject;

class Subselect
    extends Combine
{

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Subselect')
            ->setValue(null);
        return $this;
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @param $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key='conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    /**
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml = '<attribute>'.$this->getAttribute().'</attribute>'
            . '<operator>'.$this->getOperator().'</operator>'
            . parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'qty'  => __('total quantity'),
            'base_row_total'  => __('total amount'),
        ]);
        return $this;
    }

    /**
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '=='  => __('is'),
            '!='  => __('is not'),
            '>='  => __('equals or greater than'),
            '<='  => __('equals or less than'),
            '>'   => __('greater than'),
            '<'   => __('less than'),
            '()'  => __('is one of'),
            '!()' => __('is not one of'),
        ]);
        return $this;
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().
        __("If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getAggregatorElement()->getHtml())
        ;
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * validate
     *
     * @param DataObject $object Quote
     * @return boolean
     */
    public function validateDataObject(DataObject $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        if (!$object->getCurrentOrder()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getCurrentOrder()->getAllVisibleItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }

        return $this->validateAttribute($total);
    }
}
