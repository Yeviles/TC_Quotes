<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteRepository;
use Logiscenter\ImmutableQuote\Model\AuditLogger;

/**
 * The single policy authority used at every Magento write boundary
 * Reads the is_immutable flag directly off negotiable_quote
 */
class QuoteMutationGuard
{
    /**
     * Constructor
     *
     * @param ImmutableQuoteRepository $repository
     * @param AuditLogger $auditLogger
     */
    public function __construct(
        private readonly ImmutableQuoteRepository $repository,
        private readonly AuditLogger $auditLogger
    ) {
    }

    /**
     * Asserts that the given quote can be modified
     *
     * @param integer $quoteId
     * @param string $operation
     * @return void
     */
    public function assertCanModify(int $quoteId, string $operation): void
    {
        try {
            $extension = $this->repository->get($quoteId);
        } catch (NoSuchEntityException) {
            return;
        }
        if (!$extension->isImmutable()) {
            return;
        }
        $this->auditLogger->record($quoteId, 'modification_blocked', 'denied', ['operation' => $operation]);
        throw new LocalizedException(
            __('This is a locked B2B quote. Its negotiated items, quantities, addresses, shipping and discounts cannot be changed. Activate another quote to make changes.')
        );
    }

    /**
     * Checks if the given quote is immutable
     *
     * @param int $quoteId
     * @return bool
     */
    public function isImmutable(int $quoteId): bool
    {
        try {
            return $this->repository->get($quoteId)->isImmutable();
        } catch (NoSuchEntityException) {
            return false;
        }
    }
}
