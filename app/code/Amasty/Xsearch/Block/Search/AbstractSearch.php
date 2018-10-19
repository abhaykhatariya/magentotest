<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

use Magento\Framework\View\Element\Template;
use Amasty\Xsearch\Controller\RegistryConstants;

abstract class AbstractSearch extends Template
{
    /**
     * @var \Zend\ServiceManager\FactoryInterface
     */
    private $searchCollection;

    /**
     * \Magento\Search\Model\Query
     */
    private $query;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $xSearchHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $stringUtils;

    public function __construct(
        Template\Context $context,
        \Amasty\Xsearch\Helper\Data $xSearchHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->xSearchHelper = $xSearchHelper;
        $this->stringUtils = $string;
        $this->queryFactory = $queryFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_template = 'search/common.phtml';
        parent::_construct();
    }

    /**
     * @return string
     */
    abstract public function getBlockType();

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function generateCollection()
    {
        $collection = $this->getData('collectionFactory')->create();
        return $collection;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/title');
    }

    /**
     * @return string
     */
    public function getLimit()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/limit');
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getLoadedSearchCollection()
    {
        return $this->getSearchCollection();
    }

    /**
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     */
    protected function getSuggestCollection()
    {
        return $this->queryFactory->get()->getSuggestCollection();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function getSearchCollection()
    {
        if ($this->searchCollection === null) {
            $this->searchCollection = $this->generateCollection();
        }

        return $this->searchCollection;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getName());
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemTitle(\Magento\Framework\DataObject $item)
    {
        return $this->getName($item);
    }

    /**
     * @param $name
     * @return string
     */
    protected function generateName($name)
    {
        $text = $this->stripTags($name, null, true);

        $nameLength = $this->getNameLength();
        if ($nameLength && $this->stringUtils->strlen($text) > $nameLength) {
            $text = $this->stringUtils->substr($text, 0, $nameLength) . '...';
        }

        return $this->highlight($text);
    }

    /**
     * @param string $text
     * @return string
     */
    protected function highlight($text)
    {
        if ($this->getQuery()) {
            $text = $this->xSearchHelper->highlight($text, $this->getQuery()->getQueryText());
        }

        return $text;
    }

    /**
     * @return \Magento\Search\Model\QueryInterface
     */
    protected function getQuery()
    {
        if (null === $this->query) {
            $this->query = $this->coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY)
                ? $this->coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY)
                : $this->queryFactory->get();
            if ($this->query->getStrippedQueryText()) { //always use stripped query for all entities except products
                $this->query->setQueryText($this->query->getStrippedQueryText());
            }
        }

        return $this->query;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getSearchUrl(\Magento\Framework\DataObject $item)
    {
        if ($item instanceof \Magento\Cms\Model\Page) {
            $url = $this->_urlBuilder->getUrl(null, ['_direct' => $item->getIdentifier()]);
        } else {
            $url = $item->getUrl() ? $item->getUrl() : $this->xSearchHelper->getResultUrl($item->getQueryText());
        }

        return $url;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return bool
     */
    public function showDescription(\Magento\Framework\DataObject $item)
    {
        return false;
    }

    /**
     * @return string
     */
    public function getNameLength()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/name_length');
    }

    /**
     * @return string
     */
    public function getDescLength()
    {
        return $this->xSearchHelper->getModuleConfig($this->getBlockType() . '/desc_length');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->prepareCollection();
        return parent::_beforeToHtml();
    }

    /**
     * @return void
     */
    abstract protected function prepareCollection();

    /**
     * @param $currentHtml
     */
    protected function replaceVariables(&$currentHtml)
    {
        $currentHtml = preg_replace('@\{{(.+?)\}}@', '', $currentHtml);
    }

    /**
     * @param $descStripped
     * @param bool $descLength
     * @return string
     */
    public function getHighlightText($descStripped)
    {
        $text = $this->stringUtils->strlen($descStripped) > $this->getDescLength()
            ? $this->stringUtils->substr($descStripped, 0, $this->getDescLength()) . '...'
            : $descStripped;

        return $this->highlight($text);
    }
}
