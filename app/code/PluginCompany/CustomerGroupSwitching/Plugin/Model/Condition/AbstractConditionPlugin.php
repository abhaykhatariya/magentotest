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
namespace PluginCompany\CustomerGroupSwitching\Plugin\Model\Condition;

use Magento\Framework\DataObject;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractProduct;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\PeriodConfig;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\PeriodElementFactory;

class AbstractConditionPlugin
{
    private $subject;
    private $proceed;
    private $model;

    private $method;
    private $arguments;

    public function __construct(
        PeriodElementFactory $periodElementFactory,
        PeriodConfig $config
    ) {
        $this->periodElementFactory = $periodElementFactory;
        $this->config = $config;
    }

    public function aroundValidate(
        \Magento\Rule\Model\Condition\AbstractCondition $subject,
        callable $proceed,
        DataObject $model
    ) {
        $this->initVars(func_get_args());
        if(!$this->isGroupSwitchingClass()){
            return $proceed($model);
        }
        return $this->executeValidation();
    }

    private function initVars($vars)
    {
        $this->subject = $vars[0];
        $this->proceed = $vars[1];
        if(isset($vars[2])){
            $this->model = $vars[2];
        }
        return $this;
    }

    private function isGroupSwitchingClass()
    {
        return stristr(get_class($this->subject), 'PluginCompany\CustomerGroupSwitching');
    }

    private function executeValidation()
    {
        if(method_exists($this->subject, 'validateDataObject')){
            return $this->subject->validateDataObject($this->model);
        }
        if($this->subject instanceof AbstractProduct){
            return $this->proceed($this->model);
        }
        return $this->subject->validateAttribute(
            $this->getAttributeValue()
        );
    }

    private function proceed(...$args)
    {
        $proceed = $this->proceed;
        if($this->is__call()){
            $method = $this->method;
            $this->method = null;
            return $proceed($method, $args);
        }
        return $proceed(...$args);
    }

    private function is__call()
    {
        return (bool)$this->method;
    }

    private function getAttributeValue()
    {
        return $this->model->getData(
            $this->subject->getAttribute()
        );
        return $this;
    }

    public function aroundGetValue(
        \Magento\Rule\Model\Condition\AbstractCondition $subject,
        callable $proceed
    ){
        if(!$this->isGroupSwitchingClass()){
            return $proceed();
        }
        $this->subject = $subject;
        return $this->getValue();
    }

    private function getValue()
    {
        if ($this->subject->getInputType() == 'date' && !$this->subject->getIsValueParsed()) {
            $this->subject->setValue(
                (new \DateTime($this->subject->getData('value')))->format('Y-m-d')
            );
            $this->subject->setIsValueParsed(true);
        }
        return $this->subject->getData('value');
    }

    public function aroundGetValueElement(
        \Magento\Rule\Model\Condition\AbstractCondition $subject,
        callable $proceed
    ){
        if(!$this->isGroupSwitchingClass()){
            return $proceed();
        }
        $element = $proceed();
        if ($subject->getInputType() == 'date') {
            $element->setClass('date-input');
        }
        return $element;
    }

    public function around__call(
        \Magento\Rule\Model\Condition\AbstractCondition $subject,
        callable $proceed,
        $method,
        $arguments
    ) {
        if(!$this->isGroupSwitchingClass()){
            return $proceed($method, $arguments);
        }
        $this->initCallVars(...func_get_args());

        if(method_exists($this, $method)){
            return $this->{$method}(...$arguments);
        }

        return $proceed($method, $arguments);
    }

    private function initCallVars($subject, $proceed, $method, $arguments)
    {
        $this->subject = $subject;
        $this->proceed = $proceed;
        $this->method = $method;
        $this->arguments = $arguments;
        return $this;
    }

    public function aroundGetValueAfterElementHtml(
        \Magento\Rule\Model\Condition\AbstractCondition $subject,
        callable $proceed
    ){
        if(!$this->isGroupSwitchingClass()){
            return $proceed();
        }
        $this->initVars(func_get_args());
        return $this->getValueAfterElementHtml();
    }

    private function getValueAfterElementHtml()
    {
        if($this->subject->getInputType() == 'date'){
            return $this->subject->getDatePickerJs();
        }
        return $this->proceed();
    }

    private function getDatePickerJs()
    {
        return $this->getPeriodElementFactory()
            ->getDatePickerJs();
    }

    private function getPeriodElementFactory()
    {
        return $this->periodElementFactory->setCondition($this->subject);
    }

    private function getExplicitApply()
    {
        if (is_object($this->subject->getAttributeObject())) {
            switch ($this->subject->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    private function getPeriodOptions()
    {
        return $this->config->getPeriodOptions();
    }

    private function getPeriodTypeDropdown()
    {
        return $this->getPeriodElementFactory()
            ->createPeriodTypeDropdown();
    }

    private function getRecentDaysElement()
    {
        return $this->getDaysElement('recentdays');
    }

    private function getBeforeDaysElement()
    {
        return $this->getDaysElement('beforedays');
    }

    private function getDaysElement($type)
    {
        return $this->getPeriodElementFactory()
            ->setPeriodType($type)
            ->createDaysElement();
    }

    private function getFromDateElement()
    {
        return $this->getDateElement('fromdate');
    }

    private function getToDateElement()
    {
        return $this->getDateElement('todate');
    }

    private function getDateElement($type)
    {
        return $this->getPeriodElementFactory()
            ->setDateType($type)
            ->createDateElement();
    }

    /**
     * Get date range for current condition
     *
     * @return array
     */
    private function getPeriodRange()
    {
        $from = false;
        $to = false;
        switch($this->subject->getPeriodtype()){
            case 'all':
                $from = false;
                $to = false;
                break;
            case 'between':
                $from = $this->subject->getFromdate();
                $to = date('Y-m-d',strtotime($this->getTodate() . ' +1 day'));
                break;
            case 'lessdaysago':
                $to = false;
                $days = $this->subject->getRecentdays();
                if (!$days) {
                    $days = 0;
                }
                $from = date('Y-m-d', strtotime("-$days day"));
                break;
            case 'moredaysago':
                $from = false;
                $days = $this->subject->getBeforedays();
                if (!$days) {
                    $days = 0;
                }
                $to = date('Y-m-d', strtotime("-$days day"));
                break;
        }
        return ['from' => $from, 'to' => $to];
    }

}