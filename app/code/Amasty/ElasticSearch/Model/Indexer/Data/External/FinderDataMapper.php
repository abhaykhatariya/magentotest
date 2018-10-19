<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Data\External;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;

class FinderDataMapper implements DataMapperInterface
{
    const ENTITY_TYPE = 'catalog_product';
    const SKU_ATTRIBUTE = 'sku';

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $eavAttribute;

    public function __construct(\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute)
    {
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $documents = [];
        $skuAttributeId = $this->eavAttribute->getIdByCode(self::ENTITY_TYPE, self::SKU_ATTRIBUTE);
        foreach ($documentData as $productId => $indexData) {
            if (isset($indexData[$skuAttributeId])) {
                if (is_array($indexData[$skuAttributeId])) {
                    $skuValue = isset($indexData[$skuAttributeId][$productId])
                        ? $indexData[$skuAttributeId][$productId] : '';
                } else {
                    $skuValue = $indexData[$skuAttributeId];
                }
                $documents[$productId][self::SKU_ATTRIBUTE . '_value'] = $skuValue;
            }
        }

        return $documents;
    }
}
