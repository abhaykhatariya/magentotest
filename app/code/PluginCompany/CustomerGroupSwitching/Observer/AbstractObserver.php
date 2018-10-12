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
/*
 * Class \PluginCompany\CustomerGroupSwitching\Observer\AbstractObserver
 */
namespace PluginCompany\CustomerGroupSwitching\Observer;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use PluginCompany\CustomerGroupSwitching\Model\CronflagFactory;
use PluginCompany\CustomerGroupSwitching\Model\RuleFactory;
use Psr\Log\LoggerInterface;

abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $frameworkRegistry;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CronflagFactory
     */
    protected $cronflagFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * AbstractObserver constructor.
     * @param Registry $frameworkRegistry
     * @param RuleFactory $modelRuleFactory
     * @param LoggerInterface $logLoggerInterface
     * @param CustomerFactory $modelCustomerFactory
     * @param CronflagFactory $modelCronflagFactory
     * @param ScopeConfigInterface $configScopeConfigInterface
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(
        Registry $frameworkRegistry,
        RuleFactory $modelRuleFactory,
        LoggerInterface $logLoggerInterface,
        CustomerFactory $modelCustomerFactory,
        CronflagFactory $modelCronflagFactory,
        ScopeConfigInterface $configScopeConfigInterface,
        CustomerRegistry $customerRegistry
    ) {
        $this->frameworkRegistry = $frameworkRegistry;
        $this->ruleFactory = $modelRuleFactory;
        $this->logger = $logLoggerInterface;
        $this->customerFactory = $modelCustomerFactory;
        $this->cronflagFactory = $modelCronflagFactory;
        $this->scopeConfig = $configScopeConfigInterface;
        $this->customerRegistry = $customerRegistry;
    }

    protected function _isCron(){
        return $this->frameworkRegistry->registry('is_groupswitch_cron');
    }
    protected function _isBulk(){
        return $this->frameworkRegistry->registry('is_groupswitch_bulk');
    }

}
