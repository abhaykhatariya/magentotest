<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Blog extends AbstractSearch
{
    const BLOG_BLOCK_PAGE = 'blog';

    /**
     * @var AbstractCollection
     */
    private $postsSearchCollection;

    /**
     * @var AbstractCollection
     */
    private $categoriesSearchCollection;

    /**
     * @var AbstractCollection
     */
    private $tagsSearchCollection;

    /**
     * @return string
     */
    public function getBlockType()
    {
        return self::BLOG_BLOCK_PAGE;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $collection = $this->getData('collectionFactory')->create();
        foreach ($this->getPostsCollection() as $item) {
            $item->setUrl($item->getPostUrl());
            $this->addToBlogCollection($item, $collection);
        }

        foreach ($this->getCategoriesCollection() as $item) {
            $item->setUrl($item->getUrl());
            $this->addToBlogCollection($item, $collection);
        }

        foreach ($this->getTagsCollection() as $item) {
            $item->setUrl($item->getTagUrl());
            $this->addToBlogCollection($item, $collection);
        }

        return $collection;
    }

    /**
     * @return AbstractCollection
     */
    private function getPostsCollection()
    {
        if ($this->postsSearchCollection === null) {
            $this->postsSearchCollection = $this->getData('postsCollectionFactory')->create();
        }

        return $this->postsSearchCollection;
    }

    /**
     * @return AbstractCollection
     */
    private function getCategoriesCollection()
    {
        if ($this->categoriesSearchCollection === null) {
            $this->categoriesSearchCollection = $this->getData('categoriesCollectionFactory')->create();
        }

        return $this->categoriesSearchCollection;
    }

    /**
     * @return AbstractCollection
     */
    private function getTagsCollection()
    {
        if ($this->tagsSearchCollection === null) {
            $this->tagsSearchCollection = $this->getData('tagsCollectionFactory')->create();
        }

        return $this->tagsSearchCollection;
    }

    /**
     * @param $item
     * @param $collection
     */
    private function addToBlogCollection($item, &$collection)
    {
        $dataObject = $this->getData('dataObjectFactory')->create();
        $dataObject->setData($item->getData());
        $collection->addItem($dataObject);
    }

    protected function prepareCollection()
    {
        $collectionPosts = $this->getPostsCollection()
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->addStores()
            ->addFieldToFilter('status', 2)
            ->setPageSize($this->getLimit());
        $collectionPosts->load();

        $collectionCategories = $this->getCategoriesCollection()
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->addStores()
            ->addFieldToFilter('status', 1)
            ->setPageSize($this->getLimit());
        $collectionCategories->load();

        $collectionTags = $this->getTagsCollection()
            ->addSearchFilter($this->getQuery()->getQueryText())
            ->setPageSize($this->getLimit());
        $collectionTags->load();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        return $item->getUrl();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        if (isset($item['post_id'])) {
            $name = $item->getTitle();
        } else {
            $name = $item->getName();
        }

        return $this->generateName($name);
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return bool
     */
    public function showDescription(\Magento\Framework\DataObject $item)
    {
        return $this->stringUtils->strlen($item->getShortContent()) > 0;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getDescription(\Magento\Framework\DataObject $item)
    {
        $descStripped = $this->stripTags($item->getShortContent(), null, true);
        $this->replaceVariables($descStripped);

        return $this->getHighlightText($descStripped);
    }
}
