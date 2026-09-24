<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api\Data;

/**
 * Data contract for the immutable lock projection of a Negotiable Quote.
 * @api
 */
interface ImmutableQuoteInterface
{
    /**
     * Quote ID constant.
     * @var string
     */
    public const QUOTE_ID = 'quote_id';

    /**
     * Is immutable constant
     * @var bool
     */
    public const IS_IMMUTABLE = 'is_immutable';

    /**
     * Immutable lock timestamp constant.
     * @var string
     */
    public const LOCKED_AT = 'immutable_locked_at';

    /**
     * Immutable lock user ID constant.
     * @var string
     */
    public const LOCKED_BY_USER_ID = 'immutable_locked_by';

    /**
     * Get the Magento quote identifier.
     *
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * Set the Magento quote identifier.
     *
     * @param int $quoteId
     * @return ImmutableQuoteInterface
     */
    public function setQuoteId(int $quoteId): self;

    /**
     * Return whether the quote is locked against modifications.
     *
     * @return bool
     */
    public function isImmutable(): bool;

    /**
     * Set the immutable state.
     *
     * @param bool $isImmutable
     * @return ImmutableQuoteInterface
     */
    public function setIsImmutable(bool $isImmutable): self;

    /**
     * Get the UTC time at which the quote was locked.
     *
     * @return string|null
     */
    public function getLockedAt(): ?string;

    /**
     * Set the UTC lock timestamp.
     *
     * @param string|null $lockedAt
     * @return ImmutableQuoteInterface
     */
    public function setLockedAt(?string $lockedAt): self;

    /**
     * Get the identifier of the actor that locked the quote.
     *
     * @return int|null
     */
    public function getLockedByUserId(): ?int;

    /**
     * Set the identifier of the actor that locked the quote.
     *
     * @param int|null $userId
     * @return ImmutableQuoteInterface
     */
    public function setLockedByUserId(?int $userId): self;
}
