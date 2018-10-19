<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Category extends AbstractSearch
{
    const CATEGORY_BLOCK_TYPE = 'category';

    /**
     * @var array
     */
    private $categoryData;

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_TYPE;
    }

    /**
     * @inheritdoc
     */
    protected function prepareCollection()
    {
        $rootId = $this->_storeManager->getStore()->getRootCategoryId();
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->getSearchCollection()
            ->setStoreId($storeId)
            ->addNameToResult()
            ->addIsActiveFilter()
            ->addAttributeToSelect('description')
            ->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%'])
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->setPageSize($this->getLimit());
        $collection->load();
    }

    /**
     * @inheritdoc
     */
    public function showDescription(\Magento\Framework\DataObject $item)
    {
        return $this->stringUtils->strlen($item->getDescription()) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getItemTitle(\Magento\Framework\DataObject $item)
    {
        $path = array_reverse(explode(',', $item->getPathInStore()));
        $categoryTitle = '';
        $titles = $this->getCategoryData();
        foreach ($path as $id) {
            if (!empty($titles[$id])) {
                $categoryTitle .= $titles[$id]['name'];
                $categoryTitle .= ($id !== $item->getId()) ? ' — ' : '';
            }
        }

        return $categoryTitle ?: $item->getName();
    }

    /**
     * @return array
     */
    private function getCategoryData()
    {
        if ($this->categoryData === null) {
            $this->categoryData = [];
            $collection = $this->getData('categoryCollectionFactory')
                ->create()
                ->addNameToResult();
            foreach ($collection as $category) {
                $this->categoryData[$category->getId()] = [
                    'name' => $category->getName(),
                    'url' => $category->getUrl()
                ];
            }

        }

        return $this->categoryData;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(\Magento\Framework\DataObject $category)
    {
        $descStripped = $this->stripTags($category->getDescription(), null, true);

        return $this->getHighlightText($descStripped);
    }

    /**
     * @return bool
     */
    public function getIsCategoryBlock()
    {
        return true;
    }

    /**
     * @param $item
     * @return string
     */
    public function renderFullCategoryPath($item)
    {
        $path = array_reverse(explode(',', $item->getPathInStore()));
        $categoryTitle = '';
        $data = $this->getCategoryData();
        foreach ($path as $id) {
            if (!empty($data[$id])) {
                $categoryTitle .= sprintf(
                    '<a href="%1$s" class="am-item-link" title="%2$s">%2$s</a>',
                    $data[$id]['url'],
                    $data[$id]['name']
                );
            }
        }

        return $categoryTitle ?: $item->getName();
    }
}
