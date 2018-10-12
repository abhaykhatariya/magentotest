<?php
namespace PluginCompany\CustomerGroupSwitching\Model;

use PluginCompany\CustomerGroupSwitching\Api\RuleRepositoryInterface;
use PluginCompany\CustomerGroupSwitching\Api\Data\RuleSearchResultsInterfaceFactory;
use PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use PluginCompany\CustomerGroupSwitching\Model\ResourceModel\Rule as ResourceRule;
use PluginCompany\CustomerGroupSwitching\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class RuleRepository implements RuleRepositoryInterface
{

    protected $resource;

    protected $ruleFactory;

    protected $ruleCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataRuleFactory;

    private $storeManager;


    /**
     * @param ResourceRule $resource
     * @param RuleFactory $ruleFactory
     * @param RuleInterfaceFactory $dataRuleFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param RuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceRule $resource,
        RuleFactory $ruleFactory,
        RuleInterfaceFactory $dataRuleFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        RuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->ruleFactory = $ruleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRuleFactory = $dataRuleFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface $rule
    ) {
        /* if (empty($rule->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $rule->setStoreId($storeId);
        } */
        try {
            $this->resource->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the rule: %1',
                $exception->getMessage()
            ));
        }
        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($ruleId)
    {
        $rule = $this->ruleFactory->create();
        $rule->load($ruleId);
        if (!$rule->getId()) {
            throw new NoSuchEntityException(__('Rule with id "%1" does not exist.', $ruleId));
        }
        return $rule;
    }

    public function getByIdOrNew($ruleId)
    {
        $rule = $this->ruleFactory->create();
        $rule->load($ruleId);
        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $collection = $this->ruleCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $items = [];
        
        foreach ($collection as $ruleModel) {
            $ruleData = $this->dataRuleFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $ruleData,
                $ruleModel->getData(),
                'PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface'
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $ruleData,
                'PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface'
            );
        }
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \PluginCompany\CustomerGroupSwitching\Api\Data\RuleInterface $rule
    ) {
        try {
            $this->resource->delete($rule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Rule: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        return $this->delete($this->getById($ruleId));
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        /** @var \PluginCompany\CustomerGroupSwitching\Model\ResourceModel\Rule\Collection $collection */
        $collection = $this->ruleCollectionFactory->create();
        return $collection->getAllIds();
    }
}
