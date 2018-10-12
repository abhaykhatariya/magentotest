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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Orderhistory;

use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Config\Source\Allmethods as PaymentAllmethods;
use Magento\Shipping\Model\Config\Source\Allmethods as ShippingAllmethods;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Directory\Model\Config\Source\AllregionFactory;
use Magento\Directory\Model\Config\Source\CountryFactory;

class Allorders extends AbstractCondition
{
    /**
     * @var AllregionFactory
     */
    protected $_sourceAllregionFactory;

    /**
     * @var CountryFactory
     */
    protected $_sourceCountryFactory;

    /**
     * @var AttributeFactory
     */
    protected $_entityAttributeFactory;

    /** @var Allmethods */
    protected $sourceShippingMethods;

    /** @var PaymentAllmethods */
    protected $sourcePaymentMethods;


    /**
     * Allorders constructor.
     * @param Context $context
     * @param AttributeFactory $entityAttributeFactory
     * @param AllregionFactory $sourceAllregionFactory
     * @param CountryFactory $sourceCountryFactory
     * @param ShippingAllmethods $sourceShippingMethods
     * @param PaymentAllmethods $sourcePaymentMethods
     * @param array $data
     */
    public function __construct(
        Context $context,
        AttributeFactory $entityAttributeFactory,
        AllregionFactory $sourceAllregionFactory,
        CountryFactory $sourceCountryFactory,
        ShippingAllmethods $sourceShippingMethods,
        PaymentAllmethods $sourcePaymentMethods,
        array $data = []
    ) {
        $this->_entityAttributeFactory = $entityAttributeFactory;
        $this->_sourceAllregionFactory = $sourceAllregionFactory;
        $this->_sourceCountryFactory = $sourceCountryFactory;
        $this->sourceShippingMethods = $sourceShippingMethods;
        $this->sourcePaymentMethods = $sourcePaymentMethods;
        parent::__construct($context, $data);
    }


    /**
     * Load attribute options
     *
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'base_subtotal_incl_tax' => __('Subtotal (incl. Tax)'),
            'base_subtotal' => __('Subtotal (excl. Tax)'),
            'base_grand_total' => __('Grand Total (incl. Tax)'),
            'base_shipping_incl_tax' => __('Shipping Costs (incl. Tax)'),
            'base_shipping_amount' => __('Shipping Costs (excl. Tax)'),
            'base_discount_amount' => __('Discount Amount'),
            'base_shipping_discount_amount' => __('Shipping Discount Amount'),
            'base_tax_amount' => __('Tax Amount'),
            'tax_percent' => __('Tax Percentage'),
            'base_total_invoiced' => __('Total Invoiced (incl. Tax)'), //al
            'base_total_invoiced_ex_tax' => __('Total Invoiced (excl. Tax)'), //all
            'base_total_invoiced_minus_refunded' => __('Total Invoiced - Total Refunded (incl. Tax)'),
            'base_total_invoiced_minus_refunded_ex_tax' => __('Total Invoiced - Total Refunded (excl. Tax)'),
            'base_total_paid' => __('Total Paid'),
            'base_total_refunded' => __('Total Refunded'),
            'base_total_paid_minus_refunded' => __('Total Paid - Total Refunded'),
            'total_qty_ordered' => __('Total Items'),
            'weight' => __('Total Weight'),
            'coupon_code' => __('Coupon Code'),
            'coupon_rule_name' => __('Coupon Rule Name'),
            'state' => __('Order State'),
            'status' => __('Order Status'),
//            'is_virtual' => __('Is Virtual'),
            'entity_id' => __('Order ID'),
            'increment_id' => __('Order Increment ID'),
            'customer_taxvat' => __('VAT Number'),
            'order_currency_code' => __('Currency Code'),
            'shipping_method' => __('Shipping Method'),
            'payment_method' => __('Payment Method'),
//            'created_at' => __('Order Date'),
        ];

        $bAttributes =
            [
                'billingattr_region_id' => __('Region (select)'),
                'billingattr_region' => __('Region (text)'),
                'billingattr_postcode' => __('Postal Code'),
                'billingattr_street' => __('Street'),
                'billingattr_city' => __('City'),
                'billingattr_country_id' => __('Country'),
                'billingattr_vat_id' => __('VAT Number'),
                'billingattr_vat_is_valid' => __('VAT Number Valid'),
            ];

        $sAttributes =
            [
                'shippingattr_region_id' => __('Region (select)'),
                'shippingattr_region' => __('Region (text)'),
                'shippingattr_postcode' => __('Postal Code'),
                'shippingattr_street' => __('Street'),
                'shippingattr_city' => __('City'),
                'shippingattr_country_id' => __('Country'),
                'shippingattr_vat_id' => __('VAT Number'),
                'shippingattr_vat_is_valid' => __('VAT Number Valid'),
            ];

        $attributes = array_merge($attributes, $bAttributes, $sAttributes);

        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * Retrieve attribute object
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttributeObject()
    {
        try {
            $attrCode = $this->getAttribute();
            if(stristr($attrCode,'billingattr_')){
                $attrCode = str_replace('billingattr_','',$attrCode);
                $entityCode = 'order_address';
            }elseif(stristr($attrCode,'shippingattr_')){
                $attrCode = str_replace('shippingattr_','',$attrCode);
                $entityCode = 'order_address';
            }else{
                $entityCode = 'order';
            }

            if(in_array($attrCode,['country_id','region_id'])){
                $entityCode = 'customer_address';
            }
            $obj = $this->_entityAttributeFactory->create()->loadByCode($entityCode,$attrCode);
        }
        catch (\Exception $e) {
            return 'string';
        }
        if($attrCode == 'region_id'){
            $obj->setFrontendInput('select');
        }
        return $obj;
    }

    /**
     * Get value select options for rule generation
     *
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'shippingattr_region_id':
                case 'billingattr_region_id':
                    $options = $this->_sourceAllregionFactory->create()
                        ->toOptionArray();
                    break;

                case 'billingattr_country_id':
                case 'shippingattr_country_id':
                    $options = $this->_sourceCountryFactory->create()
                        ->toOptionArray();
                    break;
                case 'shipping_method':
                    $options = $this->sourceShippingMethods->toOptionArray(true);
                    break;
                case 'payment_method':
                    $options = $this->sourcePaymentMethods->toOptionArray(true);
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve attribute element
     *
     * @return \Magento\Framework\Form\Element\AbstractElement
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        if ($this->isSelectAttribute()) {
            return 'select';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * @return string
     */
    public function getAttributeName()
    {
        $attribute = $this->getAttribute();
        $name = $this->getAttributeOption($attribute);

        $prefix = '';
        if(stristr($attribute, 'shippingattr')){
            $prefix = __('Shipping Address ');
        }
        if(stristr($attribute, 'billingattr')){
            $prefix = __('Billing Address ');
        }
        return $prefix . $name->getText();
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->isSelectAttribute()) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    protected function isSelectAttribute()
    {
        return in_array($this->getAttribute(), $this->getCustomSelectAttributeCodes());
    }

    private function getCustomSelectAttributeCodes()
    {
        return [
            'attribute_set_id',
            'shipping_method',
            'payment_method'
        ];
    }
}
