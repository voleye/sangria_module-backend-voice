<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Gateway\Http;

use Laminas\Http\Response;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Client
{
    /**
     * @var string
     */
    const XPATH_GOOGLE_KEY = 'sangria_backend_voice_settings/general/google_key';
    /**
     * @var string
     */
    const HEADER_PARAM_CONTENT_TYPE = 'Content-Type';
    /**
     * @var CurlFactory
     */
    private $adapterFactory;
    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Client constructor.
     *
     * @param CurlFactory $adapterFactory
     * @param Json $jsonSerializer
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        CurlFactory $adapterFactory,
        Json $jsonSerializer,
        ScopeConfigInterface $config
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->config = $config;
    }

    public function recognize($data)
    {
        $adapter = $this->adapterFactory->create();

        $requestBody = $this->jsonSerializer->serialize($data);

        $adapter->setOptions($this->getOptions());
        $adapter->write(
            'POST',
            'https://speech.googleapis.com/v1/speech:recognize?key=' . $this->config->getValue(self::XPATH_GOOGLE_KEY),
            '1.1',
            $this->getHeaders(),
            $requestBody
        );

        $response = $adapter->read();

        $content = Response::fromString($response)->getContent();
        return $this->jsonSerializer->unserialize($content);
    }

    /**
     * @return int[]
     */
    private function getOptions(): array
    {
        return [
            CURLOPT_TIMEOUT => 30,
        ];
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        $headers = array_merge(
            [
                self::HEADER_PARAM_CONTENT_TYPE => 'application/json',
            ],
            []
        );

        $result = [];
        foreach ($headers as $key => $value) {
            $result[] = sprintf('%s: %s', $key, $value);
        }

        return $result;
    }
}
