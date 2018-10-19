<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search;

use Amasty\ElasticSearch\Api\RelevanceRuleRepositoryInterface;
use Amasty\ElasticSearch\Model\Client\ClientRepositoryInterface;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Amasty\ElasticSearch\Model\Search\GetResponse\GetAggregations;

class Adapter implements AdapterInterface
{
    /**
     * @var GetRequestQuery
     */
    private $getRequestQuery;

    /**
     * @var GetResponse
     */
    private $getElasticResponse;

    /**
     * @var GetAggregations
     */
    private $getAggregations;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var RelevanceRuleRepositoryInterface
     */
    private $relevanceRuleRepository;

    public function __construct(
        ClientRepositoryInterface $clientRepository,
        GetAggregations $getAggregations,
        GetRequestQuery $getRequestQuery,
        GetResponse $getElasticResponse,
        RelevanceRuleRepositoryInterface $relevanceRuleRepository
    ) {
        $this->getAggregations = $getAggregations;
        $this->getRequestQuery = $getRequestQuery;
        $this->getElasticResponse = $getElasticResponse;
        $this->clientRepository = $clientRepository;
        $this->relevanceRuleRepository = $relevanceRuleRepository;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\Search\Response\QueryResponse
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function query(RequestInterface $request)
    {
        $client = $this->clientRepository->get();
        $requestQuery = $this->getRequestQuery->execute($request);
        $elasticResponse = $client->search($requestQuery);
        $elasticDocuments = isset($elasticResponse['hits']['hits']) ? $elasticResponse['hits']['hits'] : [];
        $aggregations = $this->getAggregations->execute($request, $elasticResponse);
        $responseQuery = $this->getElasticResponse->execute($elasticDocuments, $aggregations);
        if (in_array($request->getName(), ['quick_search_container', 'catalogsearch_fulltext'], true)) {
            $productIds = array_map(function ($item) {
                return (int)$item['_id'];
            }, $elasticResponse['hits']['hits']);
            $responseQuery = $this->applyRelevanceRules($responseQuery, $productIds);
        }

        return $responseQuery;
    }

    /**
     * @param \Magento\Framework\Search\Response\QueryResponse $responseQuery
     * @param int[] $productIds
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    private function applyRelevanceRules(\Magento\Framework\Search\Response\QueryResponse $responseQuery, $productIds)
    {
        if ($responseQuery->count()) {
            $boostMultipliers = $this->relevanceRuleRepository->getProductBoostMultipliers($productIds);
            foreach ($responseQuery->getIterator() as $document) {
                if (isset($boostMultipliers[$document->getId()])) {
                    $score = $boostMultipliers[$document->getId()] * $document->getCustomAttribute('score')->getValue();
                    $document->getCustomAttribute('score')->setValue($score);
                }
            }
        }

        return $responseQuery;
    }
}
