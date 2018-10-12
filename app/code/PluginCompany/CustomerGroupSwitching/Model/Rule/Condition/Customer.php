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
 * Class \PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Customer
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition;

use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Date;
use Magento\Framework\View\DesignInterface;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractCondition;

class Customer extends AbstractCondition
{
    /**
     * @var Config
     */
    protected $_modelConfig;

    /**
     * @var DesignInterface
     */
    protected $_viewDesignInterface;

    /** @var CustomerFactory */
    protected $customerFactory;

    public function __construct(
        Context $context,
        Config $modelConfig,
        DesignInterface $viewDesignInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_modelConfig = $modelConfig;
        $this->_viewDesignInterface = $viewDesignInterface;
        $this->customerFactory = $customerFactory;

        parent::__construct($context, $data);
    }


    /**
     * Check if attribute is allowed for rule creation
     * @param $attribute
     *
     * @return bool
     */
    protected function _canUseAttribute($attribute)
    {
        $excluded = [
            'store_id',
            'website_id',
            'created_in',
            'entity_type_id' ,
            'attribute_set_id' ,
            'updated_at' ,
            'group_id' ,
            'password_hash' ,
            'default_billing' ,
            'default_shipping' ,
            'rp_token',
            'rp_token_created_at' ,
            'reward_update_notification' ,
            'reward_warning_notification' ,
            'failures_num',
            'lock_expires',
            'first_failure'
        ];
        if(!in_array($attribute,$excluded)){
            return true;
        }
        return false;
    }

    /**
     * Set attribute options
     *
     * @return $this
     */
    protected function _getSpecialAttributes()
    {
        $attributes = [
            'newsletter' => __('Subscribed to newsletter'),
            'age' => __('Age in years'),
            'account_age' => __('Account age in days')
        ];

        return $attributes;
    }

    /**
     * Retrieve value by option
     *
     * @param mixed $option
     * @return string
     */
    public function getValueOption($option=null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option'.(!is_null($option) ? '/'.$option : ''));
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
    }

    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
            if(in_array($attributeObject->getAttributeCode(), ["disable_auto_group_change","newsletter","confirmation"])){
                $selectOptions = [
                    ['label' => 'Yes', 'value' => 1],
                    ['label' => 'No', 'value' => 0]
                ];
            }
        }
        

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

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
            $obj = $this->_modelConfig
                ->getAttribute('customer', $this->getAttribute());
        }
        catch (\Exception $e) {
            return 'string';
        }
        return $obj;
    }
    
    /**
     * Set attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->getAllCustomerAttributeCodes();
        $attributes = [];
        foreach ($customerAttributes as $attribute) {
            if(!$this->_canUseAttribute($attribute->getAttributeCode()) || !$attribute->getFrontendLabel()){
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $specialAttr = $this->_getSpecialAttributes();
        $attributes = array_merge($attributes,$specialAttr);

        $this->sortAttributes($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    private function sortAttributes(&$attributes)
    {
        foreach($this->getPriorityAttributes() as $key){
            if(!isset($attributes[$key])){
                continue;
            }
            $this->moveToTop($attributes, $key);
        }
    }

    private function getPriorityAttributes()
    {
        return array_reverse(
            [
                'firstname',
                'middlename',
                'lastname',
                'prefix',
                'suffix',
                'email',
                'gender',
                'dob',
                'age',
                'newsletter',
                'account_age',
            ]
        );
    }

    private function moveToTop(&$array, $key) {
        $temp = array($key => $array[$key]);
        unset($array[$key]);
        $array = $temp + $array;
    }

    public function getAllCustomerAttributeCodes()
    {
        return $this->getCustomerResource()
            ->loadAllAttributes()
            ->getAttributesByCode()
        ;
    }

    public function getCustomerResource()
    {
        return $this->customerFactory->create()->getResource();
    }

    /**
     * Get attribute element renderer
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
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if (in_array($this->getAttribute(),['account_age','age'])) {
            return 'numeric';
        }
        if(in_array($this->getAttribute(), ["disable_auto_group_change","newsletter","confirmation"])){
            return 'boolean';
        }
        
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
            case 'datetime':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if(in_array($this->getAttribute(), ["disable_auto_group_change","newsletter","confirmation"])){
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
            case 'datetime':
                return 'date';
            
            default:
                return 'text';
        }
    }
}
