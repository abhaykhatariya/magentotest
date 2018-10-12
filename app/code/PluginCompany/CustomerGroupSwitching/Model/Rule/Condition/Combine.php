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
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Combine
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition;

use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\Combine as ConditionCombine;
use Magento\Rule\Model\Condition\Context;

class Combine extends ConditionCombine
{
    /**
     * @var OrderFactory
     */
    protected $_conditionOrderFactory;

    /**
     * @var InvoiceFactory
     */
    protected $_conditionInvoiceFactory;

    /**
     * @var ShippingFactory
     */
    protected $_conditionShippingFactory;

    /**
     * @var BillingFactory
     */
    protected $_conditionBillingFactory;

    /**
     * @var CustomerFactory
     */
    protected $_conditionCustomerFactory;

    public function __construct(
        Context $context,
        OrderFactory $conditionOrderFactory,
        InvoiceFactory $conditionInvoiceFactory, 
        ShippingFactory $conditionShippingFactory, 
        BillingFactory $conditionBillingFactory, 
        CustomerFactory $conditionCustomerFactory, 
        array $data = [])
    {
        $this->_conditionOrderFactory = $conditionOrderFactory;
        $this->_conditionInvoiceFactory = $conditionInvoiceFactory;
        $this->_conditionShippingFactory = $conditionShippingFactory;
        $this->_conditionBillingFactory = $conditionBillingFactory;
        $this->_conditionCustomerFactory = $conditionCustomerFactory;

        parent::__construct($context, $data);
        $this->setType('PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Combine');
    }

    /**
     * Get select options for rule generation
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        //get order attributes
        $orderCondition = $this->_conditionOrderFactory->create();
        $orderAttributes = $orderCondition->loadAttributeOptions()->getAttributeOption();
        $orderAttributesArray = [];
        foreach ($orderAttributes as $code=>$label) {
            $orderAttributesArray[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Order|'.$code, 'label'=>$label];
        }

        //get invoice attributes
        $invoiceCondition = $this->_conditionInvoiceFactory->create();
        $invoiceAttributes = $invoiceCondition->loadAttributeOptions()->getAttributeOption();
        $invoiceAttributesArray = [];
        foreach ($invoiceAttributes as $code=>$label) {
            $invoiceAttributesArray[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Invoice|'.$code, 'label'=>$label];
        }

        //get shipping address attributes
        $shippingCondition = $this->_conditionShippingFactory->create();
        $shippingAttributes = $shippingCondition->loadAttributeOptions()->getAttributeOption();
        $shippingAttributesArray = [];
        foreach ($shippingAttributes as $code=>$label) {
            $shippingAttributesArray[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Shipping|'.$code, 'label'=>$label];
        }

        //get billing address attributes
        $billingCondition = $this->_conditionBillingFactory->create();
        $billingAttributes = $billingCondition->loadAttributeOptions()->getAttributeOption();
        $billingAttributesArray = [];
        foreach ($billingAttributes as $code=>$label) {
            $billingAttributesArray[] = ['value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Billing|'.$code, 'label'=>$label];
        }

        //get customer attributes
        $customerCondition = $this->_conditionCustomerFactory->create();
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();
        $customerAttributesArray = [];
        foreach ($customerAttributes as $code=>$label) {
            $customerAttributesArray[] = [
                'value'=>'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Customer|' . $code,
                'label'=>$label
            ];
        }

        //get order history product attributes
        $productAllordersCondition = [
            'label' => 'Ordered Products (order history)',
            'value' => [
                ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Found', 'label' => __('Product attribute combination')],
                ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Allorders\Subselect', 'label' => __('Products subselection')],
            ]
        ];

        //get current cart product attributes
        $productNeworderCondtion = [
            'label' => 'Ordered Products (new orders)',
            'value' => [
                ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Found', 'label' => __('Product attribute combination')],
                ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Product\Neworder\Subselect', 'label' => __('Products subselection')],
            ]
        ];

        //get order history order attributes
        $orderhistoryAllordersCondition = [
            'label' => 'Single Order Totals (entire order history)',
            'value' => [
                ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders\Found', 'label' => __('Order attribute combination')],
                ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory\Allorders\Subselect', 'label' => __('Order subselection')],
            ]
        ];

        //set attributes
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value' => 'PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Combine', 'label' => __('Conditions combination')],
            ['label' => __('Customer Information'), 'value' => $customerAttributesArray],
            ['label' => __('Default Billing Address'), 'value' => $billingAttributesArray],
            ['label' => __('Default Shipping Address'), 'value' => $shippingAttributesArray],
            ['label' => __('Sum of Invoice Totals'), 'value' => $invoiceAttributesArray],
            ['label' => __('Sum of Order Totals'), 'value' => $orderAttributesArray],
            $orderhistoryAllordersCondition,
            $productAllordersCondition,
            $productNeworderCondtion
        ]);

        return $conditions;
    }
}
