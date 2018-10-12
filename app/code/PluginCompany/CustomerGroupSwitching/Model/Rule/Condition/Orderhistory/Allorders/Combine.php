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
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders\Combine
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Rule\Block\Editable;
use Magento\Rule\Model\Condition\Context;
use Magento\Sales\Model\Order;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\Combine as ConditionCombine;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\AllordersFactory;
use Psr\Log\LoggerInterface;

class Combine extends ConditionCombine
{
    /**
     * @var AllordersFactory
     */
    protected $_orderhistoryAllordersFactory;

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

    public function __construct(
        Context $context,
        AllordersFactory $orderhistoryAllordersFactory,
        Editable $blockEditable,
        DesignInterface $viewDesignInterface,
        array $data = []
    )
    {
        $this->_orderhistoryAllordersFactory = $orderhistoryAllordersFactory;
        $this->_logLoggerInterface = $context->getLogger();
        $this->_blockEditable = $blockEditable;
        $this->_viewDesignInterface = $viewDesignInterface;
        $this->_dateTimeTimezoneInterface = $context->getLocaleDate();

        parent::__construct($context, $data);
        $this->initType();
    }

    public function initType()
    {
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders\Combine');
        return $this;
    }

    /**
     * Get attributes for cart history condtions
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //Get orderhistory / cart attributes
        $orderhistoryCondition = $this->_orderhistoryAllordersFactory->create();
        $orderhistoryAttributes = $orderhistoryCondition->loadAttributeOptions()->getAttributeOption();
        $oAttributes = [];
        $bAttributes = [];
        $sAttributes = [];
        foreach ($orderhistoryAttributes as $code=>$label) {
            if (stristr($code, 'billingattr')) {
                $bAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders|'.$code, 'label'=>$label];
            }elseif(stristr($code,'shippingattr')){
                $sAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders|'.$code, 'label'=>$label];
            } else {
                $oAttributes[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders|'.$code, 'label'=>$label];
            }
        }

        //set attributes
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders\Combine', 'label'=>__('Conditions Combination')],
            ['label'=>__('Order Attributes'), 'value'=>$oAttributes],
            ['label'=>__('Billing Address Attributes'), 'value'=>$bAttributes],
            ['label'=>__('Shipping Address Attributes'), 'value'=>$sAttributes],
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
     * @param $orderhistoryCollection
     * @return $this
     */
    public function collectValidatedAttributes($orderhistoryCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($orderhistoryCollection);
        }
        return $this;
    }

    /**
     * @param Order $order
     */
    protected function addCustomOrderAttributesToOrder($order)
    {
        $order
            ->setBaseTotalInvoicedExTax($this->_getBaseTotalInvoicedExTaxForOrder($order))
            ->setBaseTotalInvoicedMinusRefunded($this->_getBaseTotalInvoicedMinusRefundedForOrder($order))
            ->setBaseTotalInvoicedMinusRefundedExTax($this->_getBaseTotalInvoicedMinusRefundedExTaxForOrder($order))
            ->setBaseTotalPaidMinusRefunded($this->_getBaseTotalPaidMinusRefundedForOrder($order));

        if($order->getPayment()){
            $order->setPaymentMethod(
                $order->getPayment()->getMethod()
            );
        }
    }

    protected function _getBaseTotalInvoicedExTaxForOrder($order)
    {
        return $order->getBaseTotalInvoiced() - $order->getBaseTaxInvoiced();
    }

    protected function _getBaseTotalInvoicedMinusRefundedForOrder($order)
    {
        return $order->getBaseTotalInvoiced() - $order->getBaseTotalRefunded();
    }

    protected function _getBaseTotalInvoicedMinusRefundedExTaxForOrder($order)
    {
        return $this->_getBaseTotalInvoicedMinusRefundedForOrder($order) + $order->getBaseTaxRefunded() - $order->getBaseTaxInvoiced();
    }

    protected function _getBaseTotalPaidMinusRefundedForOrder($order)
    {
        return $order->getBaseTotalPaid() - $order->getBaseTotalRefunded();
    }
}
