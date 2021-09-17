<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model\Lookup;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

class MenuLookup implements ActionInterface
{
    /**
     * @var string
     */
    public const LOOKUP_TYPE = 2;

    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var ResourceConnection
     */
    private $connection;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * MenuLookup constructor.
     *
     * @param Json $jsonSerializer
     * @param ResourceConnection $connection
     * @param UrlInterface $url
     */
    public function __construct(
        Json $jsonSerializer,
        ResourceConnection $connection,
        UrlInterface $url
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->connection = $connection;
        $this->url = $url;
    }

    /**
     * @param array $response
     *
     * @return array|null
     */
    public function execute($response)
    {
        $q = $response['results'][0]['alternatives'][0]['transcript'] ?? '';

        $connection = $this->connection->getConnection();
        $dbData = $connection->fetchAll(
            "SELECT
                    *, MATCH (phrase) AGAINST (:q IN NATURAL LANGUAGE MODE) AS score
                FROM voice_dictionary t1
                WHERE type=:type and MATCH (phrase) AGAINST (:q IN NATURAL LANGUAGE MODE);
            ",
            [
                'q' => $q,
                'type' => self::LOOKUP_TYPE
            ]
        );
        $result = ['type' => self::LOOKUP_TYPE];
        if (isset($dbData[0])) {
            $phrase = $dbData[0]['phrase'] ?? null;
            $config = (array)$this->jsonSerializer->unserialize($dbData[0]['config']);
            $config['config']['url'] = $this->url->getUrl($config['config']['path']);

            $result['phrase'] = $phrase;
            $result['config'] = $config;
        }

        return $result;
    }
}
