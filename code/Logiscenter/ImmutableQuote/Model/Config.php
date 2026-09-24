<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Logiscenter\ImmutableQuote\Api\ConfigRepositoryInterface;

/**
 * B2B immutables configuration
 */
class Config implements ConfigRepositoryInterface
{
    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Retrieve configuration value by path and store ID
     *
     * @param string $path
     * @param int|null $storeId
     * @return string
     */
    private function getConfigValue(string $path, ?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function getRateLimit(?int $storeId = null): int
    {
        return (int)$this->getConfigValue(self::XML_RATE_LIMIT, $storeId);
    }
}
