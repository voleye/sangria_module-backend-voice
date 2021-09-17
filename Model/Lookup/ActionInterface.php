<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model\Lookup;

interface ActionInterface
{
    /**
     * @param array $response
     *
     * @return array|null|void
     */
    public function execute($response);
}
