<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model\Speech;

use Sangria\BackendVoice\Gateway\Http\Client;

class Recognizer
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Recognizer constructor.
     *
     * @param Client $client
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function recognize($data)
    {
        return $this->client->recognize($data);
    }
}
