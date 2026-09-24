<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Immutable Quote Cache
 */
class ImmutableQuoteCache
{
    /**
     * Constant for cache lifetime in seconds
     * @var int
     */
    private const LIFETIME = 300;

    /**
     * Request cache for loaded ImmutableQuote entities
     * @var array
     */
    private array $requestCache = [];

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     * @param ImmutableQuoteFactory $factory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ImmutableQuoteFactory $factory,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * Load an ImmutableQuote entity from the cache or request cache
     *
     * @param integer $quoteId
     * @return ImmutableQuote|null
     */
    public function load(int $quoteId): ?ImmutableQuote
    {
        if (isset($this->requestCache[$quoteId])) {
            return $this->requestCache[$quoteId];
        }
        $cacheData = $this->cache->load($this->key($quoteId));
        if (!$cacheData) {
            return null;
        }
        /**
         * @var ImmutableQuote $entity
         * */
        $entity = $this->factory->create();
        $entity->setData($this->serializer->unserialize($cacheData));
        return $this->requestCache[$quoteId] = $entity;
    }

    /**
     * Save an ImmutableQuote entity to the cache and request cache
     *
     * @param ImmutableQuote $entity
     * @return void
     */
    public function save(ImmutableQuote $entity): void
    {
        $quoteId = $entity->getQuoteId();
        $this->requestCache[$quoteId] = $entity;
        $this->cache->save(
            $this->serializer->serialize($entity->getData()),
            $this->key($quoteId),
            [ImmutableQuote::CACHE_TAG],
            self::LIFETIME
        );
    }

    /**
     * Invalidate an ImmutableQuote entity in the cache and request cache
     *
     * @param integer $quoteId
     * @return void
     */
    public function invalidate(int $quoteId): void
    {
        unset($this->requestCache[$quoteId]);
        $this->cache->remove($this->key($quoteId));
    }

    /**
     * Generate the cache key for an ImmutableQuote entity based on its quote ID
     *
     * @param integer $quoteId
     * @return string
     */
    private function key(int $quoteId): string
    {
        return ImmutableQuote::CACHE_TAG . '_' . $quoteId;
    }
}
