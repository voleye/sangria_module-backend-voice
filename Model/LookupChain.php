<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model;

use Magento\Framework\ObjectManager\TMapFactory;
use Sangria\BackendVoice\Model\Lookup\ActionInterface;

class LookupChain
{
    /**
     * @var ActionInterface[]
     */
    private $actions;

    /**
     * ActionsPool constructor.
     *
     * @param TMapFactory $tmapFactory
     * @param array $actions
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $actions = []
    ) {
        $this->actions = $tmapFactory->create(
            [
                'array' => $actions,
                'type' => ActionInterface::class
            ]
        );
    }

    /**
     * @param array $response
     *
     * @return array
     */
    public function execute($response)
    {
        $result = null;
        foreach ($this->actions as $action) {
            $result = $action->execute($response);
            if ($result) {
                break;
            }
        }

        return $result;
    }
}
