<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Logiscenter\ImmutableQuote\Api\ConfigRepositoryInterface;

/**
 * Keys are scoped by authenticated principal and UTC hour
 */
class RateLimiter
{
    /**
     * Constant for the cache key prefix
     */
    private const CACHE_KEY_PREFIX = 'logiscenter_iq_rate_';

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     * @param ConfigRepositoryInterface $config
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ConfigRepositoryInterface $config,
        private readonly TimezoneInterface $timezone
    ) {}

    /**
     * Asserts that the given subject is allowed under the current rate limit
     *
     * @param string $subject
     * @return void
     */
    public function assertAllowed(string $subject): void
    {
        $hour = $this->timezone->date(null, null, false)->format('YmdH');
        $key = self::CACHE_KEY_PREFIX . hash('sha256', $subject . $hour);
        $count = (int)($this->cache->load($key) ?: 0);

        if ($count >= $this->config->getRateLimit()) {
            throw new LocalizedException(__('Immutable quote API rate limit exceeded. Try again in the next hour.'));
        }

        $this->cache->save((string)($count + 1), $key, [], 3600);
    }
}
