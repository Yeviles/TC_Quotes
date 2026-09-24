<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Api\ConfigRepositoryInterface;
use Logiscenter\ImmutableQuote\Model\RateLimiter;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private CacheInterface&MockObject $cache;

    private ConfigRepositoryInterface&MockObject $config;

    private TimezoneInterface&MockObject $timezone;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->config = $this->createMock(ConfigRepositoryInterface::class);
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->timezone->method('date')->willReturn(new \DateTime('2026-09-23 10:00:00'));
    }

    public function testIncrementsRequestCountBelowLimit(): void
    {
        $this->cache->method('load')->willReturn('1');
        $this->cache->expects(self::once())->method('save')->with('2', self::anything(), [], 3600);
        $this->config->method('getRateLimit')->willReturn(2);
        $this->createRateLimiter()->assertAllowed('admin:5:create');
    }

    public function testRejectsRequestAtLimit(): void
    {
        $this->cache->method('load')->willReturn('2');
        $this->config->method('getRateLimit')->willReturn(2);
        $this->expectException(LocalizedException::class);
        $this->createRateLimiter()->assertAllowed('admin:5:create');
    }

    public function testStartsCountAtOneWhenNothingCached(): void
    {
        $this->cache->method('load')->willReturn(false);
        $this->cache->expects(self::once())->method('save')->with('1', self::anything(), [], 3600);
        $this->config->method('getRateLimit')->willReturn(5);
        $this->createRateLimiter()->assertAllowed('admin:5:create');
    }

    private function createRateLimiter(): RateLimiter
    {
        return new RateLimiter($this->cache, $this->config, $this->timezone);
    }
}