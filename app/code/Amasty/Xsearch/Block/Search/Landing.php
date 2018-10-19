<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Landing extends AbstractSearch
{
    const CATEGORY_BLOCK_LANDING = 'landing_page';

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_LANDING;
    }

    /**
     * @inheritdoc
     */
    protected function prepareCollection()
    {
        $collection = $this->getSearchCollection()
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->addStoreFilter($this->_storeManager->getStore())
            ->addFieldToFilter('is_active', 1)
            ->setPageSize($this->getLimit());
        $collection->load();
    }

    /**
     * @inheritdoc
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getTitle());
    }

    /**
     * @inheritdoc
     */
    public function showDescription(\Magento\Framework\DataObject $page)
    {
        $description = $page->getLayoutTopDescription() ?: $page->getLayoutBottomDescription();
        $page->setContent($description);

        return $this->stringUtils->strlen($description) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $page)
    {
        $descStripped = $this->stripTags($page->getContent(), null, true);

        return $this->getHighlightText($descStripped);
    }
}
