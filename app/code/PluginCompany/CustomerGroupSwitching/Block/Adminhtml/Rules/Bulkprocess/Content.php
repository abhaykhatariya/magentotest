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

namespace PluginCompany\CustomerGroupSwitching\Block\Adminhtml\Rules\Bulkprocess;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use PluginCompany\CustomerGroupSwitching\Model\RuleFactory;
use PluginCompany\CustomerGroupSwitching\Model\RuleRepository;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class Content
    extends Template
{
    private $ruleFactory;
    private $customerCollectionFactory;
    private $ruleRepository;

    private $ruleCollection;
    private $filteredRuleCollection;

    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        RuleRepository $ruleRepository,
        CollectionFactory $customerCollectionFactory,
        array $data = []
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context, $data);
    }


    public function getRules()
    {
        return $this->getFilteredRuleCollection();
    }

    public function getRuleCollection()
    {
        if(!$this->ruleCollection){
            $this->ruleCollection = $this->getNewRuleCollection();
        }
        return $this->ruleCollection;
    }

    public function getFilteredRuleCollection()
    {
        if(!$this->filteredRuleCollection){
            $this->filteredRuleCollection = $this->getNewFilteredRuleCollection();
        }
        return $this->filteredRuleCollection;
    }

    private function getNewFilteredRuleCollection()
    {
        $collection = $this->getNewRuleCollection();
        $collection
            ->addFieldToFilter(
                'rule_id',
                ['in' => $this->getRuleIds()]
            );
        return $collection;
    }

    private function getNewRuleCollection()
    {
        return $this->ruleFactory->create()
            ->getCollection()
            ->setOrder('sort_order','ASC')
        ;
    }


    public function getRuleIds(){
        $ruleIds = $this->getRequest()->getParam('selected', []);

        if(empty($ruleIds) && !$this->hasRuleFilter()){
            return $this->getAllRuleIds();
        };

        if($ruleIds && !is_array($ruleIds)){
            $ruleIds = [$ruleIds];
        }
        return $ruleIds;
    }

    private function hasRuleFilter()
    {
        $excluded = $this->getRequest()->getParam('excluded');
        if($excluded == "false"){
            return false;
        }
        return true;
    }

    private function getAllRuleIds()
    {
        return $this->ruleRepository->getAllIds();
    }

    public function getTotalCustomerCount()
    {
        return $this->customerCollectionFactory->create()
            ->count();
    }
}
