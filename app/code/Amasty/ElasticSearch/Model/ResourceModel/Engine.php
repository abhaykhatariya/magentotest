<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

class Engine implements EngineInterface
{
    /**
     * @var ProductVisibility
     */
    private $productVisibility;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    public function __construct(
        ProductVisibility $productVisibility,
        IndexScopeResolver $indexScopeResolver
    ) {
        $this->productVisibility = $productVisibility;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedVisibility()
    {
        return $this->productVisibility->getVisibleInSiteIds();
    }

    /**
     * {@inheritdoc}
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * @return IndexScopeResolver
     */
    public function isAvailable()
    {
        return true;
    }
}