<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\QuoteMutationGuard;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Blocks direct repository writes to fields with no legitimate checkout resubmission path
 */
class CartRepositoryPlugin
{
    /**
     * Contains the list of protected fields that should not be modified directly
     * @var string[]
     */
    private const PROTECTED_FIELDS = ['coupon_code', 'customer_note'];

    /**
     * Constructor
     *
     * @param QuoteMutationGuard $guard
     */
    public function __construct(
        private readonly QuoteMutationGuard $guard
    ) {}

    /**
     * Before plugin for saving a cart (quote)
     *
     * @param mixed $subject
     * @param CartInterface $quote
     * @return array
     */
    public function beforeSave(mixed $subject, CartInterface $quote): array
    {
        $quoteId = (int)$quote->getId();
        if ($quoteId && $this->hasProtectedChange($quote)) {
            $this->guard->assertCanModify($quoteId, 'direct_quote_save');
        }
        return [$quote];
    }

    /**
     * Checks if the quote has any protected field changes
     *
     * @param CartInterface $quote
     * @return boolean
     */
    private function hasProtectedChange(CartInterface $quote): bool
    {
        if (!method_exists($quote, 'dataHasChangedFor')) {
            return false;
        }
        foreach (self::PROTECTED_FIELDS as $field) {
            if ($quote->dataHasChangedFor($field)) {
                return true;
            }
        }
        return false;
    }
}
