<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Block;

class GlobalVoice extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Sangria_BackendVoice::system/voice.phtml';

    /**
     * Get components configuration
     * @return array
     */
    public function getWidgetInitOptions()
    {
        return [
        ];
    }
}
