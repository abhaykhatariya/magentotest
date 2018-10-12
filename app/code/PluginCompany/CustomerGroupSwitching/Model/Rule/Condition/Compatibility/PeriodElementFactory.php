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

use Magento\Rule\Model\Condition\AbstractCondition as RuleAbstractCondition;

class PeriodElementFactory
{
    private $layout;
    private $condition;
    private $periodType;
    private $dateType;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context
    ) {
        $this->layout = $context->getLayout();
        return $this;
    }

    /**
     * get xx days before input element
     * @return mixed
     * @throws \Exception
     */
    public function createDaysElement()
    {
        if(!$this->canCreateDaysElement()){
            throw new \Exception("condition and periodType are not set.");
        }
        $condition = $this->condition;
        $days = $condition->getData($this->periodType);

        if (is_null($days)) {
            $days = 30;
            $condition->setData($this->periodType, $days);
        }

        return $condition->getForm()->addField(
            $condition->getPrefix() . '__' . $condition->getId() . '__' . $this->periodType,
            'text',
            [
                'name'           => $this->getFieldName(),
                'value'          => $days,
                'value_name'     => $days,
                'required'       => true,
                'class'     => 'validate-number',
                'data-form-part' => $condition->getFormName()
            ]
        )->setRenderer(
            $this->layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    private function canCreateDaysElement()
    {
        return $this->condition && $this->periodType;
    }

    private function getFieldName()
    {
        return 'rule[' . $this->condition->getPrefix() . '][' . $this->condition->getId() . '][' . $this->periodType . ']';
    }

    public function createDateElement()
    {
        if(!$this->canCreateDateElement()){
            throw new \Exception("condition and dateType are not set.");
        }
        $condition = $this->condition;
        $date = $this->getDate();

        return $condition->getForm()->addField(
            $condition->getPrefix() . '__' . $condition->getId() . '__' . $this->dateType,
            'text',
            [
                'name'          => $this->getDateFieldName(),
                'value'          => $date,
                'value_name'     => $date,
                'explicit_apply' => true,
                'required'       => true,
                'class'          => 'date-input',
                'after_element_html' => $condition->getDatePickerJs(),
                'data-form-part' => $condition->getFormName()
            ]
        )->setRenderer(
            $this->layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
        return $condition;
    }

    private function canCreateDateElement()
    {
        return $this->condition && $this->dateType;
    }

    private function getDate()
    {
        $this->initDate();
        return $this->condition->getData($this->dateType);
    }

    private function initDate()
    {
        if (is_null($this->condition->getData($this->dateType))) {
            $this->condition->setData($this->dateType, date('Y-m-d'));
        }
        return $this;
    }

    private function getDateFieldName()
    {
        return 'rule[' . $this->condition->getPrefix() . ']'
            . '[' . $this->condition->getId() . ']'
            . '[' . $this->dateType . ']';
    }

    public function createPeriodTypeDropdown()
    {
        $identifier = $this->condition->getPrefix() . '_' . $this->condition->getId() . '_';
        if (is_null($this->condition->getPeriodtype())) {
            $this->condition->setPeriodtype('all');
        }

        $periodOptions = $this->condition->getPeriodOptions();
        return $this->condition->getForm()->addField(
            $this->condition->getPrefix().'__'.$this->condition->getId().'__periodtype',
            'select',
            [
                'name' =>'rule[' . $this->condition->getPrefix() . '][' .$this->condition->getId() . '][periodtype]',
                'value' => $this->condition->getPeriodtype(),
                'value_name' => $periodOptions[$this->condition->getPeriodtype()],
                'options' => $periodOptions,
                'required' => true,
                'onchange' => "hidePeriodValues('" . $identifier . "',this)",
                'data-form-part' => $this->condition->getFormName()
            ]
        )->setRenderer(
            $this->layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    /**
     * @param RuleAbstractCondition $condition
     * @return $this
     */
    public function setCondition(RuleAbstractCondition $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @param string $periodType
     * @return $this
     */
    public function setPeriodType($periodType)
    {
        $this->periodType = $periodType;
        return $this;
    }

    /**
     * @param string $dateType
     * @return $this
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
        return $this;
    }

    public function getDatePickerJs()
    {
        return $this->layout
            ->getBlockSingleton(
                'PluginCompany\CustomerGroupSwitching\Block\Adminhtml\Rules\Edit\Tab\Conditions\Renderer\Date'
            )
            ->toHtml();
    }

}