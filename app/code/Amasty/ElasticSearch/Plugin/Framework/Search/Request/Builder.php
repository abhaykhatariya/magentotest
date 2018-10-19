<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Framework\Search\Request;

use Amasty\ElasticSearch\Model\Config;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Magento\Framework\Search\Request\Builder as MagentoRequestBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogInventory\Model\Stock;

class Builder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param MagentoRequestBuilder $subject
     * @return array
     */
    public function beforeCreate(MagentoRequestBuilder $subject)
    {
        if ($this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH) == Config::ELASTIC_SEARCH_ENGINE
            && !$this->scopeConfig->isSetFlag('cataloginventory/options/show_out_of_stock')
        ) {
            $subject->bind('stock_status', Stock::STOCK_IN_STOCK);
        }
        return [];
    }
}