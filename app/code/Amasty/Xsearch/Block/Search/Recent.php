<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Recent extends AbstractSearch
{
    const CATEGORY_BLOCK_RECENT = 'recent_searches';

    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_RECENT;
    }

    /**
     * @inheritdoc
     */
    protected function prepareCollection()
    {
        $collection = $this->getSearchCollection()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setRecentQueryFilter()
            ->setPageSize($this->getLimit());
        $collection
            ->getSelect()
            ->where('num_results > 0 AND display_in_terms = 1');
        $collection->load();
    }

    /**
     * @inheritdoc
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getQueryText());
    }
}
