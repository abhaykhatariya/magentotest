<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\External;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\EntityBuilderInterface;

class Finder implements EntityBuilderInterface
{
    const ATTRIBUTE_TYPE_KEYWORD    = 'keyword';
    const SKU_ATTRIBUTE = 'sku';

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildEntityFields()
    {
        return [self::SKU_ATTRIBUTE . '_value' => ['type' => self::ATTRIBUTE_TYPE_KEYWORD]];
    }
}
