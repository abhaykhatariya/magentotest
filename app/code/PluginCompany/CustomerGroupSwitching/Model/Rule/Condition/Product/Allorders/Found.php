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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders;

use Magento\Framework\DataObject;

class Found
    extends Combine
{

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Found');
        return $this;
    }

    /**
     * Load value options
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Found
     */
    public function loadValueOptions()
    {
        $this->setValueOption([
            1 => __('FOUND'),
            0 => __('NOT FOUND')
        ]);
        return $this;
    }

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

        $html =
            $this->getTypeElement()->getHtml()
            . __(
                "If an item is %1 in the order history %2"
                . "<span style='$displayBetween' id='" . $identifier . "between'>%3 and %4</span>"
                . "<span style='$displayLessdays' id='" . $identifier . "lessdaysago'>%5 days</span>"
                . "<span style='$displayMoredays' id='" . $identifier . "moredaysago'>%6 days</span>"
                . ", with %7 of these conditions true:",
                $this->getValueElement()->getHtml(),
                $this->getPeriodTypeDropdown()->toHtml(),
                $this->getFromDateElement()->getHtml(),
                $this->getToDateElement()->getHtml(),
                $this->getRecentDaysElement()->getHtml(),
                $this->getBeforeDaysElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
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
        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;

        $period = $this->getPeriodRange();

        foreach ($this->getRule()->getProductHistory($period['from'],$period['to']) as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }
        // found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        }
        // not found and we're making sure it doesn't exist
        elseif (!$found && !$true) {
            return true;
        }
        return false;
    }
}
