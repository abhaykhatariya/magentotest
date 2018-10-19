<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\ResourceModel\Category\Fulltext;

use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

class Collection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
{
    /** @var  QueryResponse */
    protected $queryResponse;

    /** @var string */
    private $queryText;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /** @var string */
    private $searchRequestName;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $mysqlAdapter;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Framework\Search\Adapter\Mysql\Adapter $mysqlAdapter,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $searchRequestName = 'amasty_xsearch_category'
    ) {

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $connection
        );

        $this->requestBuilder = $requestBuilder;
        $this->searchRequestName = $searchRequestName;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->mysqlAdapter = $mysqlAdapter;
    }

    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    protected function _renderFiltersBefore()
    {
        $this->requestBuilder->bindDimension('scope', $this->getStoreId());
        if ($this->queryText) {
            $this->requestBuilder->bind('search_term', $this->queryText);
        }

        $this->requestBuilder->setRequestName($this->searchRequestName);

        $queryRequest = $this->requestBuilder->create();

        $this->queryResponse = $this->mysqlAdapter->query($queryRequest);

        $temporaryStorage = $this->temporaryStorageFactory->create();

        $table = $temporaryStorage->storeDocuments($this->queryResponse->getIterator());

        $this->getSelect()->joinInner(
            [
               'search_result' => $table->getName(),
            ],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
            []
        );

        $this->getSelect()
            ->order('search_result.'. TemporaryStorage::FIELD_SCORE . ' DESC')
            ->order("e.entity_id ASC");

        return parent::_renderFiltersBefore();
    }
}
