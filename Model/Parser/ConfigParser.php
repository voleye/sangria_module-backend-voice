<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model\Parser;

use Magento\Config\Model\Config\Structure\Element\Field;

class ConfigParser
{
    /**
     * @param $n
     * @param array $cumulate
     * @param array $result
     */
    public function parse($n, $cumulate = [], &$result = [])
    {
        $cumulateMerged = [];
        foreach ($n as $item) {
            if ($item->getLabel() && ($item instanceof Field || $item->hasChildren())) {
                $cumulateMerged = $cumulate;
                $cumulateMerged[] = $item->getLabel();
                $cumulateMerged = array_filter($cumulateMerged);
            }
            if ($item instanceof Field) {
                if ($cumulateMerged) {
                    $result[] = [
                        'chain' => implode('::', $cumulateMerged),
                        'path' => $item->getPath()
                    ];
                }
            } elseif ($item->hasChildren()) {
                $this->parse($item->getChildren(), $cumulateMerged, $result);
            }
        }
    }
}
