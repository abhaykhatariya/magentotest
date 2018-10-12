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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder;

use Magento\Framework\DataObject;

class Found
    extends Combine
{

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Found');
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

    /**
     * HTML for rule creation
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __("If an item is %1 in the cart with %2 of these conditions true:", $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml());
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

        if ($object->getCurrentOrder()) {
            foreach ($object->getCurrentOrder()->getAllItems() as $item) {
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
