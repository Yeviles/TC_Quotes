<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\Data\ImmutableQuoteInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Lock projection of a single Negotiable Quote row; not a standalone entity
 */
class ImmutableQuote extends AbstractModel implements ImmutableQuoteInterface
{
    /**
     * Constant for cache tag
     * @var string
     */
    public const CACHE_TAG = 'logiscenter_immutable_quote';

    /**
     * Cache tag for model
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\ImmutableQuote::class);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId(): int
    {
        return (int)$this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId(int $quoteId): ImmutableQuoteInterface
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }
    /**
     * @inheritDoc
     */
    public function isImmutable(): bool
    {
        return (bool)$this->getData(self::IS_IMMUTABLE);
    }
    /**
     * @inheritDoc
     */
    public function setIsImmutable(bool $isImmutable): ImmutableQuoteInterface
    {
        return $this->setData(self::IS_IMMUTABLE, $isImmutable);
    }
    /**
     * @inheritDoc
     */
    public function getLockedAt(): ?string
    {
        return $this->getData(self::LOCKED_AT) ?: null;
    }
    /**
     * @inheritDoc
     */
    public function setLockedAt(?string $lockedAt): ImmutableQuoteInterface
    {
        return $this->setData(self::LOCKED_AT, $lockedAt);
    }
    /**
     * @inheritDoc
     */
    public function getLockedByUserId(): ?int
    {
        $id = $this->getData(self::LOCKED_BY_USER_ID);
        return $id === null ? null : (int)$id;
    }
    /**
     * @inheritDoc
     */
    public function setLockedByUserId(?int $userId): ImmutableQuoteInterface
    {
        return $this->setData(self::LOCKED_BY_USER_ID, $userId);
    }
}
