<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\CatalogSearch\Controller\Result;

use Magento\Search\Model\QueryFactory;

class Index
{
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Search\Helper\Data
     */
    private $searchHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Amasty\Xsearch\Helper\Data $helper,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->searchHelper = $searchHelper;
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(
        $subject,
        \Closure $proceed
    ) {
        $seoKey = $this->helper->getSeoKey();
        $identifier = trim($this->request->getPathInfo(), '/');
        $identifier = explode('/', $identifier);
        $identifier = array_shift($identifier);

        if (!$this->request->isForwarded()
            && $this->helper->isSeoUrlsEnabled()
            && $seoKey
            && $seoKey != $identifier
        ) {
            // redirect to seo url
            $url = $this->searchHelper->getResultUrl($this->request->getParam(QueryFactory::QUERY_VAR_NAME));
            $subject->getResponse()->setRedirect($url);
        } else {
            return $proceed();
        }
    }
}
