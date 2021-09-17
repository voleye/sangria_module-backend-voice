<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model\Parser;

class MenuParser
{
    /**
     * @param $n
     * @param $cumulate
     * @param $result
     */
    public function parse($n, $cumulate, &$result)
    {
        $cumulateMerged = [];
        foreach ($n as $item) {
            if (!$item->isAllowed()) {
                continue;
            }

            if ((string)$item->getTitle()) {
                $cumulateMerged = $cumulate;
                $cumulateMerged[] = (string)$item->getTitle();
            }
            if ($item->hasChildren()) {
                $this->parse($item->getChildren(), $cumulateMerged, $result);
            } else {
                $result[] = [
                    'chain' => implode('::', $cumulateMerged),
                    'path' => $item->getAction()
                ];
            }
        }
    }
}
