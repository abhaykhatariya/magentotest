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
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Combine
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder;

use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\Combine as ConditionCombine;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\NeworderFactory;

class Combine extends ConditionCombine
{
    /**
     * @var NeworderFactory
     */
    protected $_productNeworderFactory;

    public function __construct(
        Context $context,
        NeworderFactory $productNeworderFactory, 
        array $data = []
    ) {
        $this->_productNeworderFactory = $productNeworderFactory;

        parent::__construct($context, $data);
        $this->initType();
    }

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Combine');
        return $this;
    }

    /**
     * Get select options for condtion combination
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //get cart conditions
        $productCondition = $this->_productNeworderFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_')===0) {
                $iAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder|'.$code, 'label'=>$label];
            } else {
                $pAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder|'.$code, 'label'=>$label];
            }
        }

        //set condtions
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Combine', 'label'=>__('Conditions Combination')],
            ['label'=>__('Cart Item Attribute'), 'value'=>$iAttributes],
            ['label'=>__('Product Attribute'), 'value'=>$pAttributes],
        ]);
        return $conditions;
    }

    /**
     * @param $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
