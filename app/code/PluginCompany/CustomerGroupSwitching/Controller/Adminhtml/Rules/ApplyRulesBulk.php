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

namespace PluginCompany\CustomerGroupSwitching\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use PluginCompany\CustomerGroupSwitching\Model\RuleFactory;

class ApplyRulesBulk extends Action
{
    const ADMIN_RESOURCE = 'PluginCompany_CustomerGroupSwitching::rules';
    /**
     * @var Registry
     */
    private $frameworkRegistry;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        Context $context,
        Registry $frameworkRegistry,
        RuleFactory $modelRuleFactory
    )
    {

        $this->frameworkRegistry = $frameworkRegistry;
        $this->ruleFactory = $modelRuleFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $this->registerBulkSwitch();

        $this
            ->getResponse()
            ->setHeader('Content-type','application/json',true)
            ->setBody(
                json_encode(
                    $this->executeRulesAndGetResult()
                )
            )
        ;
    }

    private function registerBulkSwitch()
    {
        $this->frameworkRegistry
            ->register('is_groupswitch_bulk',1)
        ;
        return $this;
    }

    private function executeRulesAndGetResult()
    {
        $result = $this->applyRules();
        if(!count($result)){
            $result = 'done';
        }
        return $result;
    }

    private function applyRules()
    {
        return $this->ruleFactory->create()
            ->applySelectedRulesForCustomerRange(
                $this->getOffset(),
                $this->getLimit(),
                $this->getRuleIds()
            );
    }

    private function getRuleIds()
    {
        return $this->getRequest()
            ->getParam('ruleIds')
            ;
    }

    private function getOffset()
    {
        return $this->getRequest()->getParam('offset');
    }

    private function getLimit()
    {
        return 10;
    }
}
