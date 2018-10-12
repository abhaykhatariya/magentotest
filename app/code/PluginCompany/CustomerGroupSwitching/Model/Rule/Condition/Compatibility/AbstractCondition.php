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
/**
 * Abstract Rule condition data model
 *
 * @method string getOperator()
 * @method string getFormName()
 * @method setFormName()
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility;

use Magento\Framework\DataObject;
use Magento\Rule\Model\Condition\ConditionInterface;

abstract class AbstractCondition
    extends \Magento\Rule\Model\Condition\AbstractCondition
    implements ConditionInterface
{

    public function validateDataObject(DataObject $dataObject)
    {
        $attributeValue = $dataObject->getData($this->getAttribute());
        return $this->validateAttribute($attributeValue);
    }

}
