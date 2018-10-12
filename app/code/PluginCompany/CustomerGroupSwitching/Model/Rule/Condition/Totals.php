<?php
/**
 *
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
 *
 */
namespace PluginCompany\CustomerGroupSwitching\Model\Rule\Condition;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Rule\Block\Editable;
use Magento\Rule\Model\Condition\Context;
use PluginCompany\CustomerGroupSwitching\Model\Rule\Condition\Compatibility\AbstractCondition;

class Totals extends AbstractCondition
{
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
        Editable $blockEditable, 
        DesignInterface $viewDesignInterface,
        array $data = [])
    {
        $this->_blockEditable = $blockEditable;
        $this->_viewDesignInterface = $viewDesignInterface;
        $this->_dateTimeTimezoneInterface = $context->getLocaleDate();

        parent::__construct($context, $data);
    }

    protected $_typeName;

    /**
     * Generate condition rhtml
     *
     * @return string
     */
    public function asHtml()
    {
        $identifier = $this->getPrefix() . '_' . $this->getId() . '_';

        $displayBetween = $displayLessdays = $displayMoredays = 'display:none';
        switch($this->getPeriodtype()){
            case 'between':
                $displayBetween = '';
                break;
            case 'lessdaysago':
                $displayLessdays = '';
                break;
            case 'moredaysago':
                $displayMoredays = '';
                break;
        }

        $html = 'Sum of ' . $this->_typeName
            . $this->getTypeElementHtml()
            . $this->getAttributeElementHtml()
            . "in the order history"
            . $this->getPeriodTypeDropdown()->toHtml()
            . "<span style='$displayBetween' id='"  . $identifier  . "between'>"
            . $this->getFromDateElement()->getHtml()  . " and " . $this->getToDateElement()->getHtml()
            . "</span>"
            . "<span style='$displayLessdays' id='" . $identifier . "lessdaysago'>"
            . $this->getRecentDaysElement()->getHtml() . " days"
            . "</span>"
            . "<span style='$displayMoredays' id='" . $identifier . "moredaysago'>"
            . $this->getBeforeDaysElement()->getHtml() . "days"
            . "</span>"
            . $this->getOperatorElementHtml()
            . $this->getValueElementHtml()
            . $this->getRemoveLinkHtml()
            . $this->getChooserContainerHtml();
        return $html;
    }


    /**
     * Conditions as array
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
     * @return string
     */
    public function asXml()
    {
        $xml = "<type>".$this->getType()."</type>"
            ."<periodtype>".$this->getPeriodtype()."</periodtype>"
            ."<recentdays>".$this->getRecentdays()."</recentdays>"
            ."<beforedays>".$this->getBeforedays()."</beforedays>"
            ."<fromdate>".$this->getFromdate()."</fromdate>"
            ."<todate>".$this->getTodate()."</todate>"
            ."<attribute>".$this->getAttribute()."</attribute>"
            ."<operator>".$this->getOperator()."</operator>"
            ."<value>".$this->getValue()."</value>";
        return $xml;
    }

    /**
     * Load conditions array
     *
     * @param $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key='conditions'){
        parent::loadArray($arr);
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
        return $this;
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
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string'      => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'],
                'numeric'     => ['==', '!=', '>=', '>', '<=', '<'],
                'date'        => ['==', '>=', '<='],
                'select'      => ['==', '!='],
                'boolean'     => ['==', '!='],
                'multiselect' => ['{}', '!{}', '()', '!()'],
                'grid'        => ['()', '!()'],
            ];
            $this->_arrayInputTypes = ['multiselect', 'grid'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Get input type for comparison operator
     *
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * Get value input renderer
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

}
