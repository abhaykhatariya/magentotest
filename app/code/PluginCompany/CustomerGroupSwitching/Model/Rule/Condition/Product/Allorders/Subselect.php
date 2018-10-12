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
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Subselect
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders;

use Magento\Framework\DataObject;

class Subselect
    extends Combine
{

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Subselect')
            ->setValue(null)
        ;
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
            'product_qty'  => __('total ordered quantity'),
            'product_turnover_incl_tax'  => __('product turnover incl. tax'),
            'product_turnover_excl_tax'  => __('product turnover excl. tax'),
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
     * HTML for rule creation
     *
     * @return string
     */
    public function asHtml()
    {
        $identifier = $this->getPrefix() . '_' . $this->getId() . '_';

        $displayBetween = $displayLessdays = $displayMoredays = 'display:none';
        switch($this->getPeriodtype()){
            case 'between':
                $displayBetween = '';
                break;
            case 'lessdaysago':
                $displayLessdays = '';
                break;
            case 'moredaysago':
                $displayMoredays = '';
                break;
        }

        $html = $this->getTypeElement()->getHtml().
        __(
            "If %1 %2 %3 for a subselection of items in the order history %4"
            . "<span style='$displayBetween' id='" . $identifier . "between'>%5 and %6</span>"
            . "<span style='$displayLessdays' id='" . $identifier . "lessdaysago'>%7 days </span>"
            . "<span style='$displayMoredays' id='" . $identifier . "moredaysago'>%8 days </span>"
            . "matching %9 of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getPeriodTypeDropdown()->toHtml(),
            $this->getFromDateElement()->getHtml(),
            $this->getToDateElement()->getHtml(),
            $this->getRecentDaysElement()->getHtml(),
            $this->getBeforeDaysElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }


    /**
     * validate product history
     *
     * @param DataObject $object Quote
     * @return boolean
     */
    public function validateDataObject(DataObject $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $period = $this->getPeriodRange();

        $productHistory = $this->getRule()->getProductHistory($period['from'], $period['to']);

        $attr = $this->getAttribute();
        $total = 0;

        foreach ($productHistory as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }

        return $this->validateAttribute($total);
    }
}
