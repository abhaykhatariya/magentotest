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
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Combine
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Rule\Block\Editable;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\Combine as ConditionCombine;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\AllordersFactory;
use Psr\Log\LoggerInterface;

class Combine extends ConditionCombine
{
    /**
     * @var AllordersFactory
     */
    protected $_productAllordersFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logLoggerInterface;

    /**
     * @var Editable
     */
    protected $_blockEditable;


    /**
     * @var DesignInterface
     */
    protected $_viewDesignInterface;

    /**
     * @var TimezoneInterface
     */
    protected $_dateTimeTimezoneInterface;


    public function __construct(Context $context, 
        AllordersFactory $productAllordersFactory, 
        Editable $blockEditable,
        DesignInterface $viewDesignInterface,
        array $data = [])
    {
        $this->_productAllordersFactory = $productAllordersFactory;
        $this->_logLoggerInterface = $context->getLogger();
        $this->_blockEditable = $blockEditable;
        $this->_viewDesignInterface = $viewDesignInterface;
        $this->_dateTimeTimezoneInterface = $context->getLocaleDate();

        parent::__construct($context, $data);
        $this->initType();
    }

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Combine');
        return $this;
    }


    /**
     * Get attributes for cart history condtions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //Get product / cart attributes
        $productCondition = $this->_productAllordersFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_')===0) {
                $iAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders|'.$code, 'label'=>$label];
            } else {
                $pAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders|'.$code, 'label'=>$label];
            }
        }

        //set attributes
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Combine', 'label'=>__('Conditions Combination')],
            ['label'=>__('Product Attribute'), 'value'=>$pAttributes],
        ]);
        return $conditions;
    }

    /**
     * Conditions as Array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();
        $out['periodtype'] = $this->getPeriodtype();
        $out['beforedays'] = $this->getBeforedays();
        $out['recentdays'] = $this->getRecentdays();
        $out['todate'] = date('Y-m-d',strtotime($this->getTodate()));
        $out['fromdate'] = date('Y-m-d',strtotime($this->getFromdate()));

        return $out;
    }

    /**
     * Conditions as XML
     *
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml = "<aggregator>".$this->getAggregator()."</aggregator>"
            ."<periodtype>".$this->getPeriodtype()."</periodtype>"
            ."<recentdays>".$this->getRecentdays()."</recentdays>"
            ."<beforedays>".$this->getBeforedays()."</beforedays>"
            ."<fromdate>".$this->getFromdate()."</fromdate>"
            ."<todate>".$this->getTodate()."</todate>"
            ."<value>".$this->getValue()."</value>"
            ."<$containerKey>";
        foreach ($this->getConditions() as $condition) {
            $xml .= "<$itemKey>".$condition->asXml()."</$itemKey>";
        }
        $xml .= "</$containerKey>";
        return $xml;
    }

    /**
     * Load conditions array
     *
     * @param $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key='conditions')
    {
        $this->setAggregator(isset($arr['aggregator']) ? $arr['aggregator']
            : (isset($arr['attribute']) ? $arr['attribute'] : null))
            ->setValue(isset($arr['value']) ? $arr['value']
                : (isset($arr['operator']) ? $arr['operator'] : null));

        if (isset($arr['periodtype'])) {
            $this->setPeriodtype($arr['periodtype']);
        }
        if (isset($arr['recentdays'])) {
            $this->setRecentdays($arr['recentdays']);
        }
        if (isset($arr['beforedays'])) {
            $this->setBeforedays($arr['beforedays']);
        }
        if (isset($arr['fromdate'])) {
            $this->setFromdate($arr['fromdate']);
        }
        if (isset($arr['todate'])) {
            $this->setTodate($arr['todate']);
        }

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $condArr) {
                try {
                    $cond = $this->_conditionFactory->create($condArr['type']);
                    if ($cond) {
                        $this->addCondition($cond);
                        $cond->loadArray($condArr, $key);
                    }
                } catch (\Exception $e) {
                    $this->_logLoggerInterface->error($e);
                }
            }
        }
        return $this;
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
