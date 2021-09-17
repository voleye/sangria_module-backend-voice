<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Plugin\Backend\Model\Menu;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder as Subject;
use Magento\Framework\App\ResourceConnection;
use Sangria\BackendVoice\Model\Parser\MenuParser;

class Builder
{
    /**
     * @var MenuParser
     */
    private $menuParser;
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * Builder constructor.
     *
     * @param MenuParser $menuParser
     * @param ResourceConnection $connection
     */
    public function __construct(
        MenuParser $menuParser,
        ResourceConnection $connection
    ) {
        $this->menuParser = $menuParser;
        $this->connection = $connection;
    }

    public function afterGetResult(Subject $subject, Menu $result, Menu $menu)
    {
        $connection = $this->connection->getConnection();

        $flatMenu = [];
        $cumulate = [];

        $this->menuParser->parse($result, $cumulate, $flatMenu);

        $connection->delete($connection->getTableName('voice_dictionary'), ['type = ?' => 2]);
        foreach ($flatMenu as $item) {
            $parts = explode('::', $item['chain']);
            if ($parts) {
                $phrase = end($parts) ? strtolower(end($parts)) : null;
                if ($phrase) {
                    $connection->insert(
                        $connection->getTableName('voice_dictionary'),
                        [
                            'phrase' => $phrase . '|' . strtolower(implode(' ', $parts)),
                            'config' => '{
  "type": "redirect",
  "config": {
    "path": "' . $item['path'] . '",
    "params": {},
    "phrase": "' . implode(' / ', $parts) . '"
  }
}',
                            'type' => 2
                        ]
                    );
                }
            }
        }
        return $result;
    }
}
