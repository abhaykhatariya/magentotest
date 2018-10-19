<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DB\Select;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Amasty\Xsearch\Controller\RegistryConstants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

class Product extends ListProduct
{
    const XML_PATH_TEMPLATE_PRODUCT_LIMIT = 'product/limit';
    const XML_PATH_TEMPLATE_TITLE = 'product/title';
    const XML_PATH_TEMPLATE_NAME_LENGTH = 'product/name_length';
    const XML_PATH_TEMPLATE_DESC_LENGTH = 'product/desc_length';
    const XML_PATH_TEMPLATE_REVIEWS = 'product/reviews';
    const XML_PATH_TEMPLATE_ADD_TO_CART = 'product/add_to_cart';
    
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $xSearchHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $string;
    
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var RedirectInterface
     */
    private $redirector;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Amasty\Xsearch\Helper\Data $xSearchHelper,
        RedirectInterface $redirector,
        array $data = []
    ) {
        $this->xSearchHelper = $xSearchHelper;
        $this->string = $string;
        $this->formKey = $formKey;
        $this->redirector = $redirector;

        return parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    protected function _construct()
    {
        $this->_template = 'search/product.phtml';
        parent::_construct();
    }

    /**
     * @return $this
     */
    public function prepareCollection()
    {
        if ($this->_productCollection === null) {
            $this->_getProductCollection();
            $this->_productCollection->clear();
            $this->_productCollection->setPageSize($this->getLimit());
            $this->_productCollection->getSelect()
                ->reset(Select::ORDER)
                ->order('search_result.'. TemporaryStorage::FIELD_SCORE . ' ' . Select::SQL_DESC);

            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $this->_productCollection]
            );

            $this->_productCollection->load();
            $this->getQuery()->saveNumResults($this->_productCollection->getSize());
        }

        return $this;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    protected function _beforeToHtml()
    {
        $this->prepareCollection();
        return parent::_beforeToHtml();
    }

    public function getLimit()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_PRODUCT_LIMIT);
    }

    protected function getQuery()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_XSEARCH_QUERY);
    }

    public function getResultUrl()
    {
        return $this->xSearchHelper->getResultUrl($this->getQuery()->getQueryText());
    }

    public function highlight($text)
    {
        return $this->xSearchHelper->highlight($text, $this->getQuery()->getQueryText());
    }

    public function getTitle()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_TITLE);
    }

    public function getName($product)
    {
        $nameLength = $this->getNameLength();

        $productNameStripped = $this->stripTags($product->getName(), null, true);

        $text =
            $this->string->strlen($productNameStripped) > $nameLength ?
            $this->string->substr($productNameStripped, 0, $this->getNameLength()) . '...'
            : $productNameStripped;

        return $this->highlight($text);
    }

    public function getDescription($product)
    {
        $descLength = $this->getDescLength();
        $productDescStripped = $this->stripTags($product->getShortDescription(), null, true);

        $text =
            $this->string->strlen($productDescStripped) > $descLength ?
            $this->string->substr($productDescStripped, 0, $descLength) . '...'
            : $productDescStripped;

        return $this->highlight($text);
    }

    public function showDescription($product)
    {
        return $this->string->strlen($product->getShortDescription()) > 0;
    }

    public function getNameLength()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_NAME_LENGTH);
    }

    public function getDescLength()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_DESC_LENGTH);
    }

    public function getReviews()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_REVIEWS) == '1' ? 1 : 0;
    }

    public function getAddToCart()
    {
        return $this->xSearchHelper->getModuleConfig(self::XML_PATH_TEMPLATE_ADD_TO_CART) == '1'? 1 : 0;
    }

    protected function getPriceRender()
    {
        return $this->_layout->createBlock(
            'Magento\Framework\Pricing\Render',
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        
        return [
            'action' => $url,
            'data' => [
                'return_url' => $this->redirector->getRefererUrl(),
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    public function getUlrEncodedParam()
    {
        return Action::PARAM_NAME_URL_ENCODED;
    }
}
