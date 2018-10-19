<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Popular extends AbstractSearch
{
    const CATEGORY_BLOCK_POPULAR = 'popular_searches';

    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_POPULAR;
    }

    /**
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     */
    protected function generateCollection()
    {
        return $this->getSuggestCollection();
    }

    /**
     * @inheritdoc
     */
    protected function prepareCollection()
    {
        $this->getSearchCollection()
            ->setPageSize($this->getLimit())
            ->load();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getQueryText());
    }
}
