<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Api\ConfigRepositoryInterface;
use Logiscenter\ImmutableQuote\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetRateLimitReadsConfiguredStoreScopedValue(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::once())
            ->method('getValue')
            ->with(ConfigRepositoryInterface::XML_RATE_LIMIT, ScopeInterface::SCOPE_STORE, 2)
            ->willReturn('15');

        $config = new Config($scopeConfig);

        self::assertSame(15, $config->getRateLimit(2));
    }

    public function testGetRateLimitDefaultsToZeroWhenNotConfigured(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn(null);

        $config = new Config($scopeConfig);

        self::assertSame(0, $config->getRateLimit());
    }
}
