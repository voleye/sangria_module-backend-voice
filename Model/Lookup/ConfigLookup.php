<?php
/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Sangria\BackendVoice\Model\Lookup;

use Magento\Backend\Model\UrlInterface;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\ResourceConnection;
use Sangria\BackendVoice\Model\Parser\ConfigParser;

class ConfigLookup implements ActionInterface
{
    /**
     * @var string
     */
    public const LOOKUP_TYPE = 3;
    /**
     * @var string[]
     */
    private $phrases = [
        'find settings'
    ];
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var ConfigParser
     */
    private $configParser;
    /**
     * @var Structure
     */
    private $structure;
    /**
     * @var array
     */
    private $configCache;

    /**
     * ConfigLookup constructor.
     *
     * @param ResourceConnection $connection
     * @param UrlInterface $url
     * @param ConfigParser $configParser
     * @param Structure $structure
     */
    public function __construct(
        ResourceConnection $connection,
        UrlInterface $url,
        ConfigParser $configParser,
        Structure $structure
    ) {
        $this->url = $url;
        $this->configParser = $configParser;
        $this->structure = $structure;
    }

    /**
     * @param array $response
     *
     * @return array|null|void
     */
    public function execute($response)
    {
        $phrase = $this->isApplicablePattern($response);
        if (!$phrase) {
            return;
        }

        $q = $this->getQueryString($phrase);
        $foundItems = $this->findItems($q);

        $resultItems = [];
        foreach ($foundItems as $item) {
            $chainParts = explode('::', $item['chain']);

            $tab = array_shift($chainParts);
            $section = array_shift($chainParts);
            $group = array_shift($chainParts);
            $field = array_shift($chainParts);

            $pathParts = explode('/', $item['path']);
            $hash = '#' . $pathParts[0] . '_' . $pathParts[1] . '-link';

            $key = strtolower($tab . $section . $group);
            $resultItems[$key] = [
                'phrase' => \strip_tags(implode(' / ', [$section, $group])),
                'config' => [
                    'url' => $this->url->getUrl(
                        'adminhtml/system_config/edit/',
                        ['section' => array_shift($pathParts)]
                    ) . $hash,
                    'description' => \strip_tags($field)
                ]
            ];
        }

        return [
            'type' => self::LOOKUP_TYPE,
            'items' => $resultItems
        ];
    }

    private function isApplicablePattern($response)
    {
        $alternatives = $response['results'][0]['alternatives'] ?? [];
        foreach ($alternatives as $alternative) {
            foreach ($this->phrases as $phrase) {
                if (stripos($alternative['transcript'], $phrase) !== false) {
                    return $alternative['transcript'];
                }
            }
        }

        return false;
    }

    private function getQueryString($phrase)
    {
        foreach ($this->phrases as $p) {
            $phrase = trim(str_replace($p, '', $phrase));
        }

        return $phrase;
    }

    /**
     * @param string $q
     *
     * @return array
     */
    private function findItems($q)
    {
        if ($this->configCache === null) {
            $cumulate = [];
            $result = [];
            $this->configParser->parse($this->structure->getTabs(), $cumulate, $result);
            $this->configCache = $result;
        }

        $queryParts = explode(' ', $q);

        $result = $this->configCache;
        return array_filter($this->configCache, function ($arr) use ($queryParts) {
            foreach ($queryParts as $part) {
                if (stripos($arr['chain'], $part) === false) {
                    return false;
                }
            }
            return true;
        });
    }
}
