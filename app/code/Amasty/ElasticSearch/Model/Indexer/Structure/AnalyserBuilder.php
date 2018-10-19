<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Structure;

use Amasty\ElasticSearch\Api\Data\Indexer\Structure\AnalyzerBuilderInterface;
use Amasty\ElasticSearch\Api\StopWordRepositoryInterface;

class AnalyserBuilder implements AnalyzerBuilderInterface
{
    /**
     * @var StopWordRepositoryInterface
     */
    private $stopWordRepository;

    public function __construct(
        StopWordRepositoryInterface $stopWordRepository
    ) {
        $this->stopWordRepository = $stopWordRepository;
    }

    /**
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build($storeId)
    {
        $analyser = [
            'analyzer' => [
                //"the A*b-1^2 O'Neil's" => ["ab12", "oneil"]
                'default' => [
                    'type'      => 'custom',
                    'tokenizer' => 'whitespace',
                    'filter'    => [
                        'lowercase',
                        'stop',
                        'word_delimiter',
                    ],
                ],
                //"the A*b-1^2 O'Neil's" => ["a*b-1^2", "o'neil's"]
                'allow_spec_chars' => [
                    'type'      => 'custom',
                    'tokenizer' => 'whitespace',
                    'filter'    => [
                        'lowercase',
                        'stop'
                    ],
                ]
            ],
            'filter'   => [
                'word_delimiter' => [
                    // https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-word-delimiter-tokenfilter.html
                    'type'                    => 'word_delimiter',
                    'catenate_all'            => true,
                    'catenate_words'          => false,
                    'catenate_numbers'        => false,
                    //^ catenate all
                    'generate_word_parts'     => false,
                    'generate_number_parts'   => false,
                    'split_on_case_change'    => false,
                    'preserve_original'       => false,
                    'split_on_numerics'       => false,
                    'stem_english_possessive' => true,
                ],
            ],
        ];

        return $analyser;
    }
}
