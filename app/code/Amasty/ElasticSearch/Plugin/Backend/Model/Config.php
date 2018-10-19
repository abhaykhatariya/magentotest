<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Backend\Model;

class Config
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Amasty\ElasticSearch\Model\Config $config
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->config = $config;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     * @return \Magento\Config\Model\Config
     */
    public function beforeSave(\Magento\Config\Model\Config $subject) {
        $groups = $subject->getGroups();
        $newLongTail = isset($groups['catalog']['fields']['long_tail']['value'])
            ? $groups['catalog']['fields']['long_tail']['value']
            : false;
        $oldLongTail = $this->config->getModuleConfig('catalog/long_tail');

        if ($newLongTail && $newLongTail !== $oldLongTail) {
            $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->invalidate();
        }

        return $subject;
    }
}