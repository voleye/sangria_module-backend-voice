<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Controller\Adminhtml\Voice;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Sangria\BackendVoice\Model\LookupChain;
use Sangria\BackendVoice\Model\Speech\Recognizer;

class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;
    /**
     * @var Recognizer
     */
    private $recognizer;
    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var LookupChain
     */
    private $lookupChain;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     * @param Recognizer $recognizer
     * @param Json $jsonSerializer
     * @param LookupChain $lookupChain
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        Recognizer $recognizer,
        Json $jsonSerializer,
        LookupChain $lookupChain
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->recognizer = $recognizer;
        $this->jsonSerializer = $jsonSerializer;
        $this->lookupChain = $lookupChain;
    }

    public function execute()
    {
        $dataArray = $this->getRequest()->getParam('data');
        $response = $this->recognizer->recognize($dataArray);

        $result = $this->lookupChain->execute($response);

        $response = $this->jsonResultFactory->create();
        $response->setJsonData($this->jsonSerializer->serialize($result));

        return $response;
    }
}
