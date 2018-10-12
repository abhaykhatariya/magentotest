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
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition;

use Magento\Directory\Model\Config\Source\AllregionFactory;
use Magento\Directory\Model\Config\Source\CountryFactory;
use Magento\Framework\DataObject;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractCondition;

class Billing extends AbstractCondition
{
    /**
     * @var CountryFactory
     */
    protected $_sourceCountryFactory;

    /**
     * @var AllregionFactory
     */
    protected $_sourceAllregionFactory;

    public function __construct(
        Context $context,
        CountryFactory $sourceCountryFactory, 
        AllregionFactory $sourceAllregionFactory, 
        array $data = [])
    {
        $this->_sourceCountryFactory = $sourceCountryFactory;
        $this->_sourceAllregionFactory = $sourceAllregionFactory;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve attribute options for rule creation
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'billing_city' => __('City'),
            'billing_postcode' => __('Postcode'),
            'billing_region' => __('State/Province (text)'),
            'billing_region_id' => __('State/Province (dropdown)'),
            'billing_country_id' => __('Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * HTML for rule creation
     *
     * @return string
     */
    public function asHtml()
    {
        $html = 'Default billing address'
            .$this->getTypeElementHtml()
            .$this->getAttributeElementHtml()
            .$this->getOperatorElementHtml()
            .$this->getValueElementHtml()
            .$this->getRemoveLinkHtml()
            .$this->getChooserContainerHtml();
        return $html;
    }

    /**
     * return element for attribute rendering
     *
     * @return mixed
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Return input type by attribute
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'billing_country_id': case 'billing_region_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get attribute element for value input
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'billing_country_id': case 'billing_region_id':
                return 'select';
        }
        return 'text';
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
                case 'billing_country_id':
                    $options = $this->_sourceCountryFactory->create()
                        ->toOptionArray();
                    break;

                case 'billing_region_id':
                    $options = $this->_sourceAllregionFactory->create()
                        ->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }
}
