<?php
/**
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

namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility;

abstract class AbstractProduct extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if($this->getAttributeObject()->getFrontendInput() == 'datetime') {
            return 'date';
        }
        return parent::getValueElementType();
    }
}
